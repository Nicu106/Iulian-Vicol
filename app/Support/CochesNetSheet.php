<?php

namespace App\Support;

use Symfony\Component\Process\Process;

/**
 * Reads the "Imprimir vehículo" sheet that coches.net PRO prints for a car in stock
 * (Chrome → PDF, A4, 2-3 pages) and returns everything on it as data.
 *
 * The sheet always has the same structure (checked on six of Iulian's, 2026-10-22):
 *   the dealer's block · the title (one or two lines) · Precio al contado / Precio
 *   financiado / Garantía (label, then value) · the spec grid (value, then label:
 *   Año, Kilómetros, … Etiqueta) · Equipamiento extra (optional; "· name … 414.23 €",
 *   names may wrap) · Descripción · Desglose de la oferta (new price in Spain, the
 *   extras, the total, the offer) · on every page a date header, the print URL with
 *   the stock id and the page number; under the description a disclaimer.
 * The text is read in the order Chrome wrote it (pdftotext -raw), which keeps each
 * line whole even where the disclaimer is drawn over the description. The DGT label
 * is only a picture: it is recognised by its colour and checked against the rules
 * (fuel and year).
 *
 * Nothing here throws on a bad file: parse() returns ['ok' => false, 'error' => …]
 * with a sentence for the dealer, and on a good one the fields it could read, what
 * it could not (warnings) and the sheet itself (history: new price, extras, facts).
 */
class CochesNetSheet
{
    /** the spec grid, in the sheet's order: label => key */
    private const GRID = [
        'Año' => 'year', 'Kilómetros' => 'km', 'Tipología' => 'body', 'Cambio' => 'gearbox',
        'Puertas' => 'doors', 'Plazas' => 'seats', 'Cubicaje' => 'cc', 'Potencia' => 'cv',
        'Emisiones' => 'co2', 'Color' => 'color', 'Combustible' => 'fuel', 'Maletero' => 'trunk',
        'Etiqueta' => 'label',
    ];

    /** lines that belong to the page, not to the car (exact, after trimming) */
    private const CHROME = [
        '¿Estás', 'interesado?', '¿Estás interesado?', 'Encuentra todos', 'los detalles aquí',
        'Encuentra todos los detalles aquí',
    ];

    /** marques of more than one word, as coches.net writes them (upper case) */
    private const LONG_MARQUES = [
        'MERCEDES-BENZ' => 'Mercedes-Benz', 'LAND ROVER' => 'Land Rover', 'ALFA ROMEO' => 'Alfa Romeo',
        'ASTON MARTIN' => 'Aston Martin', 'ROLLS-ROYCE' => 'Rolls-Royce', 'DS' => 'DS', 'MINI' => 'MINI',
        'BMW' => 'BMW', 'SEAT' => 'SEAT', 'CUPRA' => 'Cupra', 'KIA' => 'Kia', 'MG' => 'MG',
        'VOLKSWAGEN' => 'Volkswagen', 'SSANGYONG' => 'SsangYong', 'LYNK & CO' => 'Lynk & Co',
    ];

    /** the label's picture as coches.net draws it (md5 of the extracted PNG) */
    private const LABEL_PICTURES = [
        '6359f3dcd1c86f579c555451a34ffdef' => 'C',
    ];

    public const MAX_BYTES = 15 * 1024 * 1024;

    public static function parse(string $path): array
    {
        if (!is_file($path) || filesize($path) === 0) {
            return self::fail('El archivo ha llegado vacío. Vuelve a intentarlo.');
        }
        if (filesize($path) > self::MAX_BYTES) {
            return self::fail('El PDF pesa más de 15 MB. La ficha de coches.net pesa menos de 1 MB: ¿es el archivo correcto?');
        }
        $head = (string) file_get_contents($path, false, null, 0, 1024);
        if (!str_contains($head, '%PDF')) {
            return self::fail('Este archivo no es un PDF. Sube la ficha que descargas en coches.net con «Imprimir vehículo» → «Guardar como PDF».');
        }

        $raw = self::run(['pdftotext', '-raw', '-enc', 'UTF-8', $path, '-']);
        if ($raw === null) {
            return self::fail('No he podido abrir el PDF (puede estar dañado o protegido con contraseña).');
        }
        if (mb_strlen(trim(preg_replace('/\s+/u', ' ', $raw))) < 40) {
            return self::fail('El PDF no tiene texto: parece una foto o un escaneo. Descarga la ficha desde coches.net con «Imprimir vehículo» → «Guardar como PDF», no la fotografíes.');
        }

        $stockId = preg_match('~coches\.net/stock/vehicle/(\d+)~', $raw, $m) ? $m[1] : null;
        $lines = self::clean($raw);

        $labelsFound = count(array_intersect(array_keys(self::GRID), $lines));
        $isSheet = str_contains($raw, 'coches.net') && $labelsFound >= 3;
        if (!$isSheet && $labelsFound < 6) {
            return self::fail('No reconozco este PDF. Tiene que ser la ficha «Imprimir vehículo» de coches.net PRO de un coche.');
        }

        $warnings = [];
        $s = ['source' => 'coches.net', 'stock_id' => $stockId];

        /* ---- the title: the lines just above the first price label -------------- */
        $iPrice = self::indexOf($lines, ['Precio al contado', 'Precio financiado', 'Garantía']);
        $title = [];
        for ($i = ($iPrice ?? 0) - 1; $i >= 0 && count($title) < 3; $i--) {
            $l = $lines[$i];
            if (str_contains($l, '@') || preg_match('/^\+?[\d\s]{9,}$/', $l)) break;
            array_unshift($title, $l);
        }
        // the dealer's block is "name / address / city / phone / email": if the email was
        // missing, the walk stops at the phone; anything with a postcode is the address
        $title = array_values(array_filter($title, fn ($l) => !preg_match('/\b\d{5}\b/', $l)));
        $s['title'] = trim(preg_replace('/\s+/u', ' ', implode(' ', array_slice($title, -2))));
        if ($s['title'] === '') {
            $warnings[] = 'No encuentro el nombre del coche (marca y modelo): rellénalo a mano.';
        }

        /* ---- the prices and the warranty (label, then its value) ---------------- */
        $s['price_cash'] = self::moneyAfter($lines, 'Precio al contado');
        $s['price_financed'] = self::moneyAfter($lines, 'Precio financiado');
        $iW = array_search('Garantía', $lines, true);
        $s['warranty_months'] = null;
        if ($iW !== false) {
            for ($j = $iW + 1; $j < min($iW + 4, count($lines)); $j++) {
                if (preg_match('/^(\d+)\s*mes(es)?$/iu', $lines[$j], $mm)) { $s['warranty_months'] = (int) $mm[1]; break; }
                if (preg_match('/^(\d+)\s*años?$/iu', $lines[$j], $mm)) { $s['warranty_months'] = (int) $mm[1] * 12; }
            }
        }

        /* ---- the grid: each value stands on the line before its label ------------ */
        $grid = [];
        $gridEnd = self::indexOf($lines, ['Equipamiento extra', 'Descripción', 'Desglose de la oferta']) ?? count($lines);
        foreach ($lines as $i => $l) {
            if ($i >= $gridEnd || !isset(self::GRID[$l]) || $i === 0) continue;
            $prev = $lines[$i - 1];
            if (isset(self::GRID[$prev]) || in_array($prev, ['12 meses', '24 meses', '36 meses'], true)
                || preg_match('/^\d+\s*meses$/u', $prev)) continue;   // no value: the label follows a label
            $grid[self::GRID[$l]] = $prev;
        }
        $s['year']   = self::int($grid['year'] ?? null);
        $s['km']     = self::int($grid['km'] ?? null);
        $s['body']   = $grid['body'] ?? null;
        $s['gearbox']= $grid['gearbox'] ?? null;
        $s['doors']  = self::int($grid['doors'] ?? null);
        $s['seats']  = self::int($grid['seats'] ?? null);
        $s['cc']     = self::int($grid['cc'] ?? null);
        $s['cv']     = self::int($grid['cv'] ?? null);
        $s['co2']    = self::int($grid['co2'] ?? null);
        $s['color']  = $grid['color'] ?? null;
        $s['fuel']   = $grid['fuel'] ?? null;
        $s['trunk']  = self::int($grid['trunk'] ?? null);

        /* ---- Equipamiento extra: "· name … price", a name may wrap -------------- */
        $s['extras'] = [];
        $iE = array_search('Equipamiento extra', $lines, true);
        if ($iE !== false) {
            $end = self::indexOf($lines, ['Descripción', 'Desglose de la oferta'], $iE + 1) ?? count($lines);
            $cur = null;
            for ($j = $iE + 1; $j < $end; $j++) {
                $l = $lines[$j];
                if (preg_match('/^[·•]\s*(.*)$/u', $l, $mm)) {
                    if ($cur) $s['extras'][] = $cur;
                    $cur = ['name' => $mm[1], 'price' => null];
                    $l = '';
                    if (preg_match('/^(.*?)\s+(\d[\d.,]*)\s*€$/u', $cur['name'], $mp)) {
                        $cur['name'] = $mp[1]; $cur['price'] = self::money($mp[2]);
                    }
                    continue;
                }
                if (!$cur) continue;
                if (preg_match('/^(\d[\d.,]*)\s*€$/u', $l, $mp)) { $cur['price'] = self::money($mp[1]); continue; }
                // a wrapped name: "anti-" + "deslumbramiento", or "delantero y" + "trasero"
                $tail = '';
                if (preg_match('/^(.*?)\s+(\d[\d.,]*)\s*€$/u', $l, $mp)) { $l = $mp[1]; $tail = $mp[2]; }
                $cur['name'] = str_ends_with($cur['name'], '-') ? $cur['name'] . $l : $cur['name'] . ' ' . $l;
                if ($tail !== '') $cur['price'] = self::money($tail);
            }
            if ($cur) $s['extras'][] = $cur;
            foreach ($s['extras'] as &$x) { $x['name'] = trim(preg_replace('/\s+/u', ' ', $x['name'])); } unset($x);
        }

        /* ---- Descripción ------------------------------------------------------- */
        $s['description'] = null;
        $iD = array_search('Descripción', $lines, true);
        if ($iD !== false) {
            $end = self::indexOf($lines, ['Desglose de la oferta'], $iD + 1) ?? count($lines);
            $text = '';
            foreach (array_slice($lines, $iD + 1, $end - $iD - 1) as $l) {
                $text = $text === '' ? $l : (preg_match('/\p{L}-$/u', $text) ? $text . $l : $text . ' ' . $l);
            }
            $s['description'] = trim(preg_replace('/\s+/u', ' ', $text)) ?: null;
        }

        /* ---- Desglose de la oferta -------------------------------------------- */
        $s['new_year'] = null; $s['new_price'] = null; $s['extras_total'] = null; $s['new_total'] = null; $s['offer'] = null;
        $iB = array_search('Desglose de la oferta', $lines, true);
        if ($iB !== false) {
            for ($j = $iB + 1; $j < count($lines); $j++) {
                $l = $lines[$j];
                if (preg_match('/^PVP .*\(Año (\d{4})\)/u', $l, $mm)) {
                    $s['new_year'] = (int) $mm[1];
                    // the price is on the next line, after the car's name (or alone)
                    for ($k = $j + 1; $k < min($j + 3, count($lines)); $k++) {
                        if (preg_match('/(\d[\d.,]*)\s*€$/u', $lines[$k], $mp)) { $s['new_price'] = self::money($mp[1]); break; }
                    }
                } elseif (preg_match('/^Total equipamiento extra añadido\s+(\d[\d.,]*)\s*€$/u', $l, $mp)) {
                    $s['extras_total'] = self::money($mp[1]);
                } elseif (preg_match('/^Total\s+(\d[\d.,]*)\s*€$/u', $l, $mp)) {
                    $s['new_total'] = self::money($mp[1]);
                } elseif (preg_match('/^Precio oferta\s+(\d[\d.,]*)\s*€$/u', $l, $mp)) {
                    $s['offer'] = self::money($mp[1]);
                }
            }
        }

        /* ---- what the description tells about the car's past ------------------- */
        $s['facts'] = self::facts((string) $s['description']);

        /* ---- the DGT label: its picture, checked against the rules ------------- */
        // a mild hybrid is sold as diesel or petrol but carries ECO
        $desc = (string) $s['description'];
        $mild = (bool) preg_match('/mild[\s-]?hybrid|\bMHEV\b|microh[ií]brido/iu', $s['title'] . ' ' . $desc);
        $byRule = $mild ? 'ECO' : self::labelByRule($s['fuel'], $s['year']);
        $byPicture = self::labelFromPicture($path);
        $byText = preg_match('/etiqueta\s+(?:ambiental\s+)?(?:DGT\s+)?«?(ECO|CERO|0|C|B)\b/u', $desc, $mt) ? ($mt[1] === 'CERO' ? '0' : $mt[1]) : null;
        $s['label'] = $byPicture ?? $byText ?? $byRule;
        $other = $byText && $byText !== $s['label'] ? [$byText, 'la descripción']
               : ($byRule && $byRule !== $s['label'] ? [$byRule, $mild ? 'por ser mild hybrid' : 'por combustible y año'] : null);
        if ($other) {
            $warnings[] = "Etiqueta DGT: la ficha dice «{$s['label']}», pero {$other[1]} " . ($other[1] === 'la descripción' ? 'dice' : 'le tocaría') . " «{$other[0]}». He dejado la de la ficha: compruébala con la matrícula en sede.dgt.gob.es.";
        }

        /* ---- the car's name: marque + model ------------------------------------ */
        [$brand, $model] = self::splitTitle($s['title']);

        /* ---- checks: missing, implausible, inconsistent ------------------------ */
        $miss = [];
        foreach (['price_cash' => 'precio', 'year' => 'año', 'km' => 'kilómetros', 'fuel' => 'combustible', 'gearbox' => 'cambio', 'cv' => 'potencia'] as $k => $name) {
            if ($s[$k] === null || $s[$k] === '') $miss[] = $name;
        }
        if ($miss) {
            $warnings[] = 'No aparece en la ficha: ' . implode(', ', $miss) . '. Rellénalo a mano.';
        }
        $nowY = (int) date('Y');
        if ($s['year'] !== null && ($s['year'] < 1950 || $s['year'] > $nowY + 1)) {
            $warnings[] = "El año de la ficha ({$s['year']}) no tiene sentido; no lo he puesto.";
            $s['year'] = null;
        }
        if ($s['km'] !== null && $s['km'] > 1500000) {
            $warnings[] = "Los kilómetros de la ficha ({$s['km']}) no tienen sentido; no los he puesto.";
            $s['km'] = null;
        }
        if ($s['price_cash'] !== null && ($s['price_cash'] < 500 || $s['price_cash'] > 2000000)) {
            $warnings[] = 'El precio de la ficha (' . self::eur($s['price_cash']) . ') no tiene sentido; no lo he puesto.';
            $s['price_cash'] = null;
        }
        if ($s['cv'] !== null && ($s['cv'] < 30 || $s['cv'] > 1600)) {
            $warnings[] = "La potencia de la ficha ({$s['cv']} cv) no tiene sentido; no la he puesto.";
            $s['cv'] = null;
        }
        if ($s['offer'] !== null && $s['price_cash'] !== null && abs($s['offer'] - $s['price_cash']) >= 1) {
            $warnings[] = 'La ficha tiene dos precios distintos: ' . self::eur($s['price_cash']) . ' al contado y ' . self::eur($s['offer']) . ' en el desglose. He puesto el de al contado.';
        }
        if ($s['extras']) {
            $sum = array_sum(array_map(fn ($x) => (float) ($x['price'] ?? 0), $s['extras']));
            if ($s['extras_total'] !== null && abs($sum - $s['extras_total']) > 1) {
                $warnings[] = 'Los extras suman ' . self::eur($sum) . ' y la ficha dice ' . self::eur($s['extras_total']) . ': puede que falte alguno. Revisa la lista.';
            }
            $noPrice = count(array_filter($s['extras'], fn ($x) => $x['price'] === null));
            if ($noPrice) $warnings[] = "{$noPrice} " . ($noPrice === 1 ? 'extra no tiene' : 'extras no tienen') . ' precio en la ficha.';
        } elseif ($s['extras_total']) {
            $warnings[] = 'La ficha dice que lleva ' . self::eur($s['extras_total']) . ' en extras, pero no he podido leer la lista.';
        }
        if ($s['new_price'] !== null && $s['price_cash'] !== null && $s['new_price'] < $s['price_cash']) {
            $warnings[] = 'El precio de nuevo (' . self::eur($s['new_price']) . ') es menor que el de venta: revisa el desglose.';
        }
        if (isset($s['facts']['km']) && $s['km'] && abs($s['facts']['km'] - $s['km']) > 3000) {
            $warnings[] = 'La descripción dice ' . number_format($s['facts']['km'], 0, ',', '.') . ' km y la ficha ' . number_format($s['km'], 0, ',', '.') . ' km. Comprueba cuál es el bueno.';
        }
        $words = ['un' => 1, 'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5];
        if ($s['warranty_months'] && preg_match('/\b(\d|un|dos|tres|cuatro|cinco)\s+años?\s+de\s+garant[ií]a/iu', $desc, $mw)) {
            $y = is_numeric($mw[1]) ? (int) $mw[1] : $words[mb_strtolower($mw[1])];
            if ($y * 12 !== $s['warranty_months']) {
                $warnings[] = 'Garantía: la ficha dice ' . self::years($s['warranty_months'] / 12) . " y la descripción {$mw[0]}. Comprueba cuál es la buena.";
            }
        }
        if (isset($s['facts']['registered']) && $s['year'] && (int) substr($s['facts']['registered'], 0, 4) !== $s['year']) {
            $warnings[] = "Año: la ficha dice {$s['year']} y la descripción que se matriculó en " . str_replace('-', '/', implode('/', array_reverse(explode('-', $s['facts']['registered'])))) . '. Comprueba cuál es el bueno.';
        }
        if ($s['seats'] && preg_match('/\b([2-9])\s+plazas\b/iu', (string) $s['description'], $mp) && (int) $mp[1] !== $s['seats']) {
            $warnings[] = "La ficha dice {$s['seats']} plazas y la descripción {$mp[1]}. Comprueba cuál es la buena.";
        }
        if ($brand === null && $s['title'] !== '') {
            $warnings[] = "No conozco la marca de «{$s['title']}»: revisa marca y modelo.";
        }

        /* ---- the form's fields, in the site's own vocabulary ------------------- */
        $fields = array_filter([
            'brand' => $brand,
            'model' => $model,
            'year' => $s['year'],
            'price' => $s['price_cash'] !== null ? (int) round($s['price_cash']) : null,
            // the grid rounds to the thousand ("134000 km"); the description often has the
            // exact figure ("134.711 km"), used only when it is the same reading
            'mileage' => (isset($s['facts']['km']) && $s['km'] && abs($s['facts']['km'] - $s['km']) <= 3000) ? $s['facts']['km'] : $s['km'],
            'fuel' => self::fuel($s['fuel']),
            'transmission' => self::gearbox($s['gearbox']),
            'engine' => $s['cc'] ? number_format(round($s['cc'] / 100) / 10, 1, '.', '') : null,
            'power' => $s['cv'] ? (string) $s['cv'] : null,
            'color' => $s['color'] ? self::colour($s['color']) : null,
            'body_type' => self::body($s['body']),
            'description' => $s['description'],
        ], fn ($v) => $v !== null && $v !== '');

        $s['imported_at'] = now()->toIso8601String();

        return [
            'ok' => true,
            'fields' => $fields,
            'sheet' => $s,
            'warnings' => $warnings,
        ];
    }

    /** the main photograph of the sheet (the largest picture on page 1), as JPEG bytes */
    public static function photo(string $path): ?string
    {
        $dir = sys_get_temp_dir() . '/cn-' . bin2hex(random_bytes(6));
        @mkdir($dir);
        try {
            if (self::run(['pdfimages', '-j', '-f', '1', '-l', '1', $path, $dir . '/i']) === null) return null;
            $best = null; $area = 0;
            foreach (glob($dir . '/i-*') as $f) {
                $sz = @getimagesize($f);
                if (!$sz || $sz[0] < 600) continue;
                $r = $sz[0] / max(1, $sz[1]);
                if ($r < 1.2 || $r > 1.9) continue;            // a car's photograph is landscape
                if ($sz[0] * $sz[1] > $area) { $area = $sz[0] * $sz[1]; $best = $f; }
            }
            if (!$best) return null;
            $bytes = file_get_contents($best);
            // not a JPEG (pdfimages wrote another format): re-encode through GD
            if (!str_starts_with($bytes, "\xFF\xD8")) {
                $im = @imagecreatefromstring($bytes);
                if (!$im) return null;
                ob_start(); imagejpeg($im, null, 90); $bytes = ob_get_clean();
            }
            return $bytes;
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            @rmdir($dir);
        }
    }

    /* ------------------------------------------------------------------------ */

    private static function fail(string $msg): array
    {
        return ['ok' => false, 'error' => $msg];
    }

    private static function run(array $cmd): ?string
    {
        // PHP-FPM runs with an empty environment (no PATH): the tools by their full path
        if (!str_contains($cmd[0], '/') && is_executable('/usr/bin/' . $cmd[0])) {
            $cmd[0] = '/usr/bin/' . $cmd[0];
        }
        try {
            $p = new Process($cmd);
            $p->setTimeout(30);
            $p->run();
            return $p->isSuccessful() ? $p->getOutput() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** trimmed lines without the page furniture (header, footer, disclaimer, QR box) */
    private static function clean(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\R|\f/u', $raw) as $l) {
            $l = trim(str_replace(["\u{00A0}", "\u{202F}"], ' ', $l));
            if ($l === '' || in_array($l, self::CHROME, true)) continue;
            if (preg_match('~^\d{1,2}/\d{1,2}/\d{4},?\s+\d{1,2}:\d{2}~', $l)) continue;          // date header
            if (str_contains($l, 'Imprimir vehículo | coches.net')) continue;
            if (preg_match('~^https?://\S*coches\.net\S*(\s+\d+/\d+)?$~', $l)) continue;          // URL footer
            if (preg_match('~^\d+/\d+$~', $l)) continue;                                           // page number
            if (str_starts_with($l, '*Este documento tiene efectos') || str_contains($l, 'coches.net se exonera')) continue;
            // two labels on one baseline come out as one line ("Maletero Etiqueta")
            $words = explode(' ', $l);
            if (count($words) > 1 && !array_diff($words, array_keys(self::GRID))) {
                array_push($out, ...$words);
                continue;
            }
            $out[] = $l;
        }
        return $out;
    }

    private static function indexOf(array $lines, array $any, int $from = 0): ?int
    {
        for ($i = $from; $i < count($lines); $i++) {
            if (in_array($lines[$i], $any, true)) return $i;
        }
        return null;
    }

    private static function moneyAfter(array $lines, string $label): ?float
    {
        $i = array_search($label, $lines, true);
        if ($i === false) return null;
        for ($j = $i + 1; $j < min($i + 3, count($lines)); $j++) {
            if (preg_match('/^(\d[\d.,]*)\s*€$/u', $lines[$j], $m)) return self::money($m[1]);
        }
        return null;
    }

    /** "14.990" → 14990, "11.740,6" → 11740.6, "414.23" → 414.23, "0" → 0 */
    public static function money(?string $v): ?float
    {
        if ($v === null) return null;
        $v = preg_replace('/[^\d.,]/', '', $v);
        if ($v === '') return null;
        if (str_contains($v, ',')) return (float) str_replace(['.', ','], ['', '.'], $v);
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $v)) return (float) str_replace('.', '', $v);
        return (float) $v;
    }

    private static function int(?string $v): ?int
    {
        if ($v === null || !preg_match('/\d[\d.]*/', $v, $m)) return null;
        return (int) str_replace('.', '', $m[0]);
    }

    private static function years(float $y): string
    {
        return $y == 1 ? '1 año' : ((int) $y) . ' años';
    }

    private static function eur(float $v): string
    {
        return number_format($v, $v == floor($v) ? 0 : 2, ',', '.') . ' €';
    }

    /** what the dealer wrote about the car's past, read conservatively */
    public static function facts(string $d): array
    {
        $f = [];
        $months = ['enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6, 'julio' => 7,
                   'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12];
        if (preg_match('/matriculad[oa]\s+en\s+(\d{1,2})\/(\d{4})/iu', $d, $m)) {
            $f['registered'] = sprintf('%04d-%02d', $m[2], $m[1]);
        } elseif (preg_match('/matriculad[oa]\s+en\s+(' . implode('|', array_keys($months)) . ')\s+(?:de|del)\s+(\d{4})/iu', $d, $m)) {
            $f['registered'] = sprintf('%04d-%02d', $m[2], $months[mb_strtolower($m[1])]);
        }
        if (preg_match('/ITV[^.•|]{0,40}?(?:(\d{1,2})\/)?(20\d{2})/u', $d, $m)) {
            $f['itv'] = $m[1] ? sprintf('%04d-%02d', $m[2], $m[1]) : $m[2];
        }
        if (preg_match('/\b(dos|2|tres|3)\s+llaves\b/iu', $d, $m)) {
            $f['keys'] = ['dos' => 2, '2' => 2, 'tres' => 3, '3' => 3][mb_strtolower($m[1])];
        }
        if (preg_match('/\b(\d{1,3}(?:\.\d{3})+)\s*km\b/iu', $d, $m)) {
            $f['km'] = (int) str_replace('.', '', $m[1]);
        }
        if (preg_match('/sin\s+(?:antecedentes\s+de\s+)?(?:daños\s+ni\s+)?(?:golpes|accidentes)|ning[uú]n\s+golpe\s*\/?\s*accidente|no\s+ha\s+tenido\s+(?:ning[uú]n\s+)?accidente/iu', $d)) {
            $f['no_accidents'] = true;
        }
        if (preg_match('/(?:ning[uú]n|sin)\s+panel(?:es)?\s+repintad[oa]s?|pintura\s+original|todo\s+original/iu', $d)) {
            $f['original_paint'] = true;
        }
        if (preg_match('/(?:[uú]nico|un\s+solo|1)\s+propietario/iu', $d)) {
            $f['one_owner'] = true;
        }
        if (preg_match('/libro\s+de\s+mantenimiento|mantenimientos?\s+(?:en|al\s+d[ií]a\s+en)\s+(?:la\s+)?(?:casa|concesionario|oficial)|historial\s+de\s+mantenimiento/iu', $d)) {
            $f['service_book'] = true;
        }
        return $f;
    }

    /** DGT label by its rules (fuel + first registration year), or null when it depends on more */
    public static function labelByRule(?string $fuel, ?int $year): ?string
    {
        if (!$fuel || !$year) return null;
        $f = mb_strtolower($fuel);
        if (str_contains($f, 'eléctr') && !str_contains($f, 'híbr')) return '0';
        if (str_contains($f, 'enchufable') || str_contains($f, 'phev')) return null;   // 0 or ECO, by range
        if (str_contains($f, 'híbr') || str_contains($f, 'gas') && (str_contains($f, 'glp') || str_contains($f, 'gnc'))) return 'ECO';
        if (str_contains($f, 'diés') || str_contains($f, 'dies')) return $year >= 2015 ? 'C' : ($year >= 2006 ? 'B' : null);
        if (str_contains($f, 'gasol')) return $year >= 2006 ? 'C' : ($year >= 2001 ? 'B' : null);
        return null;
    }

    /** the label's picture: a known one, or its colour (green C, yellow B, blue 0, blue+green ECO) */
    private static function labelFromPicture(string $path): ?string
    {
        $dir = sys_get_temp_dir() . '/cnl-' . bin2hex(random_bytes(6));
        @mkdir($dir);
        try {
            if (self::run(['pdfimages', '-png', '-f', '1', '-l', '1', $path, $dir . '/i']) === null) return null;
            foreach (glob($dir . '/i-*.png') as $f) {
                $sz = @getimagesize($f);
                if (!$sz || $sz[0] > 160 || $sz[0] !== $sz[1]) continue;       // the label: small and square
                $md5 = md5_file($f);
                if (isset(self::LABEL_PICTURES[$md5])) return self::LABEL_PICTURES[$md5];
                $im = @imagecreatefrompng($f);
                if (!$im) continue;
                $hues = ['g' => 0, 'y' => 0, 'b' => 0];
                for ($x = 0; $x < $sz[0]; $x += 2) for ($y = 0; $y < $sz[1]; $y += 2) {
                    $c = imagecolorsforindex($im, imagecolorat($im, $x, $y));
                    [$r, $g, $b] = [$c['red'], $c['green'], $c['blue']];
                    $max = max($r, $g, $b); $min = min($r, $g, $b);
                    if ($max < 90 || $max - $min < 60) continue;                     // dark or grey
                    if ($g >= $r && $g > $b) $hues[$r > 170 ? 'y' : 'g']++;
                    elseif ($r > 170 && $g > 150 && $b < 100) $hues['y']++;
                    elseif ($b > $r && $b >= $g) $hues['b']++;
                }
                $n = array_sum($hues);
                if ($n < 20) continue;
                if ($hues['b'] / $n > .25 && $hues['g'] / $n > .25) return 'ECO';
                arsort($hues);
                return ['g' => 'C', 'y' => 'B', 'b' => '0'][array_key_first($hues)];
            }
            return null;
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            @rmdir($dir);
        }
    }

    /** "MERCEDES-BENZ Clase CLA CLA 200 d" → ["Mercedes-Benz", "Clase CLA CLA 200 d"] */
    public static function splitTitle(string $title): array
    {
        if ($title === '') return [null, null];
        $up = mb_strtoupper($title);
        foreach (self::LONG_MARQUES as $key => $name) {
            if (str_starts_with($up, $key . ' ')) {
                return [$name, trim(mb_substr($title, mb_strlen($key)))];
            }
        }
        $first = strtok($title, ' ');
        $rest = trim(mb_substr($title, mb_strlen($first)));
        $m = Marques::for($first);
        if ($m) return [$m['name'], $rest];
        // an unknown marque: kept as written, in title case, and flagged by the caller
        return [null, $title];
    }

    private static function fuel(?string $v): ?string
    {
        if (!$v) return null;
        $f = mb_strtolower($v);
        return match (true) {
            str_contains($f, 'diés') || str_contains($f, 'dies') => 'Diésel',
            str_contains($f, 'enchufable') => 'Híbrido enchufable',
            str_contains($f, 'híbr') || str_contains($f, 'hibr') => 'Híbrido',
            str_contains($f, 'eléctr') || str_contains($f, 'electr') => 'Eléctrico',
            str_contains($f, 'glp') => 'GLP',
            str_contains($f, 'gasol') => 'Gasolina',
            default => $v,
        };
    }

    private static function gearbox(?string $v): ?string
    {
        if (!$v) return null;
        return str_starts_with(mb_strtolower($v), 'auto') ? 'Automático' : (str_starts_with(mb_strtolower($v), 'man') ? 'Manual' : $v);
    }

    private static function body(?string $v): ?string
    {
        if (!$v) return null;
        return ['Industriales' => 'Furgoneta', 'Familiar' => 'Familiar', 'Todo Terreno' => 'Todoterreno'][$v] ?? $v;
    }

    /** "Gris / Plata" → "Gris plata" (the site writes colours as words) */
    private static function colour(string $v): string
    {
        $v = trim(preg_replace('/\s*\/\s*/u', ' ', $v));
        return mb_strtoupper(mb_substr($v, 0, 1)) . mb_strtolower(mb_substr($v, 1));
    }

    /** what the description told about the car's past, as sentences */
    public static function factLines(array $f): array
    {
        $months = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $ym = function (string $v) use ($months) {
            [$y, $m] = array_pad(explode('-', $v), 2, null);
            return $m ? $months[(int) $m] . ' de ' . $y : $y;
        };
        return array_values(array_filter([
            !empty($f['registered'])   ? 'Matriculado en ' . $ym($f['registered']) : null,
            !empty($f['one_owner'])    ? 'Un solo propietario' : null,
            !empty($f['no_accidents']) ? 'Sin accidentes' : null,
            !empty($f['original_paint']) ? 'Pintura original' : null,
            !empty($f['service_book']) ? 'Libro de mantenimiento' : null,
            !empty($f['itv'])          ? 'ITV hasta ' . $ym((string) $f['itv']) : null,
            !empty($f['keys'])         ? ($f['keys'] === 2 ? 'Dos llaves' : $f['keys'] . ' llaves') : null,
        ]));
    }
}
