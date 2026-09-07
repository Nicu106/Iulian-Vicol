<?php

namespace App\Jobs;

use App\Support\Img;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Build the derivatives for one car's photographs, off the request.
 *
 * Without this the system still works — the endpoint generates on demand and
 * caches forever, and a brand-new file measured 0.24 s at 320px and 0.57 s at
 * 1080 — but it is the FIRST VISITOR who pays it, once per photograph per width.
 * Upload forty pictures and the next person to open that car waits for the stage
 * and every thumbnail above the fold.
 *
 * Dispatched by the Vehicle observer whenever the photographs change, so this
 * happens between the admin pressing save and anyone arriving.
 *
 * It runs AFTER THE RESPONSE, not on a queue worker: this server has no worker, no
 * supervisor and no cron for the scheduler, so anything pushed onto the database
 * queue sits there forever — verified by queueing two jobs and watching nothing
 * happen. dispatchAfterResponse() runs it in the same PHP-FPM process once the
 * admin's page has already been sent, which needs no infrastructure at all.
 *
 * That process is one of six (pm.max_children = 6), so the work is BOUNDED. It
 * builds what makes a car page fast in the order that matters and stops at the
 * budget; anything left is built on demand at 0.24-0.57 s per image, which is the
 * behaviour that existed before this class and is not a failure.
 */
class WarmVehicleImages implements ShouldQueue
{
    use Queueable;

    /** Same ladder App\Console\Commands\WarmImages uses for a first screen. */
    private const FIRST = [320, 480, 720, 1080, 1600];
    private const THUMB = [320];
    private const CLICK = [1080];

    public function __construct(public array $paths)
    {
    }

    /** Seconds of GD this is allowed to hold a PHP-FPM process for. Measured cost
     *  is 0.24 s at 320px and up to 1.8 s at 1600 depending on the source, so this
     *  is roughly one car's first screen. */
    private const BUDGET = 25.0;

    public int $timeout = 120;
    public int $tries = 1;

    public function handle(): void
    {
        $paths = array_values(array_filter($this->paths));
        if (!$paths) {
            return;
        }
        $t0 = microtime(true);
        $done = 0;

        // Ordered by what a visitor sees first, so running out of budget costs the
        // least visible thing: the card and the stage, then the thumbnail strip,
        // then the size the stage swaps to when a thumbnail is pressed.
        $queue = [];
        foreach (self::FIRST as $w) {
            $queue[] = [$paths[0], $w];
        }
        foreach (array_slice($paths, 1) as $p) {
            foreach (self::THUMB as $w) {
                $queue[] = [$p, $w];
            }
        }
        foreach (array_slice($paths, 1) as $p) {
            foreach (self::CLICK as $w) {
                $queue[] = [$p, $w];
            }
        }

        foreach ($queue as [$path, $w]) {
            if (microtime(true) - $t0 > self::BUDGET) {
                \Log::info(sprintf(
                    'WarmVehicleImages: budget reached after %d of %d — the rest build on demand',
                    $done, count($queue)
                ));
                return;
            }
            $this->build($path, $w);
            $done++;
        }
    }

    private function build(string $path, int $w): void
    {
        // Already there: Img::url answers with the cached FILE once it exists.
        if (str_starts_with((string) Img::url($path, $w), '/storage/cache/')) {
            return;
        }
        try {
            app(\App\Http\Controllers\ImageController::class)->resize(
                \Illuminate\Http\Request::create('/img/' . Img::nearestWidth($w) . '?p=' . rawurlencode($path), 'GET'),
                Img::nearestWidth($w)
            );
        } catch (\Throwable $e) {
            // One unreadable file must not stop the other thirty-nine.
            \Log::warning('WarmVehicleImages: ' . $path . ' @' . $w . ' — ' . $e->getMessage());
        }
    }
}
