<?php

namespace App\Support;

use App\Http\Controllers\Admin\SheetController;
use App\Http\Controllers\CarPageController;
use App\Models\Vehicle;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * The car's page as a PDF a buyer keeps (2026-11-04). The client: "butonul ...
 * descarcă PDF ... să pui în acel PDF datele care sunt pe site ... să fie
 * foarte nice".
 *
 * Not the bonnet placard (Admin\SheetController::placard), which is one dense
 * sheet read from a step away; this is the page itself, on paper: the marque's
 * colour with the car in front of it, the price, the facts, what comes with
 * the car, the instalment, then the photographs, the description and the
 * equipment. Same printer (tools/cartel/print.mjs, Chrome through puppeteer).
 *
 * Built once per version of the car and kept (storage/app/fichas): a press
 * costs a file read, not a Chrome. The key changes when the car is saved or
 * this code or its view change, so nothing stale is ever served.
 */
final class CarPdf
{
    private const DIR = 'app/fichas';

    /** The photographs on the second page (after the cover, his order, no flaws). */
    private const MORE = 6;

    public static function eligible(?Vehicle $car): bool
    {
        return $car !== null && in_array($car->status, ['available', 'reserved'], true) && (int) $car->price > 0;
    }

    public static function filename(Vehicle $car): string
    {
        $brand = Marques::for($car->brand)['name'] ?? $car->brand;

        return 'IV-MOTORCLASS-' . Str::slug($brand . ' ' . $car->model . ' ' . $car->year) . '.pdf';
    }

    /** The PDF's path, built now if this version of the car has none yet; null if Chrome failed. */
    public static function file(Vehicle $car): ?string
    {
        $dir = storage_path(self::DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $key = substr(sha1(implode('|', [
            $car->updated_at?->timestamp, $car->price, $car->status,
            filemtime(__FILE__), filemtime(resource_path('views/pdf/ficha.blade.php')),
            Finance::TIN, json_encode(Finance::BANKS),
        ])), 0, 12);
        $out = $dir . '/' . $car->slug . '-' . $key . '.pdf';
        if (is_file($out)) {
            return $out;
        }

        // one Chrome at a time, whoever asks: two presses on a new car wait for one build
        $lock = fopen($dir . '/.lock', 'c');
        flock($lock, LOCK_EX);
        try {
            if (is_file($out)) {
                return $out;
            }
            $tmp = sys_get_temp_dir() . '/mc-ficha-' . bin2hex(random_bytes(6));
            mkdir($tmp, 0700);
            try {
                file_put_contents($tmp . '/ficha.html', view('pdf.ficha', self::data($car))->render());
                $node = is_executable('/usr/bin/node') ? '/usr/bin/node' : 'node';
                $p = new Process([$node, base_path('tools/cartel/print.mjs'), $tmp . '/ficha.html', $tmp . '/ficha.pdf', SheetController::CHROME],
                    base_path(), ['HOME' => $tmp]);
                $p->setTimeout(90);
                $p->run();
                if (!is_file($tmp . '/ficha.pdf') || filesize($tmp . '/ficha.pdf') < 1000) {
                    report(new \RuntimeException('ficha.pdf: chrome failed: ' . $p->getErrorOutput()));

                    return null;
                }
                // the versions before this one go
                foreach (glob($dir . '/' . $car->slug . '-*.pdf') ?: [] as $old) {
                    @unlink($old);
                }
                rename($tmp . '/ficha.pdf', $out);

                return $out;
            } finally {
                Process::fromShellCommandline('rm -rf ' . escapeshellarg($tmp))->run();
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** Everything the PDF shows, as the car page shows it today. */
    public static function data(Vehicle $car): array
    {
        $marque = Marques::for($car->brand);
        $brand = $marque['name'] ?? Str::title((string) $car->brand);
        $s = $car->sheet ?? [];
        $url = url('/coche/' . $car->slug);

        $label = strtoupper(trim((string) ($car->vin ?: ($s['label'] ?? ''))));
        $label = in_array($label, ['0', 'CERO', 'ECO', 'C', 'B'], true) ? ($label === 'CERO' ? '0' : $label) : null;

        $plans = Finance::plans((int) $car->price);

        return [
            'car'      => $car,
            'brand'    => $brand,
            'colour'   => $marque['colour'] ?? '#111C2E',
            'mark'     => $marque ? self::uri(public_path('img/marques/' . $marque['key'] . '.svg')) : null,
            'url'      => $url,
            'host'     => parse_url($url, PHP_URL_HOST),
            'qr'       => (new QRCode(new QROptions([
                'outputType' => 'svg', 'outputBase64' => false, 'eccLevel' => EccLevel::M,
                'addQuietzone' => false, 'svgUseFillAttributes' => true, 'drawLightModules' => false,
                'connectPaths' => true, 'svgAddXmlHeader' => false,
            ])))->render($url),
            'cover'    => self::photo($car->primary_image ?? $car->cover_image, 2000),
            'photos'   => array_values(array_filter(array_map(fn ($p) => self::photo($p, 1080), self::more($car)))),
            'price'    => (int) $car->price,
            'before'   => $car->original_price && $car->original_price > $car->price ? (int) $car->original_price : null,
            'specs'    => SheetController::specs($car, $label),
            'warranty' => Warranty::ladder($car->sheet),
            'upkeep'   => Maintenance::ladder($car->sheet),
            'plan'     => end($plans),
            'tae'      => Finance::tae(),
            'past'     => CarPageController::past($car),
            'about'    => self::about((string) ($car->description ?: ($s['description'] ?? ''))),
            'features' => SheetController::equipment($car),
            'banks'    => array_map(fn ($k, $b) => ['name' => $b[0], 'src' => self::uri(public_path('img/banks/' . $k . '.svg')),
                              'w' => Finance::markWidth($b[1], $b[2] ?? 1)], array_keys(Finance::BANKS), Finance::BANKS),
            'fonts'    => SheetController::fontFaces(),
            'date'     => now('Europe/Madrid')->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
        ];
    }

    /** The photographs after the cover, in the order he set, without the ones of flaws. */
    private static function more(Vehicle $car): array
    {
        $cover = $car->primary_image ?? $car->cover_image;
        $tags = is_array($car->image_tags) ? $car->image_tags : [];

        return array_slice(array_values(array_filter(
            is_array($car->gallery_images) ? $car->gallery_images : [],
            fn ($p) => is_string($p) && $p !== $cover && ($tags[$p] ?? null) !== 'flaw'
        )), 0, self::MORE);
    }

    /** a photograph as a JPEG data: URI at one of the site's widths (Chrome reads nothing else) */
    private static function photo(?string $path, int $w): ?array
    {
        if (!$path) {
            return null;
        }
        $src = SheetController::jpeg(SheetController::derivative($path, $w));
        if (!$src) {
            return null;
        }
        $size = Img::size($path);

        return ['src' => $src, 'ratio' => $size && $size[1] ? $size[0] / $size[1] : 1.5];
    }

    private static function uri(string $file): ?string
    {
        return is_file($file) ? 'data:' . (str_ends_with($file, '.svg') ? 'image/svg+xml' : mime_content_type($file)) . ';base64,' . base64_encode(file_get_contents($file)) : null;
    }

    /**
     * His description in blocks: a paragraph, or a list where he wrote one. The
     * emoji he starts lines with were lost on the way into the database and are
     * stored as "?" — on paper those lines are the list they were meant to be.
     *
     * @return list<array{0:'p'|'ul', 1:string|list<string>}>
     */
    public static function about(string $text): array
    {
        $out = [];
        foreach (SheetController::paragraphs($text) as $para) {
            $list = $lines = [];
            $flush = function () use (&$list, &$lines, &$out) {
                if ($lines) {
                    $out[] = ['p', implode("\n", $lines)];
                    $lines = [];
                }
                if ($list) {
                    $out[] = ['ul', $list];
                    $list = [];
                }
            };
            foreach (preg_split('/\n/', $para) as $line) {
                $line = trim($line);
                if ($line === '' || preg_match('/^[?\s]+$/u', $line)) {
                    continue;
                }
                if (preg_match('/^(?:\?+|[•·\-–✓✔✅▪►]|\p{So})\s*(.+)$/u', $line, $m)) {
                    if ($lines) {
                        $flush();
                    }
                    $list[] = trim($m[1]);
                } else {
                    if ($list) {
                        $flush();
                    }
                    $lines[] = preg_replace('/\s\?(?=\s|$)/u', '', $line);
                }
            }
            $flush();
        }

        return $out;
    }
}
