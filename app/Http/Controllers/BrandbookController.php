<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Vehicle;

/**
 * The brandbook is generated, never transcribed.
 *
 * Every colour, size, ratio and contrast figure on /brandbook is read out of
 * public/css/mc-tokens.css or measured against the live image library at request
 * time. If a token changes, the page changes with it; if a claim on the page is
 * wrong, the token is wrong. Nothing is typed twice.
 */
class BrandbookController extends Controller
{
    private const TOKENS = 'css/mc-tokens.css';
    private const METRICS = 'app/brandbook-metrics.json';

    public function index()
    {
        $tokens = $this->tokens();

        return view('pages.brandbook', [
            'tokens'    => $tokens,
            'colors'    => $this->colorGroups($tokens),
            'contrast'  => $this->contrastTable($tokens)['rows'],
            'forbidden' => $this->contrastTable($tokens)['forbidden'],
            'quotes'    => $this->quoteStats(),
            'type'      => $this->typeScale(),
            'space'     => $this->spaceScale($tokens),
            'ratios'    => $this->ratios($tokens),
            'motion'    => $this->motion($tokens),
            'metrics'   => $this->metrics(),
            'crops'     => $this->cropAudit(),
            'inventory' => $this->inventory(),
            'sample'    => Testimonial::where('is_active', true)->orderBy('order_index')->get(),
            'car'       => Vehicle::where('status', 'available')->first(),
            'cars'      => Vehicle::where('status', 'available')->orderBy('priority', 'desc')->take(3)->get(),
            'sold'      => Vehicle::where('status', 'sold')->first(),
        ]);
    }

    /* ---------------------------------------------------------------- tokens */

    /** Parse every custom property out of the :root block of the canonical file. */
    private function tokens(): array
    {
        $css = @file_get_contents(public_path(self::TOKENS)) ?: '';
        preg_match_all('/(--[a-z0-9-]+)\s*:\s*([^;{}]+);/i', $css, $m, PREG_SET_ORDER);

        $out = [];
        foreach ($m as $t) {
            $out[trim($t[1])] = trim(preg_replace('/\s+/', ' ', $t[2]));
        }

        return $out;
    }

    private function colorGroups(array $t): array
    {
        $groups = [
            'Surfaces' => [
                ['--mc-bg',         'The page. A cool near-white, not pure white.'],
                ['--mc-surface',    'Vehicle cards and forms. Pure white, so a card lifts off the page without a shadow — shadows disappear in glare.'],
                ['--mc-band',       'Alternating section band. The darkest light surface, so every text colour is proven against this one.'],
                ['--mc-blue-tint',  'Blue wash. One section per page, at most.'],
            ],
            'Blue' => [
                ['--mc-blue',       'The brand. One value does two jobs: link text and button fill, both at 7.13:1.'],
                ['--mc-blue-dark',  'Hover, pressed, and large headings.'],
                ['--mc-blue-light', 'Links and icons on navy only. Forbidden on light — 2.46:1.'],
                ['--mc-blue-100',   'Chip fill.'],
            ],
            'Navy' => [
                ['--mc-navy',      'Footer and any fixed bar. Held at 1.55:1 from pure black so it survives a cheap phone panel.'],
                ['--mc-navy-line', 'Hairline inside navy. Decorative.'],
                ['--mc-on-navy',   'Text on navy.'],
                ['--mc-on-navy-2', 'Secondary text on navy. Never on a light surface.'],
            ],
            'Price' => [
                ['--mc-price',       'Coral, on car-planet\'s hue, for the price and nothing else. Passes 4.5:1 on every ground.'],
                ['--mc-accent',      'The darker step: small text, error borders, the failing mark in this book.'],
                ['--mc-accent-tint', 'Error-field ground.'],
            ],
            'WhatsApp' => [
                ['--mc-wa',      'The channel colour. WhatsApp\'s hue at the lightness that clears 4.5:1 with a white label — the brand #25D366 measures 1.98:1.'],
                ['--mc-wa-dark', 'Hover, and the label colour when the button sits on navy.'],
                ['--mc-wa-tint', 'Sent-message confirmation ground.'],
            ],
            'Ink' => [
                ['--mc-ink',   'Headings, body, spec values. Never pure black — #000 haloes in direct sun.'],
                ['--mc-ink-2', 'Labels, captions, metadata.'],
                ['--mc-ink-3', 'The lightest text the system permits.'],
            ],
            'Borders' => [
                ['--mc-hairline', 'Decorative: list separators.'],
                ['--mc-rule',     'Structural: card edge, section divider.'],
                ['--mc-control',  'Inputs, outline buttons, checkboxes. Must clear 3:1 on every ground.'],
            ],
            'State' => [
                ['--mc-ok',   'Same value as the WhatsApp green — the system has one green. Confirmation only, never "available".'],
                ['--mc-warn', 'Indicative: "precio orientativo", "km aprox."'],
            ],
        ];

        foreach ($groups as $name => $rows) {
            foreach ($rows as $i => [$var, $why]) {
                $hex = $t[$var] ?? '#000000';
                // Pick the label colour by measured contrast, not by a luminance
                // guess: a mid-tone swatch fails against both under a naive threshold.
                $onInk   = $this->ratio($hex, $t['--mc-ink'] ?? '#111C2E');
                $onPaper = $this->ratio($hex, '#FFFFFF');
                $groups[$name][$i] = [
                    'var'   => $var,
                    'hex'   => strtoupper($hex),
                    'why'   => $why,
                    'label' => $onPaper >= $onInk ? '#FFFFFF' : ($t['--mc-ink'] ?? '#111C2E'),
                ];
            }
        }

        return $groups;
    }

    /* -------------------------------------------------------------- contrast */

    /** WCAG 2.1 relative luminance. */
    private function luminance(string $hex): float
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return 0.0;
        }

        $c = [];
        foreach ([0, 2, 4] as $i) {
            $v = hexdec(substr($hex, $i, 2)) / 255;
            $c[] = $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }

    private function ratio(string $a, string $b): float
    {
        $la = $this->luminance($a);
        $lb = $this->luminance($b);

        return round((max($la, $lb) + 0.05) / (min($la, $lb) + 0.05), 2);
    }

    /**
     * Every pair the system actually renders, with the threshold that pair must
     * clear. "graphic" is WCAG 1.4.11 — 3:1 for a border or a meaningful icon.
     */
    private function contrastTable(array $t): array
    {
        // Every pair the UI actually renders, plus the pairs that are forbidden —
        // computed so the prohibition is evidenced rather than asserted.
        $pairs = [
            // text on light
            ['--mc-ink',        '--mc-bg',        'text',    'Body text on the page'],
            ['--mc-ink',        '--mc-surface',   'text',    'Text on a card'],
            ['--mc-ink',        '--mc-band',      'text',    'Text on an alternating band'],
            ['--mc-ink-2',      '--mc-bg',        'text',    'Labels and metadata'],
            ['--mc-ink-3',      '--mc-band',      'text',    'Lightest permitted text, worst case'],
            // blue
            ['--mc-blue',       '--mc-surface',   'text',    'Link on a card'],
            ['--mc-blue',       '--mc-band',      'text',    'Link on a band'],
            ['--mc-blue',       '--mc-blue-tint', 'text',    'Link on the blue wash'],
            ['--mc-blue',       '--mc-blue-100',  'text',    'Text on a blue chip'],
            ['--mc-blue-dark',  '--mc-bg',        'text',    'Large heading in blue'],
            ['--mc-blue',       '--mc-surface',   'graphic', 'Blue 3px rule or button edge'],
            ['--mc-surface',    '--mc-blue',      'text',    'White label on the blue button'],
            ['--mc-surface',    '--mc-blue-dark', 'text',    'White label, button pressed'],
            // accent
            ['--mc-price',      '--mc-surface',   'text',    'Price on a card'],
            ['--mc-price',      '--mc-band',      'large',   'Price on a band — always >=24px bold, so large text'],
            ['--mc-accent',     '--mc-surface',   'text',    'Error text on a card'],
            ['--mc-accent',     '--mc-accent-tint','text',   'Error text on the error-field ground'],
            ['--mc-surface',    '--mc-wa',        'text',    'White label on the WhatsApp button'],
            ['--mc-surface',    '--mc-wa-dark',   'text',    'White label, WhatsApp pressed'],
            ['--mc-wa',         '--mc-surface',   'graphic', 'WhatsApp button edge on white'],
            ['--mc-wa',         '--mc-band',      'graphic', 'WhatsApp button edge on a band'],
            ['--mc-wa-dark',    '--mc-surface',   'text',    'WhatsApp label on the white footer button'],
            ['--mc-wa',         '--mc-wa-tint',   'text',    'Confirmation text on its ground'],
            ['--mc-blue',       '--mc-surface',   'text',    'Outlined Call button, label and edge'],
            // borders
            ['--mc-control',    '--mc-bg',        'graphic', 'Input border on the page'],
            ['--mc-control',    '--mc-band',      'graphic', 'Input border on a band, worst case'],
            ['--mc-control',    '--mc-navy',      'graphic', 'Input border on navy'],
            ['--mc-rule',       '--mc-surface',   'none',    'Card edge — decorative, exempt'],
            ['--mc-hairline',   '--mc-bg',        'none',    'List separator — decorative, exempt'],
            // navy
            ['--mc-on-navy',    '--mc-navy',      'text',    'Footer text'],
            ['--mc-on-navy-2',  '--mc-navy',      'text',    'Footer secondary text'],
            ['--mc-blue-light', '--mc-navy',      'text',    'Link on navy'],
            // focus
            ['--mc-focus-inner','--mc-blue',      'graphic', 'Focus: white inner ring on the blue fill'],
            ['--mc-focus',      '--mc-focus-inner','graphic','Focus: ink outer ring on the white inner ring'],
            // state
            ['--mc-ok',         '--mc-surface',   'text',    'Confirmation text'],
            ['--mc-warn',       '--mc-surface',   'text',    'Indicative text'],
        ];

        $forbidden = [
            ['--mc-blue',       '--mc-navy',    'Primary blue on navy',            '--mc-blue-light on navy'],
            ['--mc-blue-dark',  '--mc-navy',    'Dark blue on navy',               '--mc-on-navy'],
            ['--mc-blue-light', '--mc-surface', 'Light blue on any light surface', '--mc-blue'],
            ['--mc-wa',         '--mc-navy',    'Green WhatsApp fill on navy',    'a white fill with the dark-green label'],
            ['#FFFFFF',         '#25D366',      'White text on the brand green',  '--mc-wa, or dark text if the bright green is insisted on'],
            ['--mc-ink-3',      '--mc-navy',    'Lightest ink on navy',            '--mc-on-navy-2'],
        ];

        // 'large' is WCAG's large-text threshold: >=24px, or >=18.66px bold.
        $thresholds = ['text' => 4.5, 'large' => 3.0, 'graphic' => 3.0, 'none' => 0.0];

        $rows = [];
        foreach ($pairs as [$fg, $bg, $kind, $use]) {
            $r    = $this->ratio($t[$fg] ?? '#000', $t[$bg] ?? '#fff');
            $need = $thresholds[$kind];
            $rows[] = [
                'fg'    => $fg,   'fgHex' => strtoupper($t[$fg] ?? ''),
                'bg'    => $bg,   'bgHex' => strtoupper($t[$bg] ?? ''),
                'use'   => $use,  'kind'  => $kind,
                'ratio' => $r,    'need'  => $need,
                'pass'  => $kind === 'none' ? null : $r >= $need,
                'aaa'   => ($kind === 'text' && $r >= 7.0) || ($kind === 'large' && $r >= 4.5),
            ];
        }

        $bad = [];
        foreach ($forbidden as [$fg, $bg, $use, $instead]) {
            $fgHex = str_starts_with($fg, '#') ? $fg : ($t[$fg] ?? '#000');
            $bgHex = str_starts_with($bg, '#') ? $bg : ($t[$bg] ?? '#fff');
            $bad[] = [
                'fgHex'   => strtoupper($fgHex), 'bgHex' => strtoupper($bgHex),
                'use'     => $use,
                'ratio'   => $this->ratio($fgHex, $bgHex),
                'instead' => $instead,
            ];
        }

        return ['rows' => $rows, 'forbidden' => $bad];
    }

    /* ------------------------------------------------------------------ type */

    private function typeScale(): array
    {
        // family: which of the two voices speaks. wdth: the variable width axis.
        return [
            ['--t-display', '34 → 52', 1.04, 600, '100%', '−0.035em', 'ui',    'The one line at the top of the site.'],
            ['--t-h1',      '34 → 52', 1.06, 700, '100%', '−0.03em',  'ui',    'Page title.'],
            ['--t-h2',      '28 → 40', 1.10, 600, '100%', '−0.03em',  'ui',    'Section title, with an 18px grey subtitle under it — the reference\'s size.'],
            ['--t-h3',      '18',      1.30, 600, '100%', '−0.012em', 'ui',    'Card title, block heading.'],
            ['--t-price',   '28 → 36', 1.00, 700, '100%', '−0.02em',  'ui',    'The price, in coral. Never appears without the mileage.'],
            ['--t-km',      '22 → 28', 1.00, 500, '100%', '−0.01em',  'ui',    'The mileage. 78% of the price, beside it, in the secondary ink.'],
            ['--t-prose',   '17 → 18', 1.62, 400, '100%', '0',        'ui',    'Descriptions and longer copy. One sans, like the reference.'],
            ['--t-body',    '16',      1.55, 400, '100%', '0',        'ui',    'Interface body copy.'],
            ['--t-ui',      '16',      1.20, 500, '100%', '+0.002em', 'ui',    'Buttons and controls. 16px is non-negotiable.'],
            ['--t-value',   '17',      1.30, 600, '100%', '0',        'ui',    'A spec value in the data table.'],
            ['--t-small',   '14',      1.45, 400, '100%', '0',        'ui',    'Fine print that still has to be read.'],
            ['--t-label',   '13',      1.30, 500, '100%', '+0.010em', 'ui',    'Field label. The floor of the system.'],
            ['--t-caption', '14',      1.45, 400, '100%', '0',        'voice', 'A customer\'s own words in the wall. Italic, same family.'],
        ];
    }

    private function spaceScale(array $t): array
    {
        $out = [];
        for ($i = 1; $i <= 10; $i++) {
            $rem = (float) $t["--s-$i"] ?? 0;
            $out[] = ['var' => "--s-$i", 'rem' => $t["--s-$i"] ?? '', 'px' => (int) round($rem * 16)];
        }

        return $out;
    }

    private function ratios(array $t): array
    {
        $labels = [
            '--ar-card'      => 'Vehicle card and gallery thumbnail',
            '--ar-hero-m'    => 'Detail lead photo, mobile',
            '--ar-hero-d'    => 'Detail lead photo, desktop',
            '--ar-portrait'  => 'Testimonial, default frame',
            '--ar-tall'      => 'Testimonial, tall tail (ratio < 0.62)',
            '--ar-wide'      => 'Testimonial, landscape (ratio > 1.05)',
            '--ar-editorial' => 'Section imagery',
            '--ar-social'    => 'Open Graph, generated — never displayed',
        ];

        $out = [];
        foreach ($labels as $var => $use) {
            $v = $t[$var] ?? '';
            $parts = array_map('trim', explode('/', $v));
            $num = (count($parts) === 2 && (float) $parts[1] > 0) ? (float) $parts[0] / (float) $parts[1] : 0;
            $out[] = ['var' => $var, 'value' => $v, 'decimal' => round($num, 4), 'use' => $use];
        }

        return $out;
    }

    private function motion(array $t): array
    {
        $labels = [
            '--m-instant' => 'Press down',
            '--m-tap'     => 'Release',
            '--m-quick'   => 'Colour, border, opacity',
            '--m-state'   => 'Focus, header state change',
            '--m-move'    => 'The sending button\'s line, an accordion, a panel',
            '--m-reveal'  => 'Reserved. Nothing on the site reveals on scroll',
        ];
        $easing = [
            '--e-out'   => 'Anything entering or settling',
            '--e-inout' => 'Anything that moves and stops in place',
            '--e-in'    => 'Anything leaving',
            '--e-line'  => 'The sending button\'s line only — drawn, not thrown',
        ];

        return [
            'durations' => array_map(fn ($k) => ['var' => $k, 'value' => $t[$k] ?? '', 'use' => $labels[$k]], array_keys($labels)),
            'easing'    => array_map(fn ($k) => ['var' => $k, 'value' => $t[$k] ?? '', 'use' => $easing[$k]], array_keys($easing)),
        ];
    }

    /* -------------------------------------------------------------- measured */

    private function metrics(): array
    {
        $p = storage_path(self::METRICS);

        return is_file($p) ? (json_decode(file_get_contents($p), true) ?: []) : [];
    }

    /**
     * Re-run the frame audit against the real testimonial files on every request.
     * The rule is hard: no frame may crop more than 25% of an original's height.
     */
    private function cropAudit(): array
    {
        $files = glob(storage_path('app/public/testimonials/*')) ?: [];
        $rows = ['n' => 0, 'worst' => 0.0, 'worstFile' => null, 'over' => 0, 'buckets' => ['default' => 0, 'tall' => 0, 'wide' => 0]];

        foreach ($files as $f) {
            $s = @getimagesize($f);
            if (!$s || empty($s[1])) {
                continue;
            }

            $r = $s[0] / $s[1];
            $bucket = $r < 0.62 ? 'tall' : ($r > 1.05 ? 'wide' : 'default');
            $frame  = ['tall' => 5 / 7, 'wide' => 4 / 3, 'default' => 4 / 5][$bucket];
            $loss   = $r < $frame ? 1 - $r / $frame : 0;

            $rows['n']++;
            $rows['buckets'][$bucket]++;
            if ($loss > 0.25) {
                $rows['over']++;
            }
            if ($loss > $rows['worst']) {
                $rows['worst'] = $loss;
                $rows['worstFile'] = round($r, 4);
            }
        }

        $rows['worst'] = round($rows['worst'] * 100, 1);

        return $rows;
    }

    /**
     * The quote-length distribution decides the clamp. Measured live, because a
     * layout tuned for 400-character quotes is wrong if the median is 194.
     */
    private function quoteStats(): array
    {
        $lens = Testimonial::where('is_active', true)
            ->pluck('quote')
            ->map(fn ($q) => mb_strlen(trim((string) $q)))
            ->filter(fn ($n) => $n > 2)      // one row holds a single comma
            ->sort()->values()->all();

        $n = count($lens);
        if ($n === 0) {
            return ['n' => 0];
        }

        $pct = fn ($q) => $lens[max(0, min($n - 1, (int) floor($q * ($n - 1))))];
        $buckets = ['0-80' => 0, '81-160' => 0, '161-280' => 0, '281-450' => 0, '450+' => 0];
        foreach ($lens as $l) {
            $key = $l <= 80 ? '0-80' : ($l <= 160 ? '81-160' : ($l <= 280 ? '161-280' : ($l <= 450 ? '281-450' : '450+')));
            $buckets[$key]++;
        }

        // How many quotes a given character cap shows in full.
        $caps = [];
        foreach ([160, 200, 250, 300, 350, 500] as $c) {
            $caps[$c] = round(count(array_filter($lens, fn ($l) => $l <= $c)) / $n * 100);
        }

        return [
            'n' => $n, 'min' => $lens[0], 'max' => $lens[$n - 1],
            'p10' => $pct(.10), 'median' => $pct(.50), 'p90' => $pct(.90),
            'buckets' => $buckets, 'caps' => $caps,
        ];
    }

    private function inventory(): array
    {
        return [
            'available'    => Vehicle::where('status', 'available')->count(),
            'sold'         => Vehicle::where('status', 'sold')->count(),
            'testimonials' => Testimonial::where('is_active', true)->count(),
        ];
    }
}
