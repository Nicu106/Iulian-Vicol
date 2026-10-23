<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use App\Support\CarPhotos;
use App\Support\CochesNetSheet;
use App\Support\Marques;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * A car's sheet: in (the PDF coches.net PRO prints, read into the form) and out
 * (the A4 placard that goes on the car's bonnet, with a QR code to its page).
 *
 * IN. The form sends the PDF here the moment it is chosen; it is read
 * (App\Support\CochesNetSheet) and what was read goes back to the form, which fills
 * its fields and shows what changed. Nothing is saved until the form is: the PDF and
 * what was read wait under storage/app/private/sheets/pending/{key}, and the form
 * carries only the key. On save, adopt() makes them the car's (sheets/{slug}.pdf and
 * the `sheet` column). Pending files older than a day go now and then.
 *
 * OUT. /admin/vehicles/{slug}/cartel is the placard as a page (Imprimir: the browser's
 * own print, A4); /cartel.pdf is the same page drawn by headless Chrome
 * (/opt/mc-chrome, copied from puppeteer's chrome-headless-shell) into a PDF to
 * download. Always today's data from the database: the price, the km, the photo.
 */
class SheetController extends Controller
{
    private const PENDING = 'sheets/pending';
    public const CHROME = '/opt/mc-chrome/chrome-headless-shell-linux64/chrome-headless-shell';

    /** POST /admin/fichas/leer {pdf, token?, slug?, photos?} → what the sheet says */
    public function read(Request $request): JsonResponse
    {
        $request->validate([
            'pdf'    => ['required', 'file', 'max:15360'],
            'token'  => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{32,64}$/'],
            'slug'   => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'integer', 'min:0'],
        ], [
            'pdf.required' => 'No ha llegado ningún PDF.',
            'pdf.file'     => 'El PDF no ha llegado entero. Vuelve a intentarlo.',
            'pdf.uploaded' => 'El PDF no ha llegado entero. Vuelve a intentarlo.',
            'pdf.max'      => 'El PDF pesa más de 15 MB. La ficha de coches.net pesa menos de 1 MB: ¿es el archivo correcto?',
        ]);

        $disk = Storage::disk('local');
        $key = Str::random(40);
        $disk->putFileAs(self::PENDING, $request->file('pdf'), $key . '.pdf');
        $path = $disk->path(self::PENDING . '/' . $key . '.pdf');

        $r = CochesNetSheet::parse($path);
        if (!$r['ok']) {
            $disk->delete(self::PENDING . '/' . $key . '.pdf');
            return response()->json(['message' => $r['error']], 422);
        }
        $sheet = $r['sheet'] + ['file' => mb_substr((string) $request->file('pdf')->getClientOriginalName(), 0, 120)];
        $disk->put(self::PENDING . '/' . $key . '.json', json_encode($sheet, JSON_UNESCAPED_UNICODE));

        // a car with no photographs yet gets the sheet's: staged like one from the
        // phone, so the form shows it, groups it and can remove it like any other
        $photo = null;
        if ($request->filled('token') && (int) $request->input('photos', 0) === 0) {
            $bytes = CochesNetSheet::photo($path);
            if ($bytes) {
                $tmp = tempnam(sys_get_temp_dir(), 'cnp');
                file_put_contents($tmp, $bytes);
                try {
                    $photo = CarPhotos::stage(new UploadedFile($tmp, 'coches-net.jpg', 'image/jpeg', null, true), $request->input('token'));
                } catch (\Throwable) {
                    $photo = null;
                } finally {
                    @unlink($tmp);
                }
            }
        }

        if (random_int(1, 10) === 1) {
            self::prune();
        }

        return response()->json([
            'key'      => $key,
            'fields'   => $r['fields'] + array_filter(['vin' => $sheet['label']]),
            'sheet'    => [
                'doors' => $sheet['doors'], 'seats' => $sheet['seats'], 'trunk' => $sheet['trunk'],
                'co2' => $sheet['co2'], 'warranty' => $sheet['warranty_months'],
                'extras' => count($sheet['extras']), 'new_total' => $sheet['new_total'], 'new_year' => $sheet['new_year'],
                'facts' => $sheet['facts'], 'title' => $sheet['title'],
                // for the review window: the dearest extras and the past, in words
                'extras_top' => collect($sheet['extras'])->filter(fn ($x) => ($x['price'] ?? 0) > 0)->sortByDesc('price')
                    ->take(6)->map(fn ($x) => ['name' => $x['name'], 'price' => (int) round($x['price'])])->values()->all(),
                'lines' => CochesNetSheet::factLines($sheet['facts']),
            ],
            'warnings' => $r['warnings'],
            'photo'    => $photo,
            'matches'  => $this->matches($sheet, $r['fields'], (string) $request->input('slug')),
        ]);
    }

    /** the same car already in the list: its coches.net stock id, or marque + year + km */
    private function matches(array $sheet, array $fields, string $except): array
    {
        $key = Marques::for($fields['brand'] ?? null)['key'] ?? mb_strtolower((string) ($fields['brand'] ?? ''));
        return Vehicle::query()->where('slug', '!=', $except)->get()
            ->filter(function (Vehicle $v) use ($sheet, $fields, $key) {
                if ($sheet['stock_id'] && ($v->sheet['stock_id'] ?? null) === $sheet['stock_id']) return true;
                $vk = Marques::for($v->brand)['key'] ?? mb_strtolower((string) $v->brand);
                return $key !== '' && $vk === $key
                    && isset($fields['mileage']) && $v->mileage && abs($v->mileage - $fields['mileage']) <= 3000
                    && isset($fields['year']) && abs((int) $v->year - (int) $fields['year']) <= 1;
            })
            ->take(3)
            ->map(fn (Vehicle $v) => [
                'name'   => trim((Marques::for($v->brand)['name'] ?? $v->brand) . ' ' . $v->model . ' ' . $v->year),
                'url'    => route('admin.vehicles.edit', $v->slug),
                'status' => $v->status,
            ])->values()->all();
    }

    /**
     * On save (VehicleController store/update): the sheet this car keeps. A new one
     * read in the form (sheet_key), the one it had, or none (sheet_remove); then the
     * few facts the form lets him correct (doors, seats, boot, CO2, warranty).
     */
    public static function adopt(Request $request, string $slug, ?array $current): ?array
    {
        $disk = Storage::disk('local');
        $sheet = $current;

        if ($request->boolean('sheet_remove')) {
            if (!empty($current['pdf'])) $disk->delete($current['pdf']);
            $sheet = null;
        }

        $key = (string) $request->input('sheet_key');
        if (preg_match('/^[A-Za-z0-9]{40}$/', $key) && $disk->exists(self::PENDING . "/$key.json")) {
            $new = json_decode((string) $disk->get(self::PENDING . "/$key.json"), true);
            if (is_array($new)) {
                $pdf = 'sheets/' . Str::slug($slug) . '.pdf';
                if ($disk->exists(self::PENDING . "/$key.pdf")) {
                    $disk->delete($pdf);
                    $disk->move(self::PENDING . "/$key.pdf", $pdf);
                    $new['pdf'] = $pdf;
                }
                $disk->delete(self::PENDING . "/$key.json");
                $sheet = $new;
            }
        }

        // the facts he can correct by hand (the warnings ask him to)
        $edits = [];
        foreach (['doors' => 'sheet_doors', 'seats' => 'sheet_seats', 'trunk' => 'sheet_trunk', 'co2' => 'sheet_co2', 'warranty_months' => 'sheet_warranty'] as $k => $in) {
            if ($request->has($in)) {
                $v = preg_replace('/\D/', '', (string) $request->input($in));
                $edits[$k] = $v === '' ? null : min((int) $v, 99999);
            }
        }
        // a car without a sheet gets one only for something typed (the warranty
        // select always sends its default, one year)
        $typed = array_filter($edits, fn ($v, $k) => $v !== null && !($k === 'warranty_months' && $v === 12), ARRAY_FILTER_USE_BOTH);
        if ($edits && ($sheet || $typed)) {
            $sheet = array_merge($sheet ?? ['source' => 'manual'], $edits);
        }

        return $sheet;
    }

    /** GET /admin/fichas/{slug}/original — the PDF he uploaded */
    public function original(string $slug)
    {
        $car = Vehicle::where('slug', $slug)->firstOrFail();
        $pdf = $car->sheet['pdf'] ?? null;
        abort_unless($pdf && Storage::disk('local')->exists($pdf), 404);
        return Storage::disk('local')->response($pdf, 'coches.net-' . Str::slug($car->brand . ' ' . $car->model) . '.pdf');
    }

    /* ------------------------------------------------------------- the placard */

    /** GET /admin/vehicles/{slug}/cartel — the A4 placard, on screen, to print */
    public function placard(string $slug)
    {
        $car = Vehicle::where('slug', $slug)->firstOrFail();
        return view('admin.vehicles.cartel', self::placardData($car, false));
    }

    /** GET /admin/vehicles/{slug}/cartel.pdf — the same, as a PDF to download */
    public function placardPdf(string $slug)
    {
        $car = Vehicle::where('slug', $slug)->firstOrFail();
        $html = view('admin.vehicles.cartel', self::placardData($car, true))->render();

        $dir = sys_get_temp_dir() . '/mc-cartel-' . bin2hex(random_bytes(6));
        mkdir($dir, 0700);
        $in = $dir . '/cartel.html';
        $out = $dir . '/cartel.pdf';
        file_put_contents($in, $html);
        try {
            // Chrome's own --print-to-pdf prints Letter whatever @page says; through
            // puppeteer-core the page's A4 is used (tools/cartel/print.mjs)
            // (PHP-FPM has no PATH: node by its full path)
            $node = is_executable('/usr/bin/node') ? '/usr/bin/node' : 'node';
            $p = new Process([$node, base_path('tools/cartel/print.mjs'), $in, $out, self::CHROME], base_path(), ['HOME' => $dir]);
            $p->setTimeout(60);
            $p->run();
            if (!is_file($out) || filesize($out) < 1000) {
                report(new \RuntimeException('cartel.pdf: chrome failed: ' . $p->getErrorOutput()));
                return redirect()->route('admin.vehicles.placard', $slug)
                    ->with('status', 'No se ha podido crear el PDF. Usa «Imprimir» y elige «Guardar como PDF».');
            }
            $name = 'IV-Motorclass-' . Str::slug((Marques::for($car->brand)['name'] ?? $car->brand) . ' ' . $car->model . ' ' . $car->year) . '.pdf';
            return response(file_get_contents($out), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $name . '"',
                'Cache-Control' => 'no-store',
            ]);
        } finally {
            Process::fromShellCommandline('rm -rf ' . escapeshellarg($dir))->run();
        }
    }

    /** everything the placard shows, from the database today */
    public static function placardData(Vehicle $car, bool $inline): array
    {
        $s = $car->sheet ?? [];
        $marque = Marques::for($car->brand);
        $url = url('/coche/' . $car->slug);

        $qr = (new QRCode(new QROptions([
            'outputType' => 'svg', 'outputBase64' => false, 'eccLevel' => EccLevel::M,
            'addQuietzone' => false, 'svgUseFillAttributes' => true, 'drawLightModules' => false,
            'connectPaths' => true, 'svgAddXmlHeader' => false,
        ])))->render($url);

        // the cover: the file itself (print wants every pixel), inline for Chrome
        $photo = null;
        $cover = $car->primary_image ?? $car->cover_image;
        if ($cover) {
            $file = public_path(ltrim(parse_url($cover, PHP_URL_PATH) ?? '', '/'));
            if (!is_file($file)) $file = storage_path('app/public/' . preg_replace('~^/?storage/~', '', $cover));
            if (is_file($file)) {
                $photo = $inline ? 'data:' . (mime_content_type($file) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($file)) : $cover;
            }
        }

        $label = strtoupper(trim((string) ($car->vin ?: ($s['label'] ?? ''))));
        $label = in_array($label, ['0', 'CERO', 'ECO', 'C', 'B'], true) ? ($label === 'CERO' ? '0' : $label) : null;
        $now = (int) $car->price;
        $newTotal = (float) ($s['new_total'] ?? 0);

        return [
            'car'      => $car,
            'inline'   => $inline,
            'brand'    => $marque['name'] ?? Str::title((string) $car->brand),
            'url'      => $url,
            'urlShort' => preg_replace('~^https?://(www\.)?~', '', $url),
            'qr'       => $qr,
            'photo'    => $photo,
            'label'    => $label,
            'price'    => $now,
            'before'   => $car->original_price && $car->original_price > $car->price ? (int) $car->original_price : null,
            'warranty' => (int) ($s['warranty_months'] ?? 12),
            'specs'    => self::specs($car),
            'new'      => $newTotal > $now && $newTotal > 0 ? [
                'year' => $s['new_year'] ?? null, 'total' => $newTotal, 'base' => $s['new_price'] ?? null,
                'extras' => $s['extras_total'] ?? null, 'save' => $newTotal - $now,
                'pct' => (int) floor(($newTotal - $now) / $newTotal * 100),
            ] : null,
            'extras'   => collect($s['extras'] ?? [])->filter(fn ($x) => ($x['price'] ?? 0) > 0)
                            ->sortByDesc('price')->values()->all(),
            'facts'    => CochesNetSheet::factLines($s['facts'] ?? []),
            // a car without a sheet: its own equipment list, as much as fits
            'features' => array_slice(array_values(array_filter((array) $car->features, 'is_string')), 0, 16),
            'fonts'    => $inline ? self::fontFaces() : null,
        ];
    }

    /** the facts that decide a viewing, then the rest, in the site's words */
    public static function specs(Vehicle $car): array
    {
        $s = $car->sheet ?? [];
        $num = fn ($n) => number_format((int) $n, 0, ',', '.');
        $cv = (int) preg_replace('/\D/', '', (string) $car->power);
        return array_filter([
            'Año'         => $car->year,
            'Kilómetros'  => $car->mileage ? $num($car->mileage) . ' km' : null,
            'Combustible' => $car->fuel ?: $car->fuel_type,
            'Cambio'      => $car->transmission,
            'Potencia'    => $cv ? $cv . ' CV' : null,
            'Motor'       => $car->engine ? (preg_match('/^\d\.\d$/', $car->engine) && !empty($s['cc']) ? $car->engine . ' · ' . $num($s['cc']) . ' cc' : $car->engine) : null,
            'Carrocería'  => $car->body_type,
            'Color'       => $car->color,
            'Puertas'     => $s['doors'] ?? null,
            'Plazas'      => $s['seats'] ?? null,
            'Maletero'    => !empty($s['trunk']) ? $num($s['trunk']) . ' l' : null,
            'Emisiones'   => !empty($s['co2']) ? $s['co2'] . ' g/km' : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** the site's own @font-face rules (public/css/fonts.css), the files inlined, so
        Chrome needs nothing but the HTML file */
    private static function fontFaces(): string
    {
        $css = (string) @file_get_contents(public_path('css/fonts.css'));
        return preg_replace_callback('~url\((/fonts/[^)]+\.woff2)\)~', function ($m) {
            $f = public_path(ltrim($m[1], '/'));
            return is_file($f) ? 'url(data:font/woff2;base64,' . base64_encode(file_get_contents($f)) . ')' : $m[0];
        }, $css);
    }

    /** pending sheets nobody saved, after a day */
    public static function prune(): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files(self::PENDING) as $f) {
            if ($disk->lastModified($f) < time() - 86400) $disk->delete($f);
        }
    }
}
