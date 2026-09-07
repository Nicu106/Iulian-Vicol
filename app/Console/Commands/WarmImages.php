<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Support\Img;
use Illuminate\Console\Command;

/**
 * Build the derivatives a first-time visitor needs, before they arrive.
 *
 * The resizing endpoint caches forever, but it builds on demand: measured on this
 * server, one 4.68 MB / 5712x4284 source costs 1.36 s at 400px and 1.76 s at 1600.
 * So routing /coche through it cut the page from 7.17 MB to 0.22 MB and, on a cold
 * cache, pushed the load from 1.3 s to 3.0 s — the first visitor paying for
 * everyone. That is the exact case the work was for.
 *
 * Three cores, ~1,859 originals: warming everything at every width is hours. So
 * this warms what the FIRST SCREEN of each page needs and leaves the rest to be
 * built on demand, one image at a time, by whoever scrolls that far.
 */
class WarmImages extends Command
{
    protected $signature = 'images:warm
        {--clicks : also the stage size for every gallery photograph, so pressing a thumbnail is instant}
        {--all : every gallery photograph at every width, not just the first screen}
        {--force : rebuild derivatives that already exist}';

    protected $description = 'Pre-build resized images so nobody waits for GD on a cold page';

    /** What each slot asks for, measured from the rendered pages. */
    private const BANNER = [320, 480, 720, 1080, 1600, 2000];
    private const COVER  = [320, 480, 720, 1080];   // catalogue card + car page stage
    private const STAGE  = [720, 1080, 1600];       // the photo being looked at, and the viewer
    private const THUMB  = [320];
    private const FACE   = [320, 480, 720];         // testimonial portraits
    private const ABOVE_FOLD_THUMBS = 8;

    /** Pressing a thumbnail swaps the stage, and the stage wants 1080 on a normal
     *  laptop. Left on demand that press cost 1.4 s of GD with the old photograph
     *  still up — "un pic cam nu prea se schimba poza". 1,366 gallery photographs,
     *  about 34 minutes single-threaded, so it is behind --clicks rather than in
     *  the default run. 1600 (retina, and the full-screen viewer) stays on demand:
     *  it would double the time and it is the second press, not the first. */
    private const CLICK  = [1080];

    public function handle(): int
    {
        $t0 = microtime(true);
        $jobs = [];

        foreach (glob(public_path('img/banner/*')) as $f) {
            $this->add($jobs, '/img/banner/' . basename($f), self::BANNER);
        }

        foreach (glob(storage_path('app/public/testimonials/*')) as $f) {
            $this->add($jobs, '/storage/testimonials/' . basename($f), self::FACE);
        }

        foreach (Vehicle::whereIn('status', ['available', 'sold'])->get() as $v) {
            $gallery = is_array($v->gallery_images) ? $v->gallery_images : [];
            $all = array_values(array_filter(array_merge([$v->cover_image], $gallery)));
            if (!$all) {
                continue;
            }
            $this->add($jobs, $all[0], array_unique(array_merge(self::COVER, self::STAGE)));

            $rest = array_slice($all, 1);
            $thumbs = $this->option('all') ? $rest : array_slice($rest, 0, self::ABOVE_FOLD_THUMBS);
            foreach ($thumbs as $p) {
                $this->add($jobs, $p, self::THUMB);
            }
            if ($this->option('all')) {
                foreach ($rest as $p) {
                    $this->add($jobs, $p, self::STAGE);
                }
            } elseif ($this->option('clicks')) {
                foreach ($rest as $p) {
                    $this->add($jobs, $p, self::CLICK);
                }
            }
        }

        $this->info(count($jobs) . ' derivatives to check');
        $bar = $this->output->createProgressBar(count($jobs));
        $built = 0; $skipped = 0; $failed = 0;

        foreach ($jobs as [$path, $w]) {
            $bar->advance();
            // The ENDPOINT, explicitly. Img::url() answers with the cached file
            // once it exists — which is the whole point of it — so building the
            // request from that URL asked nginx's path to generate itself and
            // reported 630 failures the moment the cache was populated.
            if (Img::url($path, $w) === null) { $failed++; continue; }
            $endpoint = '/img/' . Img::nearestWidth($w) . '?p=' . rawurlencode($path);
            try {
                $r = app(\App\Http\Controllers\ImageController::class)
                    ->resize(\Illuminate\Http\Request::create($endpoint, 'GET'), Img::nearestWidth($w));
                $r->getStatusCode() === 200 ? $built++ : $failed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf('done in %.1f s — %d ok, %d failed', microtime(true) - $t0, $built, $failed));

        return self::SUCCESS;
    }

    private function add(array &$jobs, ?string $path, array $widths): void
    {
        if (!is_string($path) || $path === '') {
            return;
        }
        foreach ($widths as $w) {
            $jobs[] = [$path, $w];
        }
    }
}
