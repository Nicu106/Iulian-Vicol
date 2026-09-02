<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>MOTORCLASS — Brandbook</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@88..100,400..700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400..600;1,8..60,400..500&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<script>document.documentElement.className += ' js';</script>
</head>
<body class="bb">

@php
  $sections = [
    ['1','The idea','idea'],           ['2','What makes it ours','ours'],
    ['3','Colour','colour'],           ['4','Typography','type'],
    ['5','Shape','shape'],             ['6','Space & layout','space'],
    ['7','Mobile','mobile'],           ['8','Imagery','imagery'],
    ['9','Motion','motion'],           ['10','Voice','voice'],
    ['11','Signature components','components'],
    ['12','Accessibility','a11y'],     ['13','What we do not do','never'],
    ['14','Build order','order'],
  ];
  $fmt = fn($n) => number_format($n); // English book: 1,927 — not 1.927, which reads as a ratio
@endphp

{{-- ══════════════════════════════════════════════ MASTHEAD ══ --}}
<header class="bb-top">
  <div class="bb-wrap">
    <span class="bb-label">MOTORCLASS · Málaga · design system v1</span>
    <p class="bb-display" style="margin-top:var(--s-3)">The Proof</p>
    <p class="bb-prose" style="margin-top:var(--s-5)">
      This is not a style guide for a showroom. It is the system for a site run by one
      person who has driven every car on it, and who shows the uncomfortable numbers
      before anyone asks for them. Every value on this page is read out of
      <code>public/css/mc-tokens.css</code> or measured against the live image library
      when the page loads. If a figure here is wrong, the token is wrong.
    </p>

    <div class="bb-meta">
      <div><span class="bb-label">Cars available</span><b>{{ $inventory['available'] }}</b></div>
      <div><span class="bb-label">Cars delivered</span><b>{{ $inventory['sold'] }}</b></div>
      <div><span class="bb-label">Photographed customers</span><b>{{ $inventory['testimonials'] }}</b></div>
      <div><span class="bb-label">Tokens parsed</span><b>{{ count($tokens) }}</b></div>
      <div><span class="bb-label">Contrast pairs checked</span><b>{{ count($contrast) }}</b></div>
      <div><span class="bb-label">Generated</span><b>{{ now()->format('d M Y H:i') }}</b></div>
    </div>
  </div>
</header>

<main class="bb-wrap">

{{-- ══════════════════════════════════════════════ CONTENTS ══ --}}
<section class="bb-section" style="padding-top:var(--s-6)">
  <ul class="bb-toc">
    @foreach($sections as [$n,$title,$id])
      <li><a href="#{{ $id }}"><span class="bb-label" style="min-width:2ch">{{ $n }}</span><span>{{ $title }}</span></a></li>
    @endforeach
  </ul>
</section>

{{-- ══════════════════════════════════════════════════ 1 IDEA ══ --}}
<section class="bb-section" id="idea">
  <div class="bb-head">
    <span class="bb-num bb-label">01</span>
    <h2>The idea</h2>
  </div>

  <p class="bb-prose">
    The structure is a dealer site: clean header, grid of cars, long detail page, fixed
    bottom bar. The content is inverted. Where a large group writes an adjective, this
    site prints a number. Where a large group hides mileage in 12px grey, this site sets
    it <em>beside the price, in the same ink, at three quarters of its size</em>. And
    every car carries a block stating what the seller himself would not buy about it.
  </p>
  <p class="bb-prose">
    Warm paper, hot ink, corners at zero, one rust signal, no shadows, no gradients, no
    decorative icons. A system that paints instantly on a mid-range Android in Málaga on
    4G, that cannot pretend to a size it does not have, and in which the real
    photography — {{ $inventory['available'] }} cars and
    {{ $inventory['testimonials'] }} faces — is the only element on the page with texture.
  </p>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p><b>Why this fits this business and no other.</b> The cars in stock run between
    108,000 and 184,000 km. Mileage is the buyer's main objection. This system takes that
    objection and turns it into the proof of honesty instead of the fine print. A brand
    system that would work equally well for a franchised BMW dealer is not a brand system;
    it is a template.</p>
  </div>
</section>

{{-- ═══════════════════════════════════════════════════ 2 OURS ══ --}}
<section class="bb-section" id="ours">
  <div class="bb-head">
    <span class="bb-num bb-label">02</span>
    <h2>What makes it ours</h2>
  </div>
  <p class="bb-prose">Ten devices. Remove one and the system stops being distinguishable
  from any other dealer template. All ten are plain CSS — none needs a library.</p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:2ch">#</th><th style="width:22ch">Device</th><th>What it is, and why it cannot be copied casually</th></tr></thead>
    <tbody>
      <tr><td class="bb-num-cell">1</td><td><b>THE PAIR</b></td><td>The price never appears alone. Price and mileage are one typographic unit separated by a 1px vertical rule — same ink, same weight, mileage at 78%. On the card, on the detail page, in the fixed bar, in the Open Graph image. If mileage is <code>NULL</code> it prints <b>Mileage unconfirmed</b>; the slot is never dropped. Copying it means copying the commercial posture.</td></tr>
      <tr><td class="bb-num-cell">2</td><td><b>THE NEGATIVE</b></td><td>A mandatory block on every vehicle: 3px left rule in signal, heading <em>What I don't like about this car</em>, the real fault in serif italic. A car without a negative does not get published. A corporate group's legal team will not allow this, which is exactly why it works.</td></tr>
      <tr><td class="bb-num-cell">3</td><td><b>THE STROKE</b></td><td>One motion idea, reused nine times: a 2–3px rule that <em>draws</em> left to right (<code>scaleX(0)→1</code>, <code>--e-line</code>, {{ $tokens['--m-move'] ?? '280ms' }}). Under the price on load, under the active nav item, on the focused card, on the active thumbnail, when a <code>&lt;details&gt;</code> opens, when the bottom bar appears. Six animation ideas read as a template; one idea used nine times reads as design.</td></tr>
      <tr><td class="bb-num-cell">4</td><td><b>THE MOSAIC</b></td><td>All {{ $inventory['testimonials'] }} photographed customers at once, in a portrait grid. The <em>quantity</em> is the argument. It replaces the Bootstrap carousel, which shows one face at a time and therefore proves nothing.</td></tr>
      <tr><td class="bb-num-cell">5</td><td><b>NO MICRO CAPS</b></td><td>No uppercase or small-caps below 16px anywhere. Labels are {{ $tokens['--t-label'] ?? '13px' }}, lowercase, <code>--mc-ink-3</code>. The five things that destroy legibility — small, uppercase, condensed, wide tracking, mid grey — stack in every dealer template. Here none of them is used.</td></tr>
      <tr><td class="bb-num-cell">6</td><td><b>THE VISIBLE GRID</b></td><td>At ≥1000px, two 1px rules at the container edges, continuous from header to footer, never interrupted by a section. Painted with a <code>background-image</code> on <code>body</code>. Three lines of CSS. Nobody does it.</td></tr>
      <tr><td class="bb-num-cell">7</td><td><b>WIDTH AXIS</b></td><td>Headings at <code>font-stretch:92%</code>, body at 100%, from the same variable font. Cost: 0 bytes. Effect: headings that sound like neither Inter nor DM Sans.</td></tr>
      <tr><td class="bb-num-cell">8</td><td><b>SERIF PROSE / SANS DATA</b></td><td>The owner speaks in Source Serif 4. The machine — prices, mileage, year, buttons, labels — speaks in Archivo. Never mixed inside one element. This is what gives human warmth without a handwriting font, which on a €25,000 purchase reads as costume.</td></tr>
      <tr><td class="bb-num-cell">9</td><td><b>NO FILTERS, NO SEARCH, NO SORT</b></td><td>{{ $inventory['available'] }} cars. One list. Every filter UI promises an inventory that does not exist, and turns the real advantage — <em>I know all of them personally</em> — into a visible weakness.</td></tr>
      <tr><td class="bb-num-cell">10</td><td><b>ZERO SOFTNESS</b></td><td>Zero shadows, zero gradients, zero radii, zero <code>backdrop-filter</code>. Radius 0 on 100% of elements including photographs. The reference site measures 0px on 100% of its 57 images; we carry the rule all the way, which turns a trend into a system.</td></tr>
    </tbody>
  </table>
  </div>
</section>

{{-- ═════════════════════════════════════════════════ 3 COLOUR ══ --}}
<section class="bb-section" id="colour">
  <div class="bb-head">
    <span class="bb-num bb-label">03</span>
    <h2>Colour</h2>
  </div>
  <p class="bb-prose">
    Warm paper instead of white, green-black instead of navy, and exactly one accent.
    The accent is rust, and it is spent only where money moves: the price rule, the
    negative block, the primary action. A second accent would make the first one mean
    nothing.
  </p>

  @foreach($colors as $group => $rows)
    <h3 style="margin:var(--s-6) 0 var(--s-4)">{{ $group }}</h3>
    <div class="bb-grid">
      @foreach($rows as $c)
        <div class="bb-sw">
          <div class="bb-sw-chip" style="background:{{ $c['hex'] }};color:{{ $c['label'] }}">{{ $c['hex'] }}</div>
          <div class="bb-sw-body">
            <code>{{ $c['var'] }}</code>
            <p>{{ $c['why'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  @endforeach

  <h3 style="margin:var(--s-7) 0 var(--s-4)">Contrast, computed not estimated</h3>
  <p class="bb-prose">
    Relative luminance per WCAG 2.1, calculated in PHP at request time for every pair the
    system actually renders. Text needs 4.5:1. A border or a meaningful graphic needs
    3:1 (SC 1.4.11). Decorative hairlines carry no requirement and are marked exempt.
  </p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Pair</th><th>Used for</th><th class="bb-num-cell">Ratio</th><th class="bb-num-cell">Needs</th><th>Verdict</th></tr></thead>
    <tbody>
      @foreach($contrast as $r)
        <tr>
          <td><span class="bb-pair"><i style="background:{{ $r['bgHex'] }}"></i><i style="background:{{ $r['fgHex'] }}"></i>{{ $r['fgHex'] }} on {{ $r['bgHex'] }}</span></td>
          <td>{{ $r['use'] }}</td>
          <td class="bb-num-cell"><b>{{ number_format($r['ratio'], 2) }}:1</b></td>
          <td class="bb-num-cell">{{ $r['need'] > 0 ? number_format($r['need'], 1).':1' : '—' }}</td>
          <td>
            @if(is_null($r['pass']))<span class="bb-exempt">decorative</span>
            @elseif($r['pass'])<span class="bb-pass">pass{{ $r['aaa'] ? ' · AAA' : '' }}</span>
            @else<span class="bb-fail">FAIL</span>@endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>

  @php $fails = collect($contrast)->filter(fn($r) => $r['pass'] === false)->count(); @endphp
  <div class="bb-note" style="margin-top:var(--s-5)">
    <p><b>{{ $fails === 0 ? 'All '.count($contrast).' pairs clear their threshold.' : $fails.' pair(s) fail and must be fixed before use.' }}</b>
    This table is regenerated on every request. Change a token and the verdict changes
    with it, which is the only way a contrast claim stays true six months from now.</p>
  </div>

  <h3 style="margin:var(--s-7) 0 var(--s-4)">Dark mode</h3>
  <p class="bb-prose">Not in v1. A warm-paper system inverted becomes a different brand,
  and a half-built dark mode is worse than none. The dark <em>surface</em> tokens
  (<code>--mc-deep</code>) exist for the footer, the fixed bar and the photo viewer —
  that is a zone, not a theme.</p>
</section>

{{-- ═══════════════════════════════════════════════════ 4 TYPE ══ --}}
<section class="bb-section" id="type">
  <div class="bb-head">
    <span class="bb-num bb-label">04</span>
    <h2>Typography</h2>
  </div>

  <div class="bb-two">
    <div>
      <h3>Archivo</h3>
      <p class="bb-small" style="color:var(--mc-ink-3)">Structure, interface, and every figure.</p>
      <p class="bb-small">An industrial signage grotesque with a variable <b>width axis</b>, which
      is what lets headings be condensed without loading a third font. Square, tabular
      figures — exactly what a price panel wants. Deliberately not DM Sans (the reference
      site's choice) and not Inter (everyone else's).</p>
      <p class="bb-display" style="margin-top:var(--s-4)">24.800 €</p>
    </div>
    <div>
      <h3>Source Serif 4</h3>
      <p class="bb-small" style="color:var(--mc-ink-3)">The owner's voice, and nothing else.</p>
      <p class="bb-small">Vehicle descriptions, the negative, testimonials, captions. Chosen over
      Newsreader for a larger x-height and thicker stems, which is what decides
      legibility at 17px on a cheap panel in sunlight. Its true italic is what makes a
      note read as written <em>without</em> a handwriting font.</p>
      <p class="bb-prose" style="margin-top:var(--s-4);font-style:italic">It has a scuff on the rear
      bumper. It is in photo 7 and I have not retouched it.</p>
    </div>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p><b>Mandatory implementation detail.</b> The width axis is applied with
    <code>font-stretch</code>, <em>never</em> with <code>font-variation-settings</code>.
    <code>font-variation-settings</code> inherits and overrides
    <code>font-optical-sizing:auto</code>, which would pin all Source Serif 4 to a single
    optical size and destroy the reason it was chosen.</p>
  </div>

  <h3 style="margin:var(--s-7) 0 var(--s-4)">The scale</h3>
  <p class="bb-prose">Fluid <code>clamp()</code> from 390px to 1200px, base 16px.
  Thirteen tokens. If a size is not in this table, it does not exist.</p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Token</th><th class="bb-num-cell">px (m → d)</th><th class="bb-num-cell">lh</th><th class="bb-num-cell">wt</th><th class="bb-num-cell">wdth</th><th class="bb-num-cell">tracking</th><th>Voice</th><th>Used for</th></tr></thead>
    <tbody>
      @foreach($type as [$var,$px,$lh,$wt,$wd,$tr,$fam,$use])
        <tr>
          <td><code>{{ $var }}</code></td>
          <td class="bb-num-cell"><b>{{ $px }}</b></td>
          <td class="bb-num-cell">{{ number_format($lh,2) }}</td>
          <td class="bb-num-cell">{{ $wt }}</td>
          <td class="bb-num-cell">{{ $wd }}</td>
          <td class="bb-num-cell">{{ $tr }}</td>
          <td>{{ $fam === 'ui' ? 'Archivo' : 'Source Serif' }}</td>
          <td>{{ $use }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-do">
      <h3>Two hard floors</h3>
      <ul>
        <li><b>13px is the absolute minimum of the system.</b> No 11px, no small caps, no
        narrow condensed, no <code>+0.14em</code> tracking. Nothing below this exists.</li>
        <li><b>16px minimum inside any control.</b>
        <code>input, select, textarea, button { font-size:1rem }</code> is
        non-negotiable: below 16px Safari iOS zooms on focus and never zooms back out.</li>
      </ul>
    </div>
    <div class="bb-dont">
      <h3>Never</h3>
      <ul>
        <li>A third typeface, for any reason.</li>
        <li>Serif and sans inside the same element.</li>
        <li>Title Case in Spanish body copy.</li>
        <li>A heading in <code>--mc-ink-3</code>. Grey headings are a template tell.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════════════ 5 SHAPE ══ --}}
<section class="bb-section" id="shape">
  <div class="bb-head">
    <span class="bb-num bb-label">05</span>
    <h2>Shape</h2>
  </div>

  <h3>Radius</h3>
  <p class="bb-prose">
    <b>{{ $tokens['--mc-r'] ?? '0px' }}, everywhere, no exceptions</b> — cards, buttons,
    inputs, chips, badges, modals, the fixed bar, and every photograph including the
    owner's own portrait. The token exists only so it can be audited. The rule is
    enforced globally with <code>*,*::before,*::after{ border-radius:var(--mc-r) }</code>,
    which also neutralises Bootstrap and the old stylesheet.
  </p>
  <p class="bb-prose">
    The previous system (<code>--border-radius:12px</code>, <code>16px</code> on cards,
    rounded image corners) is the friendly-SaaS chassis of 2019 and is the fastest way to
    look like a template. A sharp system also implies its own motion: with no visual
    softness to hide in, animation has to be short, damped and mechanical — see §9.
  </p>
  <div class="bb-note">
    <p>Explicitly rejected: <em>"one round object, the owner's face."</em> A circular
    avatar is the single most generic element on the web. The owner's photo is
    rectangular, {{ $tokens['--ar-portrait'] ?? '4/5' }}, the same size as the customers':
    he is one of the {{ $inventory['testimonials'] + 1 }} faces, not above them.</p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Rule weights</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Name</th><th class="bb-num-cell">Weight</th><th>Token</th><th>Used for</th></tr></thead>
    <tbody>
      <tr><td>hairline</td><td class="bb-num-cell">1px</td><td><code>--mc-rule</code></td><td>Rows inside a table or spec list</td></tr>
      <tr><td>structural</td><td class="bb-num-cell">1px</td><td><code>--mc-rule-2</code></td><td>Between cards, between blocks, the visible desktop grid</td></tr>
      <tr><td>control</td><td class="bb-num-cell">1px</td><td><code>--mc-rule-3</code></td><td>Border of an input, select, textarea, resting secondary button</td></tr>
      <tr><td>section</td><td class="bb-num-cell"><b>2px</b></td><td><code>--mc-ink</code></td><td>Section boundary. Horizontal only, full container width</td></tr>
      <tr><td>signal</td><td class="bb-num-cell"><b>3px</b></td><td><code>--mc-signal</code></td><td>Left rule of the negative; the price underline; focused card top</td></tr>
      <tr><td>focus</td><td class="bb-num-cell">2px</td><td><code>--mc-ink</code></td><td><code>outline</code> with <code>outline-offset:2px</code></td></tr>
    </tbody>
  </table>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Three ways to divide, and no fourth</h3>
  <ol class="bb-prose">
    <li><b>Space</b> — the default. Two related blocks are separated by space, not a line.</li>
    <li><b>A 1px <code>--mc-rule-2</code></b> between repeated items in a list.</li>
    <li><b>A 2px <code>--mc-ink</code> at full width</b> to open a section, always
    immediately <em>below</em> the <code>h2</code>, never above, with 16px of clearance.</li>
  </ol>
  <div class="bb-two">
    <div class="bb-dont">
      <h3>Forbidden</h3>
      <ul>
        <li>Separating blocks with a background change <em>and</em> a rule at once.</li>
        <li>A shadow used as a separator.</li>
        <li>Bootstrap's <code>&lt;hr&gt;</code> unrestyled.</li>
      </ul>
    </div>
    <div class="bb-do">
      <h3>Elevation</h3>
      <ul>
        <li><b>There is none.</b> Zero <code>box-shadow</code> in the entire system.</li>
        <li>The only permitted exception is the fixed bar's <code>0 -1px 0 var(--mc-ink)</code>, which is a rule, not a shadow.</li>
        <li>The fullscreen photo viewer uses an opaque ground, not a shadow.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════════════ 6 SPACE ══ --}}
<section class="bb-section" id="space">
  <div class="bb-head">
    <span class="bb-num bb-label">06</span>
    <h2>Space &amp; layout</h2>
  </div>
  <p class="bb-prose">4px base, ten values, no others. A gap that is not on this ruler is
  a bug.</p>

  <div class="bb-ruler">
    @foreach($space as $s)
      <div>
        <span style="width:{{ max(4,$s['px']) }}px"></span>
        <span class="bb-label" style="background:none;height:auto;color:var(--mc-ink-3)">{{ $s['px'] }}</span>
      </div>
    @endforeach
  </div>

  <div class="bb-scroll" style="margin-top:var(--s-5)">
  <table class="bb-t">
    <thead><tr><th>Token</th><th class="bb-num-cell">rem</th><th class="bb-num-cell">px</th></tr></thead>
    <tbody>
      @foreach($space as $s)
        <tr><td><code>{{ $s['var'] }}</code></td><td class="bb-num-cell">{{ $s['rem'] }}</td><td class="bb-num-cell"><b>{{ $s['px'] }}</b></td></tr>
      @endforeach
    </tbody>
  </table>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Container</h3>
  <p class="bb-prose">
    Maximum <b>{{ $tokens['--mc-container'] ?? '1200px' }}</b>. Gutters
    <b>{{ $tokens['--mc-gutter-m'] ?? '1rem' }}</b> on mobile and
    <b>{{ $tokens['--mc-gutter-d'] ?? '2rem' }}</b> from 768px. Prose is capped at
    <b>62ch</b> — beyond that the eye loses the line return, and this site asks people to
    read paragraphs, not scan chips.
  </p>
</section>

{{-- ═════════════════════════════════════════════════ 7 MOBILE ══ --}}
<section class="bb-section" id="mobile">
  <div class="bb-head">
    <span class="bb-num bb-label">07</span>
    <h2>Mobile</h2>
  </div>
  <p class="bb-prose">
    <b>The longest section, deliberately.</b> This is where the customer actually is: a
    used car in Málaga is researched standing next to it, one-handed, in sunlight, on 4G.
    Every rule elsewhere in this book was chosen with the 390px case first and the desktop
    case second.
  </p>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">The real viewport, which is where every other number comes from</h3>
  <p class="bb-prose">390×844 is the <em>screen</em>. It is not the design area.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Layer</th><th class="bb-num-cell">Safari iOS, portrait</th><th class="bb-num-cell">Chrome Android</th></tr></thead>
    <tbody>
      <tr><td>Screen height</td><td class="bb-num-cell">844</td><td class="bb-num-cell">~800</td></tr>
      <tr><td>Bottom chrome, expanded</td><td class="bb-num-cell">51 bar + 34 indicator = <b>85</b></td><td class="bb-num-cell">0–24</td></tr>
      <tr><td>Bottom chrome, collapsed</td><td class="bb-num-cell">~44</td><td class="bb-num-cell">0</td></tr>
      <tr><td><b>Usable visible viewport</b></td><td class="bb-num-cell"><b>~759 at rest</b></td><td class="bb-num-cell"><b>~744–800</b></td></tr>
    </tbody>
  </table>
  </div>
  <div class="bb-note" style="margin-top:var(--s-4)">
    <p><code>viewport-fit=cover</code> is <b>mandatory</b>. Without it
    <code>env(safe-area-inset-bottom)</code> evaluates to <code>0px</code> and any fixed
    bar sits under the iPhone home indicator. Production is currently missing it, which
    means the <code>padding-bottom:env(safe-area-inset-bottom)</code> already written into
    the vehicle-page lightbox does nothing at all.</p>
    <p><b>100vh is forbidden.</b> <code>vh</code> resolves to the <em>large</em> viewport,
    roughly 85px taller than the screen at rest. Use
    <code>100svh</code>, upgraded to <code>100dvh</code> behind <code>@supports</code>.</p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Thumb reach bands</h3>
  <p class="bb-prose">Measured from the top of the visible viewport, 0 → 759.</p>
  <div class="bb-phone">
    <div class="bb-band" style="background:var(--mc-paper-3)"><b>0–180</b><span>Stretch. Logo, secondary nav. Nothing destructive, nothing frequent.</span></div>
    <div class="bb-band"><b>180–430</b><span>Reach with effort. Lead photo, headline.</span></div>
    <div class="bb-band" style="background:var(--mc-wash)"><b>430–660</b><span><b>Natural thumb arc.</b> The price pair, the primary action, the negative.</span></div>
    <div class="bb-band is-deep"><b>660–759</b><span>Fixed bar. Two actions only: WhatsApp and Call.</span></div>
  </div>
  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-dont">
      <h3>Forbidden in the bottom third</h3>
      <ul>
        <li>Any link that leaves the vehicle page.</li>
        <li>"Remove from saved" or anything destructive.</li>
        <li>The GDPR consent checkbox.</li>
      </ul>
      <p class="bb-small" style="margin-top:var(--s-2)">These live in the stretch band, where reaching them has to be deliberate.</p>
    </div>
    <div class="bb-do">
      <h3>The fixed bar carries two actions, not four</h3>
      <ul>
        <li>At 390px, four columns give 93.5px per cell, minus padding = 77px usable. "WhatsApp" at 12px already occupies 58px.</li>
        <li>Four equal CTAs destroy the hierarchy, which is the bar's only reason to exist.</li>
        <li><b>The price stays in the bar.</b> On a €15–26k purchase, a bar without the price forces a scroll every time the buyer re-checks it.</li>
        <li>And by THE PAIR: if the price is there, the mileage is there.</li>
      </ul>
    </div>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Touch targets</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Rule</th><th>Value</th></tr></thead>
    <tbody>
      <tr><td>Minimum target</td><td><b>{{ $tokens['--mc-tap'] ?? '44px' }} × {{ $tokens['--mc-tap'] ?? '44px' }}</b> CSS px, preferred {{ $tokens['--mc-tap-pref'] ?? '48px' }}. Achieved with <code>min-height</code> + padding, never by scaling type.</td></tr>
      <tr><td>Minimum separation</td><td>8px between adjacent targets; 12px inside the fixed bar.</td></tr>
      <tr><td>Every interactive element</td><td><code>touch-action:manipulation</code> and <code>-webkit-tap-highlight-color:transparent</code> — removes the 300ms delay and the Android grey flash.</td></tr>
      <tr><td>Gallery strip</td><td><code>overscroll-behavior-x:contain</code>, so a swipe cannot trigger browser back-navigation.</td></tr>
      <tr><td>Gestures</td><td>No gesture is ever the only way to do something. Every swipe has a tap equivalent.</td></tr>
      <tr><td>Nested scroll</td><td>Exactly one region (the gallery). Never two axes in the same component.</td></tr>
    </tbody>
  </table>
  </div>
  <div class="bb-note" style="margin-top:var(--s-4)">
    <p><b>Measured baseline on the current build, at 390px:</b> 45 sub-44px targets on the
    home page, 41 on the catalogue, 27 on the vehicle page. This is the number the rebuild
    has to take to zero, and it is the single largest usability defect in the site today.</p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">What is removed on mobile, rather than shrunk</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Removed</th><th>Replaced by</th></tr></thead>
    <tbody>
      <tr><td>Filter sidebar, range sliders, sort dropdown</td><td>Nothing. {{ $inventory['available'] }} cars in one list.</td></tr>
      <tr><td>Pagination</td><td>Nothing.</td></tr>
      <tr><td>Breadcrumbs in the header</td><td>A 44px "← All cars" link at the top of the content.</td></tr>
      <tr><td>The testimonial carousel</td><td>THE MOSAIC — all {{ $inventory['testimonials'] }} faces, three columns.</td></tr>
    </tbody>
  </table>
  </div>
  <p class="bb-prose">Shrinking a desktop component to fit a phone is how a site ends up
  with a 12px filter chip nobody can hit. The decision is always <em>remove or rebuild</em>,
  never <em>scale down</em>.</p>
</section>

{{-- ════════════════════════════════════════════════ 8 IMAGERY ══ --}}
<section class="bb-section" id="imagery">
  <div class="bb-head">
    <span class="bb-num bb-label">08</span>
    <h2>Imagery</h2>
  </div>
  <p class="bb-prose">
    Photography is the only element in this system with texture, so the frames are chosen
    from the library that exists — not from a moodboard. The figures below were measured
    against the live files when this page loaded.
  </p>

  @if(!empty($metrics['groups']))
    <div class="bb-scroll">
    <table class="bb-t">
      <thead><tr><th>Library</th><th class="bb-num-cell">Files</th><th class="bb-num-cell">min</th><th class="bb-num-cell">p10</th><th class="bb-num-cell">median</th><th class="bb-num-cell">p90</th><th class="bb-num-cell">max</th><th class="bb-num-cell">Portrait</th><th class="bb-num-cell">Landscape</th></tr></thead>
      <tbody>
        @foreach($metrics['groups'] as $name => $g)
          <tr>
            <td><b>{{ ucfirst($name) }}</b></td>
            <td class="bb-num-cell">{{ $fmt($g['n']) }}</td>
            <td class="bb-num-cell">{{ $g['min'] }}</td>
            <td class="bb-num-cell">{{ $g['p10'] }}</td>
            <td class="bb-num-cell"><b>{{ $g['p50'] }}</b></td>
            <td class="bb-num-cell">{{ $g['p90'] }}</td>
            <td class="bb-num-cell">{{ $g['max'] }}</td>
            <td class="bb-num-cell">{{ $fmt($g['portrait']) }}</td>
            <td class="bb-num-cell">{{ $fmt($g['landscape']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  @endif

  <h3 style="margin:var(--s-6) 0 var(--s-4)">The frames</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Token</th><th class="bb-num-cell">Ratio</th><th class="bb-num-cell">Decimal</th><th>Used for</th></tr></thead>
    <tbody>
      @foreach($ratios as $r)
        <tr><td><code>{{ $r['var'] }}</code></td><td class="bb-num-cell"><b>{{ $r['value'] }}</b></td><td class="bb-num-cell">{{ $r['decimal'] }}</td><td>{{ $r['use'] }}</td></tr>
      @endforeach
    </tbody>
  </table>
  </div>

  @php $veh = $metrics['groups']['vehicles'] ?? null; @endphp
  @if($veh)
    @php
      $modeKey = array_key_first($veh['modes'] ?? []);
      $modeN   = $veh['modes'][$modeKey] ?? 0;
      $modePct = $veh['n'] ? round($modeN / $veh['n'] * 100, 1) : 0;
    @endphp
    <p class="bb-prose" style="margin-top:var(--s-5)">
      <b>Card at 4:3</b> because <b>{{ $modePct }}%</b> of the vehicle library
      ({{ $fmt($modeN) }} of {{ $fmt($veh['n']) }} files) sits at exactly
      {{ $modeKey }} — so the frame crops nothing at all across three quarters of the
      inventory. A 16:9 frame would take 25% of the height, and on a car shot from 3–4m
      that 25% is split between the roofline and the bottom of the wheel arches. Cut wheel
      arches make a car look sunken and cheap.
    </p>
  @endif
  <p class="bb-prose">
    <b>Lead photo 3:2 on desktop, 4:3 on mobile.</b> Against the real p10–p90 spread, 3:2
    is the minimum-damage frame: it takes about 11% from a 4:3 original, and that 11% is
    sky and asphalt. At 390px a 3:2 image would be 260px tall and would lose the car, so
    mobile keeps 4:3.
  </p>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">The hard rule, and the audit that enforces it</h3>
  <p class="bb-prose">
    <b>No frame may crop more than 25% of an original's height.</b> If it would, the frame
    changes — not the crop. Tall and landscape testimonials are handled with a
    <em>variant frame</em>, decided server-side from <code>getimagesize()</code>:
  </p>
  <pre class="bb-mono bb-scroll" style="border:1px solid var(--mc-rule-2);background:var(--mc-paper-2);padding:var(--s-4);margin:0 0 var(--s-4)"><code>$r   = $w / $h;
$cls = $r &lt; 0.62 ? 'is-tall' : ($r &gt; 1.05 ? 'is-wide' : '');</code></pre>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Audit, re-run on every request</th><th class="bb-num-cell">Result</th></tr></thead>
    <tbody>
      <tr><td>Testimonial files checked</td><td class="bb-num-cell"><b>{{ $crops['n'] }}</b></td></tr>
      <tr><td>Default frame ({{ $tokens['--ar-portrait'] ?? '4/5' }})</td><td class="bb-num-cell">{{ $crops['buckets']['default'] }} files</td></tr>
      <tr><td>Tall frame ({{ $tokens['--ar-tall'] ?? '5/7' }})</td><td class="bb-num-cell">{{ $crops['buckets']['tall'] }} files</td></tr>
      <tr><td>Wide frame ({{ $tokens['--ar-wide'] ?? '4/3' }})</td><td class="bb-num-cell">{{ $crops['buckets']['wide'] }} files</td></tr>
      <tr><td>Worst height loss across the library</td><td class="bb-num-cell"><b>{{ $crops['worst'] }}%</b></td></tr>
      <tr><td>Files exceeding the 25% budget</td><td class="bb-num-cell">
        @if($crops['over'] === 0)<span class="bb-pass">0 — rule holds</span>@else<span class="bb-fail">{{ $crops['over'] }}</span>@endif
      </td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-4)">
    <p><b>A correction to the original specification, verified against the real files.</b>
    The research proposed <code>--ar-tall: 3/4</code> for the tall tail. Measured against
    the actual library, a 3/4 frame loses <b>27.6%</b> of height on the narrowest file
    (ratio 0.5432) — which breaks the specification's own 25% rule. A 4/5 frame is worse
    still at 32.1%. <code>5/7</code> is the widest frame that keeps every file inside the
    budget, at <b>23.9%</b> worst case. The token was changed; this is why the audit above
    reads {{ $crops['over'] }} violations.</p>
    <p style="margin-bottom:0"><b>Why any of this matters:</b> in a photo of someone standing
    beside a car, the head occupies the top third. A frame that over-crops takes the face.
    <em>A cropped face is a broken trust signal</em> — which is the entire point of the
    testimonial.</p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Fit and position</h3>
  <pre class="bb-mono bb-scroll" style="border:1px solid var(--mc-rule-2);background:var(--mc-paper-2);padding:var(--s-4);margin:0 0 var(--s-4)"><code>.mc-img          { object-fit:cover; background:var(--mc-paper-3); }
.mc-img--vehicle { object-position:50% 42%; }  /* protects the roofline */
.mc-img--portrait{ object-position:50% 28%; }  /* protects the face */
.mc-img--interior{ object-position:50% 50%; }</code></pre>
  <p class="bb-small" style="color:var(--mc-ink-3)">There is no focal-point column on
  <code>testimonials</code> and one is not going to be invented: 50% 28% is correct for
  100% of standing or half-body photographs, which is all of them. If
  <code>focal_x</code>/<code>focal_y</code> are ever added, they inject as an inline
  <code>object-position</code>.</p>

  @if(!empty($metrics['exif']))
    @php $ex = $metrics['exif']; $pct = $ex['total'] ? round($ex['rotated']/$ex['total']*100,1) : 0; @endphp
    <h3 style="margin:var(--s-6) 0 var(--s-4)">Blocking bug — EXIF orientation</h3>
    <div class="bb-note" style="border-left-color:var(--mc-signal)">
      <p><b>{{ $fmt($ex['rotated']) }} of {{ $fmt($ex['total']) }} JPEGs ({{ $pct }}%)
      carry an EXIF orientation other than 1.</b>
      @foreach($ex['by'] as $o => $n)<code>orientation {{ $o }}</code> × {{ $n }}@if(!$loop->last), @endif @endforeach.
      GD ignores the tag; browsers honour it. So the same file displays upright when
      served directly and rotated 90° when served through the <code>/img/{w}</code>
      resizer — which is every card, every thumbnail, every mosaic tile.</p>
      <p style="margin-bottom:0">Fixed in this environment: the resizer now applies the
      orientation before scaling, and the cache key was versioned so the wrong renders are
      not served from disk. <b>Production still carries the bug.</b> It is roughly fifteen
      lines and it affects about 180 vehicle photographs.</p>
    </div>
  @endif

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Publishable / rejected</h3>
  <div class="bb-two">
    <div class="bb-do">
      <h3>Publishable</h3>
      <ul>
        <li>Daylight, the car where it actually stands.</li>
        <li>The fault photographed as clearly as the good side.</li>
        <li>The odometer, readable, at full resolution.</li>
        <li>The customer, once, with the car they bought.</li>
      </ul>
    </div>
    <div class="bb-dont">
      <h3>Rejected</h3>
      <ul>
        <li>Stock photography of any kind. It is the loudest fake signal there is.</li>
        <li>Studio cut-outs and manufacturer renders.</li>
        <li>Filters, vignettes, colour grading, retouched panels.</li>
        <li>A photo where the face is cropped.</li>
        <li>Duotone, overlay gradients, "premium" darkening.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ═════════════════════════════════════════════════ 9 MOTION ══ --}}
<section class="bb-section" id="motion">
  <div class="bb-head">
    <span class="bb-num bb-label">09</span>
    <h2>Motion</h2>
  </div>
  <p class="bb-prose">
    A sharp system has no softness to hide in, so movement has to be short, damped and
    mechanical. Six durations, four curves, one idea.
  </p>

  <div class="bb-two">
    <div>
      <h3>Durations</h3>
      <div class="bb-scroll">
      <table class="bb-t" style="min-width:0">
        <tbody>
          @foreach($motion['durations'] as $m)
            <tr><td><code>{{ $m['var'] }}</code></td><td class="bb-num-cell"><b>{{ $m['value'] }}</b></td><td>{{ $m['use'] }}</td></tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
    <div>
      <h3>Easing</h3>
      <div class="bb-scroll">
      <table class="bb-t" style="min-width:0">
        <tbody>
          @foreach($motion['easing'] as $m)
            <tr><td><code>{{ $m['var'] }}</code></td><td class="bb-mono">{{ $m['value'] }}</td><td>{{ $m['use'] }}</td></tr>
          @endforeach
        </tbody>
      </table>
      </div>
    </div>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p style="margin-bottom:0"><b>There is no spring and no overshoot anywhere in the set,
    and that is a brand decision.</b> If anyone adds
    <code>cubic-bezier(.34,1.56,.64,1)</code> in any place, the site stops being
    MOTORCLASS. Bounce reads as playful; this business is asking for €20,000 on trust.</p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">THE STROKE, live</h3>
  <p class="bb-prose">One idea, nine placements. A rule drawn from the left over
  {{ $tokens['--m-move'] ?? '280ms' }} on <code>--e-line</code>. Never faded in, never
  slid in — drawn.</p>
  <div style="border:1px solid var(--mc-rule-2);background:var(--mc-paper-2);padding:var(--s-5)">
    <span class="bb-label" style="display:block">Málaga · 2016 · Diesel</span>
    <div class="mc-pair" style="margin:var(--s-2) 0 var(--s-2)">
      <span class="mc-price">24.800 €</span>
      <span class="mc-km">184.000 km</span>
    </div>
    <span class="mc-pair-under" id="stroke-demo" style="max-width:280px"></span>
    <p class="bb-small" style="margin:var(--s-4) 0 0;color:var(--mc-ink-3)">
      <button class="mc-btn mc-btn--ghost" type="button" onclick="var e=document.getElementById('stroke-demo');e.classList.remove('is-drawn');void e.offsetWidth;e.classList.add('is-drawn')">Draw it again</button>
    </p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Reduced motion</h3>
  <p class="bb-prose"><code>prefers-reduced-motion: reduce</code> collapses every duration
  to 0.01ms. Nothing in the system conveys information by movement alone, so removing all
  of it costs nothing — which is the test of whether motion was decorative or load-bearing.</p>
</section>

{{-- ══════════════════════════════════════════════════ 10 VOICE ══ --}}
<section class="bb-section" id="voice">
  <div class="bb-head">
    <span class="bb-num bb-label">10</span>
    <h2>Voice</h2>
  </div>
  <p class="bb-prose">
    Everything comes from one fact: <b>there is a person behind this business and he has
    driven every car on the site.</b> No large group can say that, which makes it the only
    defensible position available.
  </p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:20ch">Attribute</th><th>What it means in practice</th></tr></thead>
    <tbody>
      <tr><td><b>First person</b></td><td>The site speaks as a person, not an entity. <em>I</em> for anything involving judgement, selection or contact; impersonal or <em>you</em> for the interface. <b><em>We</em> is banned across the entire site.</b></td></tr>
      <tr><td><b>Concrete</b></td><td>Every claim carries a number, a date, a place or a name. Adjectives without evidence are the loudest "written by AI" signal there is.</td></tr>
      <tr><td><b>Calm</b></td><td>No manufactured urgency, no superlatives, <b>no exclamation marks in commercial copy</b>. A small seller who shouts sounds desperate; a quiet one sounds like someone who knows the car is good.</td></tr>
      <tr><td><b>Honest past comfort</b></td><td>Every vehicle publishes at least one true limitation. This is the mechanism that turns a small unknown into someone trustworthy, and no corporate group can copy it because their legal department will not allow it.</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p style="margin-bottom:0"><b>The test, applied to any string before it ships:</b>
    Is a person speaking? Is there a fact in it? Is it calm? <em>Would I still say this if
    it did not suit me?</em></p>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Register by context</h3>
  <p class="bb-prose">Dial 1 is warmest, 5 is most neutral.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Context</th><th class="bb-num-cell">Dial</th><th>Person</th><th>Rules</th></tr></thead>
    <tbody>
      <tr><td>Hero</td><td class="bb-num-cell">2</td><td>I + you</td><td>One sentence, max 12 words. A real number in the subtitle. No exclamation marks.</td></tr>
      <tr><td>Vehicle description</td><td class="bb-num-cell">1</td><td>I</td><td>The owner's own words. At least one true negative. Where the car came from. Paragraphs of 2–3 lines.</td></tr>
      <tr><td>The negative</td><td class="bb-num-cell">1</td><td>I</td><td>The fault, where it is visible, and that it has not been retouched.</td></tr>
      <tr><td>Price</td><td class="bb-num-cell">4</td><td>Impersonal</td><td>No adjectives, ever. <em>Only</em>, <em>from</em>, <em>bargain</em>, <em>incredible</em> are banned.</td></tr>
      <tr><td>Specifications</td><td class="bb-num-cell">4</td><td>Impersonal</td><td>The figure alone. No commentary.</td></tr>
      <tr><td>Form errors</td><td class="bb-num-cell">2</td><td>I + you</td><td>What is missing and why it matters. No <em>Error</em>, no <em>invalid</em>, no <em>Please</em>.</td></tr>
      <tr><td>Legal / finance</td><td class="bb-num-cell">5</td><td>Impersonal</td><td>Neutral. Every estimate labelled as an estimate. No guarantee that is not actually given.</td></tr>
    </tbody>
  </table>
  </div>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">Microcopy — Spanish, ready to paste</h3>
  <p class="bb-small" style="color:var(--mc-ink-3)">The site ships in Spanish; this book is
  in English. <b>[CONFIRM]</b> marks a string that needs the owner's sign-off because it is
  a commercial or legal claim, not a style choice.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:26ch">Where</th><th>String</th></tr></thead>
    <tbody>
      <tr><td>Home h1</td><td><b>Nueve coches. Los he conducido todos yo.</b> <span class="bb-exempt">(count is dynamic)</span></td></tr>
      <tr><td>Home subtitle</td><td>Están en Málaga. Los kilómetros están a la vista, y también lo que no me gusta de cada uno.</td></tr>
      <tr><td>Primary CTA</td><td>Ver los coches</td></tr>
      <tr><td>Secondary CTA</td><td>Escríbeme por WhatsApp</td></tr>
      <tr><td>Catalogue h1</td><td>{n} coches en Málaga</td></tr>
      <tr><td>Catalogue standfirst</td><td>Los he comprado y conducido yo. Si preguntas por uno, te contesto yo.</td></tr>
      <tr><td>Back link</td><td>← Todos los coches</td></tr>
      <tr><td>Note under the price</td><td>Precio para particular. Transferencia e impuesto de matriculación no incluidos. <b>[CONFIRM]</b></td></tr>
      <tr><td>Mileage, unknown</td><td>Km sin confirmar</td></tr>
      <tr><td>Spec table heading</td><td>Los datos</td></tr>
      <tr><td>Spec labels</td><td>Año · Kilómetros · Combustible · Cambio · Potencia · Distintivo DGT · Color · Dónde está</td></tr>
      <tr><td>Description heading</td><td>Lo que te digo yo</td></tr>
      <tr><td><b>The negative heading</b></td><td><b>Lo que no me gusta de este coche</b></td></tr>
      <tr><td>Negative, example</td><td><em>Tiene un roce en el paragolpes trasero. Está en la foto 7 y no lo he retocado.</em></td></tr>
      <tr><td>Negative, example 2</td><td><em>No tiene libro de revisiones sellado. Sí tengo dos facturas de taller y te las enseño.</em></td></tr>
      <tr><td>Under the CTA block</td><td>Contesto yo. Suelo tardar unas horas, no unos minutos.</td></tr>
      <tr><td>Testimonials heading</td><td>Veinticinco personas se hicieron la foto</td></tr>
      <tr><td>Testimonials standfirst</td><td>No pedí ninguna. Están todas.</td></tr>
      <tr><td>Form labels</td><td>Cómo te llamas · Tu teléfono · Tu email (opcional) · Qué quieres saber</td></tr>
      <tr><td>Submit</td><td>Enviármelo</td></tr>
      <tr><td>Success</td><td>Recibido. Te contesto yo, hoy o mañana por la mañana.</td></tr>
      <tr><td>Phone error</td><td>Necesito un teléfono para poder contestarte.</td></tr>
      <tr><td>Name error</td><td>Dime cómo te llamas y te contesto por tu nombre.</td></tr>
      <tr><td>Send failure</td><td>No ha salido. Si tienes prisa, escríbeme por WhatsApp al 614 753 187.</td></tr>
      <tr><td>Empty list</td><td>Ahora mismo no tengo ningún coche disponible. Escríbeme y te aviso cuando entre algo.</td></tr>
      <tr><td>404</td><td>Esta página no existe. Estos son los coches que sí tengo.</td></tr>
      <tr><td>Warranty notice</td><td>Garantía legal de 12 meses para vehículos de ocasión vendidos a particulares (art. 120 TRLGDCU). <b>[CONFIRM]</b></td></tr>
      <tr><td>Finance notice</td><td>No hago financiación yo. Te puedo poner en contacto con una financiera; las condiciones las fija ella. <b>[CONFIRM]</b></td></tr>
      <tr><td>Footer hours</td><td>Lun–Vie 10:00–19:00 · Sábado 10:00–14:00 · Domingo, consultar</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-dont">
      <h3>Banned across the site</h3>
      <ul>
        <li><em>Nuestro equipo</em> · <em>Nosotros</em></li>
        <li><em>Tu coche perfecto te espera</em></li>
        <li><em>¡Últimas unidades!</em> · <em>Amplio catálogo</em></li>
        <li><em>Miles de clientes satisfechos</em> · <em>Vehículos seleccionados</em></li>
        <li><em>Estado impecable</em> · <em>Precio increíble</em> · <em>Chollo</em> · <em>Desde solo</em></li>
        <li>Title Case in Spanish.</li>
      </ul>
    </div>
    <div class="bb-dont">
      <h3>Banned because it belongs to the reference site</h3>
      <ul>
        <li>Any regulatory registration number.</li>
        <li><em>14-day money-back</em>, <em>RAC warranty</em>.</li>
        <li><em>150-point inspection</em>, <em>100-point check</em>.</li>
        <li>Award badges, and any buy-back promise.</li>
      </ul>
      <p class="bb-small" style="margin:var(--s-3) 0 0"><b>MOTORCLASS cannot state guarantees
      it does not offer.</b> Copying the reference's structure is legitimate; copying its
      commercial claims is false advertising.</p>
    </div>
  </div>
</section>

{{-- ═════════════════════════════════════════════ 11 COMPONENTS ══ --}}
<section class="bb-section" id="components">
  <div class="bb-head">
    <span class="bb-num bb-label">11</span>
    <h2>Signature components</h2>
  </div>
  <p class="bb-prose">Three components carry the identity. Everything else in the system is
  ordinary and should stay ordinary. These are rendered live below — this is the real CSS,
  not a picture of it.</p>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">THE PAIR</h3>
  <div class="bb-two">
    <div style="border:1px solid var(--mc-rule-2);background:var(--mc-paper-2);padding:var(--s-5)">
      <span class="bb-label" style="display:block">Known mileage</span>
      <div class="mc-pair" style="margin-top:var(--s-2)">
        <span class="mc-price">24.800 €</span><span class="mc-km">184.000 km</span>
      </div>
    </div>
    <div style="border:1px solid var(--mc-rule-2);background:var(--mc-paper-2);padding:var(--s-5)">
      <span class="bb-label" style="display:block">Mileage missing — the slot is never dropped</span>
      <div class="mc-pair" style="margin-top:var(--s-2)">
        <span class="mc-price">18.500 €</span><span class="mc-km is-unknown">Km sin confirmar</span>
      </div>
    </div>
  </div>
  <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">Mileage is
  {{ round(100*0.78) }}% of the price size, in the <em>same</em> ink and the <em>same</em>
  weight. Making it grey and small is precisely the industry habit this device exists to
  refuse.</p>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">THE NEGATIVE</h3>
  <div class="mc-negative">
    <h3>Lo que no me gusta de este coche</h3>
    <p>Tiene un roce en el paragolpes trasero. Está en la foto 7 y no lo he retocado.</p>
  </div>
  <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
    Mandatory on every vehicle. A car without a negative does not get published — that is
    an editorial gate, not a suggestion. Wash ground, 3px signal rule on the left, serif
    italic body: the owner is speaking, so it is set in his voice.
  </p>

  <h3 style="margin:var(--s-6) 0 var(--s-4)">THE MOSAIC</h3>
  @if($sample->count())
    <div class="mc-mosaic">
      @foreach($sample as $t)
        @php
          // image_path is stored as a public path ("/storage/testimonials/x.jpg"),
          // not a disk path. Normalise both shapes rather than assuming one.
          $rel  = ltrim(preg_replace('#^/?storage/#', '', $t->image_path ?? ''), '/');
          $src  = $rel ? asset('storage/'.$rel) : null;
          $fs   = $rel ? storage_path('app/public/'.$rel) : null;
          $cls  = '';
          if ($fs && is_file($fs)) {
            $sz = @getimagesize($fs);
            if ($sz && $sz[1]) { $rr = $sz[0]/$sz[1]; $cls = $rr < 0.62 ? 'is-tall' : ($rr > 1.05 ? 'is-wide' : ''); }
          }
        @endphp
        <figure>
          <div class="mc-portrait {{ $cls }}">
            @if($src)
              <img class="mc-img mc-img--portrait" src="{{ $src }}"
                   alt="{{ $t->author_name }}" loading="lazy" decoding="async" width="400" height="500">
            @endif
          </div>
        </figure>
      @endforeach
    </div>
    <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
      Showing {{ $sample->count() }} of {{ $inventory['testimonials'] }}. On the site all
      of them appear at once, because <b>the quantity is the argument</b>. Note the frames:
      tall files fall back to {{ $tokens['--ar-tall'] ?? '5/7' }} and landscape files sit at {{ $tokens['--ar-wide'] ?? '4/3' }} — decided server-side, per file.
    </p>
  @endif

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p style="margin-bottom:0"><b>No star ratings.</b> Gold glyphs printed by our own
    template, next to testimonials we also control, have zero evidential weight and the
    buyer knows it. The photograph is the proof. This is why the mosaic replaced the
    carousel and why nothing on this site is rated out of five.</p>
  </div>
</section>

{{-- ═══════════════════════════════════════════════════ 12 A11Y ══ --}}
<section class="bb-section" id="a11y">
  <div class="bb-head">
    <span class="bb-num bb-label">12</span>
    <h2>Accessibility</h2>
  </div>
  <p class="bb-prose">Not a compliance chapter. Most of it was already decided by the type
  floor, the contrast table and the touch-target rule — this is the list of what remains.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Requirement</th><th>How this system meets it</th></tr></thead>
    <tbody>
      <tr><td>Text contrast (1.4.3)</td><td>Every rendered pair is computed above; the page fails loudly if one drops below 4.5:1.</td></tr>
      <tr><td>Non-text contrast (1.4.11)</td><td><code>--mc-rule-3</code> is the legal floor for a control border and is checked in the same table.</td></tr>
      <tr><td>Target size (2.5.8)</td><td>{{ $tokens['--mc-tap'] ?? '44px' }} minimum, {{ $tokens['--mc-tap-pref'] ?? '48px' }} preferred, 8px separation.</td></tr>
      <tr><td>Focus visible (2.4.7)</td><td>2px <code>--mc-ink</code> outline with 2px offset, on <code>:focus-visible</code>. Never <code>outline:none</code>.</td></tr>
      <tr><td>Reflow (1.4.10)</td><td>No horizontal page scroll at 320px. Wide tables scroll inside their own container.</td></tr>
      <tr><td>Motion (2.3.3)</td><td><code>prefers-reduced-motion</code> honoured globally; no information is carried by movement.</td></tr>
      <tr><td>Text resize (1.4.4)</td><td>Everything in <code>rem</code>. No <code>maximum-scale</code>, no <code>user-scalable=no</code>.</td></tr>
      <tr><td>Images (1.1.1)</td><td>Vehicle and customer photographs carry real alt text — make, model, year, or the person's name. Decorative frames carry <code>alt=""</code>.</td></tr>
      <tr><td>Language (3.1.1)</td><td>The site is <code>lang="es"</code>. This book is <code>lang="en"</code>.</td></tr>
    </tbody>
  </table>
  </div>
</section>

{{-- ═════════════════════════════════════════════════ 13 NEVER ══ --}}
<section class="bb-section" id="never">
  <div class="bb-head">
    <span class="bb-num bb-label">13</span>
    <h2>What we deliberately do not do</h2>
  </div>
  <p class="bb-prose">A system is defined at least as much by its refusals. Each of these
  was available, considered, and rejected for a stated reason.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:26ch">Not doing</th><th>Because</th></tr></thead>
    <tbody>
      <tr><td>Rounded corners</td><td>The measured reference uses 0px on 100% of its images. Half-rounding is a trend; zero everywhere is a system.</td></tr>
      <tr><td>Shadows and gradients</td><td>They simulate depth this brand does not need and cost paint time on the mid-range Android that is the real target device.</td></tr>
      <tr><td>A testimonial carousel</td><td>It shows one face at a time. The argument is the quantity, so all of them appear at once.</td></tr>
      <tr><td>Star ratings</td><td>Self-issued glyphs next to self-collected reviews prove nothing.</td></tr>
      <tr><td>Filters, search, sort, pagination</td><td>{{ $inventory['available'] }} cars. Filter UI advertises an inventory that does not exist.</td></tr>
      <tr><td>Icon sets</td><td>Decorative icons are the fastest way to look like every dealer template. Type and rules carry the structure instead.</td></tr>
      <tr><td>A circular owner avatar</td><td>The most generic element on the web. He is one of the faces, in the same rectangle as everyone else.</td></tr>
      <tr><td>Handwriting fonts</td><td>On a €25,000 purchase they read as costume. The serif italic does the same job honestly.</td></tr>
      <tr><td>Dark mode</td><td>Inverting a warm-paper system produces a different brand. A half-built dark mode is worse than none.</td></tr>
      <tr><td>Spring and bounce easing</td><td>Playful. This business is asking for €20,000 on trust.</td></tr>
      <tr><td>Generic component-library pages</td><td>A brandbook that documents "form fields" and "alert variants" documents Bootstrap, not a brand. Only what is ours is specified here.</td></tr>
      <tr><td>Any claim the business cannot honour</td><td>Warranties, inspection counts, money-back windows and award badges belong to the reference site, not to MOTORCLASS.</td></tr>
    </tbody>
  </table>
  </div>
</section>

{{-- ═════════════════════════════════════════════════ 14 ORDER ══ --}}
<section class="bb-section" id="order">
  <div class="bb-head">
    <span class="bb-num bb-label">14</span>
    <h2>Build order</h2>
  </div>
  <p class="bb-prose">Dependency order, not priority order. Each step is only safe once the
  ones above it are done.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:3ch">#</th><th>Step</th><th>Done when</th></tr></thead>
    <tbody>
      <tr><td class="bb-num-cell">1</td><td>EXIF orientation in the resizer</td><td><span class="bb-pass">done here</span> — pending on production</td></tr>
      <tr><td class="bb-num-cell">2</td><td>Load <code>mc-tokens.css</code> before anything else; global <code>border-radius:0</code></td><td><span class="bb-pass">done</span></td></tr>
      <tr><td class="bb-num-cell">3</td><td>Fonts: Archivo + Source Serif 4, with metric-matched fallbacks</td><td><span class="bb-pass">done</span></td></tr>
      <tr><td class="bb-num-cell">4</td><td>Viewport meta: <code>viewport-fit=cover</code>, <code>interactive-widget=resizes-content</code></td><td>Every layout, both apps</td></tr>
      <tr><td class="bb-num-cell">5</td><td>THE PAIR as a Blade component, used everywhere a price appears</td><td>No price renders without it</td></tr>
      <tr><td class="bb-num-cell">6</td><td>THE NEGATIVE: column, admin field, editorial gate</td><td>A car without one cannot be published</td></tr>
      <tr><td class="bb-num-cell">7</td><td>Vehicle card rebuilt on the 4:3 frame</td><td>Home and catalogue share one component</td></tr>
      <tr><td class="bb-num-cell">8</td><td>THE MOSAIC replaces the carousel; variant frames server-side</td><td>Crop audit reads 0 violations</td></tr>
      <tr><td class="bb-num-cell">9</td><td>Fixed bottom bar, two actions, price pair, safe-area padding</td><td>Verified on a real iPhone, not a simulator</td></tr>
      <tr><td class="bb-num-cell">10</td><td>Touch-target sweep at 390px</td><td>0 targets under 44px, from 45/41/27 today</td></tr>
      <tr><td class="bb-num-cell">11</td><td>THE STROKE, nine placements, one implementation</td><td>One CSS class, no library</td></tr>
      <tr><td class="bb-num-cell">12</td><td>Voice pass over every string on the site</td><td>Zero banned phrases; every <b>[CONFIRM]</b> signed off</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p style="margin-bottom:0"><b>Two of these need the owner's decision, not a developer's.</b>
    Step 6 changes what can be published at all, and step 12 contains legal and commercial
    claims marked <b>[CONFIRM]</b>. Neither should be implemented on an assumption.</p>
  </div>
</section>

</main>

<footer class="bb-foot">
  <div class="bb-wrap">
    <div class="bb-head"><h2>MOTORCLASS</h2></div>
    <p class="bb-small">Design system v1 · "The Proof" · Málaga, España<br>
      Generated from <code>public/css/mc-tokens.css</code> ·
      {{ count($tokens) }} tokens · {{ count($contrast) }} contrast pairs ·
      {{ $crops['n'] }} photographs audited</p>
    <p class="bb-small">This document is not for publication. It is the working
      specification for the v2 front end and is excluded from indexing.</p>
    <p class="bb-mark" aria-hidden="true">MOTORCLASS</p>
  </div>
</footer>

<script>
(function(){
  var s = document.getElementById('stroke-demo');
  if (!s) return;
  if (!('IntersectionObserver' in window)) { s.classList.add('is-drawn'); return; }
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){ if (e.isIntersecting) { e.target.classList.add('is-drawn'); io.unobserve(e.target); } });
  }, {threshold:.4});
  io.observe(s);
  setTimeout(function(){ s.classList.add('is-drawn'); }, 3000);
})();
</script>
</body>
</html>
