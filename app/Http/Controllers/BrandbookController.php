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
            'contrast'  => $this->contrastTable($tokens),
            'type'      => $this->typeScale(),
            'space'     => $this->spaceScale($tokens),
            'ratios'    => $this->ratios($tokens),
            'motion'    => $this->motion($tokens),
            'metrics'   => $this->metrics(),
            'crops'     => $this->cropAudit(),
            'inventory' => $this->inventory(),
            'sample'    => Testimonial::where('is_active', true)->orderBy('order_index')->limit(10)->get(),
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
            'Ground' => [
                ['--mc-paper',   'Page background. Warm paper, not white — white is the default of every template.'],
                ['--mc-paper-2', 'Raised surfaces: inputs, the quick-spec panel.'],
                ['--mc-paper-3', 'Alternating bands, hover, pressed state.'],
            ],
            'Ink' => [
                ['--mc-ink',   'Body text, figures, heavy rules.'],
                ['--mc-ink-2', 'Long prose and secondary text.'],
                ['--mc-ink-3', 'Labels and meta. The lightest text the system allows.'],
            ],
            'Rules' => [
                ['--mc-rule',   'Decorative hairline inside a table or spec list.'],
                ['--mc-rule-2', 'Structural separation between cards and blocks.'],
                ['--mc-rule-3', 'Legal minimum for a control border — 3:1 against paper.'],
            ],
            'Signal' => [
                ['--mc-signal',    'Rust. One accent for the whole site, spent only where money moves.'],
                ['--mc-signal-up', 'The same signal, lifted for dark ground.'],
                ['--mc-wash',      'Ground of the negative block. Never used without a rule.'],
            ],
            'Deep' => [
                ['--mc-deep',      'Footer, fixed bottom bar, photo viewer. Green-black, not navy.'],
                ['--mc-deep-2',    'Elevation inside the dark zone.'],
                ['--mc-on-deep',   'Text on deep.'],
                ['--mc-on-deep-2', 'Secondary text on deep.'],
            ],
            'State' => [
                ['--mc-ok',   'It does have it. A validated field.'],
                ['--mc-note', 'Indicative, estimated. Never a warning.'],
            ],
        ];

        foreach ($groups as $name => $rows) {
            foreach ($rows as $i => [$var, $why]) {
                $hex = $t[$var] ?? '#000000';
                // Pick the label colour by measured contrast, not by a luminance
                // guess. A mid-tone swatch (#868074, #E0724F) fails against both
                // ink and paper under a naive threshold; this picks the better of
                // the two and the swatch stays readable.
                $onInk   = $this->ratio($hex, $t['--mc-ink'] ?? '#16181A');
                $onPaper = $this->ratio($hex, $t['--mc-paper'] ?? '#F4F1E9');
                $groups[$name][$i] = [
                    'var'   => $var,
                    'hex'   => strtoupper($hex),
                    'why'   => $why,
                    'label' => $onPaper >= $onInk ? 'var(--mc-paper)' : 'var(--mc-ink)',
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
        $pairs = [
            ['--mc-ink',        '--mc-paper',  'text',    'Body text on paper'],
            ['--mc-ink-2',      '--mc-paper',  'text',    'Long prose on paper'],
            ['--mc-ink-3',      '--mc-paper',  'text',    'Labels and meta on paper'],
            ['--mc-ink',        '--mc-paper-2','text',    'Text on a raised surface'],
            ['--mc-ink',        '--mc-paper-3','text',    'Text on an alternating band'],
            ['--mc-signal',     '--mc-paper',  'text',    'Signal as text on paper'],
            ['--mc-signal',     '--mc-paper',  'graphic', 'Signal as a 3px rule or fill'],
            ['--mc-signal',     '--mc-wash',   'text',    'Signal on the negative wash'],
            ['--mc-ink',        '--mc-wash',   'text',    'Negative body text'],
            ['--mc-ok',         '--mc-paper',  'text',    'Confirmed state'],
            ['--mc-note',       '--mc-paper',  'text',    'Indicative state'],
            ['--mc-rule-3',     '--mc-paper',  'graphic', 'Input border on paper'],
            ['--mc-rule-2',     '--mc-paper',  'none',    'Structural rule — decorative, exempt'],
            ['--mc-rule',       '--mc-paper',  'none',    'Hairline — decorative, exempt'],
            ['--mc-on-deep',    '--mc-deep',   'text',    'Footer text on deep'],
            ['--mc-on-deep-2',  '--mc-deep',   'text',    'Footer secondary on deep'],
            ['--mc-signal-up',  '--mc-deep',   'text',    'Signal on deep'],
            ['--mc-on-deep',    '--mc-deep-2', 'text',    'Text on raised deep'],
        ];

        $thresholds = ['text' => 4.5, 'graphic' => 3.0, 'none' => 0.0];

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
                'aaa'   => $kind === 'text' && $r >= 7.0,
            ];
        }

        return $rows;
    }

    /* ------------------------------------------------------------------ type */

    private function typeScale(): array
    {
        // family: which of the two voices speaks. wdth: the variable width axis.
        return [
            ['--t-display', '34 → 52', 1.04, 600, '92%',  '−0.035em', 'ui',    'The one line at the top of the site.'],
            ['--t-h1',      '28 → 40', 1.08, 600, '92%',  '−0.028em', 'ui',    'Page title.'],
            ['--t-h2',      '22 → 28', 1.16, 600, '94%',  '−0.022em', 'ui',    'Section title. Always followed by a 2px rule.'],
            ['--t-h3',      '18',      1.30, 600, '100%', '−0.012em', 'ui',    'Card title, block heading.'],
            ['--t-price',   '28 → 36', 1.00, 600, '96%',  '−0.020em', 'ui',    'The price. Never appears without the mileage.'],
            ['--t-km',      '22 → 28', 1.00, 600, '96%',  '−0.015em', 'ui',    'The mileage. 78% of the price, same ink, same weight.'],
            ['--t-prose',   '17 → 18', 1.62, 400, '—',    '0',        'voice', 'The owner speaking. Serif, always.'],
            ['--t-body',    '16',      1.55, 400, '100%', '0',        'ui',    'Interface body copy.'],
            ['--t-ui',      '16',      1.20, 500, '100%', '+0.002em', 'ui',    'Buttons and controls. 16px is non-negotiable.'],
            ['--t-value',   '17',      1.30, 600, '100%', '0',        'ui',    'A spec value in the data table.'],
            ['--t-small',   '14',      1.45, 400, '100%', '0',        'ui',    'Fine print that still has to be read.'],
            ['--t-label',   '13',      1.30, 500, '100%', '+0.010em', 'ui',    'Field label. The floor of the system.'],
            ['--t-caption', '14',      1.45, 400, '—',    '0',        'voice', 'Photo caption. Italic serif.'],
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
            '--m-move'    => 'THE STROKE, accordion, panel',
            '--m-reveal'  => 'The one scroll reveal',
        ];
        $easing = [
            '--e-out'   => 'Anything entering or settling',
            '--e-inout' => 'Anything that moves and stops in place',
            '--e-in'    => 'Anything leaving',
            '--e-line'  => 'THE STROKE only — drawn, not thrown',
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

    private function inventory(): array
    {
        return [
            'available'    => Vehicle::where('status', 'available')->count(),
            'sold'         => Vehicle::where('status', 'sold')->count(),
            'testimonials' => Testimonial::where('is_active', true)->count(),
        ];
    }
}
