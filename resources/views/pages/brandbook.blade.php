<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>MOTORCLASS — Brandbook</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<script>document.documentElement.className += ' js';</script>
</head>
<body class="bb">

@php
  $sections = [
    ['01','What we take from car-planet','ref'],
    ['02','The order of the page','order'],
    ['03','Elements — what it will look like','elements'],
    ['04','The customer wall','wall'],
    ['05','Colour','colour'],
    ['06','Type','type'],
    ['07','Mobile','mobile'],
    ['08','Photographs','photos'],
    ['09','Voice','voice'],
    ['10','Sign-off sheet','signoff'],
    ['A','Appendix — the contrast maths','contrast'],
    ['B','Appendix — space, motion, build order','appendix'],
  ];
  $draft = 3;
  $fmt   = fn($n) => number_format($n);
  $euros = fn($n) => number_format($n, 0, ',', '.').' €';
  $km    = fn($n) => number_format($n, 0, ',', '.').' km';   // Spanish, for everything that mimics the site
  $car   = $car ?? null;
  $carImg = $car?->thumbUrl(800);
@endphp

{{-- ══════════════════════════════════════════════ MASTHEAD ══ --}}
<header class="bb-top">
  <div class="bb-wrap">
    <span class="bb-num bb-label">IV MOTORCLASS · Málaga · design system, draft {{ $draft }}</span>
    <h1 class="bb-display">Your website, before it is built</h1>
    <p class="bb-prose" style="margin-top:var(--s-6)">
      This is not an essay about design. It is a picture of every part of your site, at
      real size, in the real code — so you can look at each one and say yes, or say what
      you want changed, before anybody spends a day building it.
    </p>
    <p class="bb-prose">
      Read it on your phone. Most of your customers will. Where something behaves
      differently on a phone, it is shown twice.
    </p>

    <div class="bb-meta">
      <div><span class="bb-label">Cars available</span><b>{{ $inventory['available'] }}</b></div>
      <div><span class="bb-label">Cars delivered</span><b>{{ $inventory['sold'] }}</b></div>
      <div><span class="bb-label">Customers photographed</span><b>{{ $inventory['testimonials'] }}</b></div>
      <div><span class="bb-label">Elements to approve</span><b>7</b></div>
      <div><span class="bb-label">Generated</span><b>{{ now()->format('d M Y H:i') }}</b></div>
    </div>
  </div>
</header>

<main class="bb-wrap">

{{-- ══════════════════════════════════════════ HOW TO READ ══ --}}
<section class="bb-section" style="border-top:0">
  <div class="bb-head">
    <span class="bb-num bb-label">How to read this</span>
    <h2>Three kinds of decision</h2>
  </div>

  <p class="bb-prose">Not everything here is up for discussion, and it should be obvious
  which is which. Every rule in this book carries one of these three marks.</p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:14ch">Mark</th><th>What it means</th><th>What you do</th></tr></thead>
    <tbody>
      <tr>
        <td><span class="bb-st bb-st--fixed">Fixed</span></td>
        <td>Decided by something outside this project — an iPhone rule, an accessibility
            law, a bug that is already live. Changing it breaks the site.</td>
        <td>Nothing. It is background.</td>
      </tr>
      <tr>
        <td><span class="bb-st bb-st--prop">Proposed</span></td>
        <td>My recommendation. There is a workable alternative and I say what it costs.</td>
        <td>Accept, or ask for the alternative.</td>
      </tr>
      <tr>
        <td><span class="bb-st bb-st--you">Your call</span></td>
        <td>A decision about your business, not about design — what you promise, what you
            publish, how you sell. I should not be making these.</td>
        <td>Decide. Nothing gets built until you do.</td>
      </tr>
    </tbody>
  </table>
  </div>

  <ul class="bb-toc" style="margin-top:var(--s-6);list-style:none;padding:0">
    @foreach($sections as [$n,$title,$id])
      <li><a href="#{{ $id }}"><span class="bb-label">{{ $n }}</span><span>{{ $title }}</span></a></li>
    @endforeach
  </ul>
</section>

{{-- ═══════════════════════════════════════════ 1 REFERENCE ══ --}}
<section class="bb-section" id="ref">
  <div class="bb-head">
    <span class="bb-num bb-label">01</span>
    <h2>What we take from car-planet, and what we don't</h2>
  </div>

  <p class="bb-prose">
    You said you want a site like <b>car-planet.co.uk</b> — the same structure, the same
    consistency, the same feeling that these people are serious. So we measured it rather
    than looked at it. Here is what their site is actually made of, and what we take.
  </p>

  <div class="bb-note">
    <p><b>The finding that shapes everything else.</b> On car-planet, blue is barely a
    background at all. We counted the references in their own stylesheet:
    <b>text in blue appears 46 times; a blue-filled area appears twice.</b> The only large
    blue surfaces on the whole site are the footer and one card. Everything that
    <em>feels</em> blue is a pale wash — 71 of them.</p>
    <p style="margin-bottom:0">So what you liked is not a lot of blue. It is blue used with
    discipline. That is what we copy.</p>
  </div>

  <div class="bb-scroll" style="margin-top:var(--s-5)">
  <table class="bb-t">
    <thead><tr><th style="width:22ch">What they do</th><th>Do we?</th><th>Why</th></tr></thead>
    <tbody>
      <tr><td>Blue as ink and edges, not as fields</td><td><b>Yes</b> <span class="bb-st bb-st--fixed">Fixed</span></td><td>Measured on their site: 46 text uses to 2 fills. It is also what lets your photographs stay the loudest thing on the page.</td></tr>
      <tr><td>Navy footer</td><td><b>Yes</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>It signals where the page ends. Ours is slightly lighter than theirs on purpose — see the colour section.</td></tr>
      <tr><td>Their typeface</td><td><b>Yes</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>DM Sans, which they render on 485 of 509 measured text nodes. One family, no serif. It is a free Google font, so nothing is copied that is theirs to own.</td></tr>
      <tr><td>Soft corners, measured</td><td><b>Yes</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>Their filled buttons are 10px in 100% of cases, cards 22px in 26 of 26, chips 4px. Only the photographs and bare text links are square. An earlier draft took "square" to every element, and it read as old-school — so this now follows their numbers exactly: 4 / 10 / 22.</td></tr>
      <tr><td>The price in a pale box on the card</td><td><b>Yes</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>Their card sets the price at 24px bold inside a tinted box. Same device, with the kilometres beside it.</td></tr>
      <tr><td>Pale blue, everywhere it counts</td><td><b>Yes</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>They paint pale blue 71 times against 48 saturated fills. Bands, spec rows and stages here lean blue, not grey.</td></tr>
      <tr><td>Clean header, grid of cars, long detail page, fixed bar on mobile</td><td><b>Yes</b> <span class="bb-st bb-st--fixed">Fixed</span></td><td>This is the structure you asked for, and it is the right one.</td></tr>
      <tr><td>A coral price</td><td><b>Yes, corrected</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>Their coral fails legibility at 3.59:1 as 16px text. Ours keeps their hue at a lightness that passes — {{ number_format(collect($contrast)->firstWhere('use','Price on a card')['ratio'] ?? 0, 2) }}:1 on white — and uses it only on the price, which is always large.</td></tr>
      <tr><td>Filters, search and sort</td><td><b>Not yet</b> <span class="bb-st bb-st--you">Your call</span></td><td>They have thousands of cars. You have {{ $inventory['available'] }}. A filter panel over {{ $inventory['available'] }} cars advertises stock that isn't there. If you plan to hold 25+, we build it — say so and it goes in.</td></tr>
      <tr><td>A gradient in the header</td><td><b>No</b> <span class="bb-st bb-st--prop">Proposed</span></td><td>Gradients band visibly on mid-range Android screens, which is most of your traffic. A banded header on a €20,000 purchase looks cheap.</td></tr>
      <tr><td>Their ink is 1.08:1 from pure black</td><td><b>No</b> <span class="bb-st bb-st--fixed">Fixed</span></td><td>Their "navy" is black in practice, and in Málaga sun on a cheap screen it reads as black. Ours holds 1.55:1 so it stays navy.</td></tr>
      <tr><td>Money-back windows, inspection-point counts, warranty programmes, award badges</td><td><b>No</b> <span class="bb-st bb-st--fixed">Fixed</span></td><td>You do not offer these. Publishing them would be false advertising. Copying their structure is fine; copying their promises is not.</td></tr>
    </tbody>
  </table>
  </div>
</section>


{{-- ═══════════════════════════════════════════════ 2 ORDER ══ --}}
<section class="bb-section" id="order">
  <div class="bb-head">
    <span class="bb-num bb-label">02</span>
    <h2>The order of the page</h2>
  </div>

  <p class="bb-prose">
    Which section comes first is not a matter of taste, and it is not something I should
    decide on my own. Below is the order the published research supports, with the source
    for each position. Where the research does not settle it, it says so and the decision
    comes to you.
  </p>

  <div class="bb-dont">
    <h3>What your site does today, measured on your live pages</h3>
    <p>Measured 2 September 2026 with a real browser at 390&nbsp;×&nbsp;785px — a normal
      phone with the browser bar collapsed.</p>
    <div class="bb-scroll">
    <table class="bb-t" style="min-width:0">
      <tbody>
        <tr><td>The hero occupies</td><td><b>701px of 785</b> — 89% of the screen</td></tr>
        <tr><td>First photo of a real car</td><td><b>3.1 screens down</b></td></tr>
        <tr><td>Customer testimonials</td><td><b>6.2 screens down</b></td></tr>
        <tr><td>Whole page</td><td>9,048px — about 11.5 screens</td></tr>
      </tbody>
    </table>
    </div>
    <p style="margin-bottom:0">Nothing but the hero is visible on the first screen. And what
      is on it is a <b>stock photograph of a car that is not yours</b>, the sentence
      <em>Tu coche perfecto te espera</em>, a search box for nine cars, and a badge
      reading <em>Garantía</em>. Eye-tracking puts <b>57% of viewing time above the fold
      and 74% within the first two screens</b> — so your strongest asset, 25 photographed
      customers, sits almost entirely outside where people look.</p>
  </div>

  <h3>Home page — the recommended order</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:3ch">#</th><th style="width:22ch">Section</th><th>Why it sits here</th><th style="width:20ch">Evidence</th></tr></thead>
    <tbody>
      <tr><td class="bb-num-cell">1</td><td><b>Header: name, Málaga, phone, WhatsApp</b></td>
        <td>Contact details are the highest-scoring credibility signal measured for an unknown seller. A physical address scored 1.86 and a phone number 1.71 on a −3…+3 scale — against 0.69 for photos of your own people.</td>
        <td>Fogg et al., CHI 2001, n=1,410 <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">2</td><td><b>A short hero</b></td>
        <td>Short enough that the first row of cars breaks the fold. A full-screen hero creates a "false floor" — people believe they have seen the page and leave.</td>
        <td>NN/g eye-tracking, 120 participants, 130,000+ fixations <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">3</td><td><b>The nine cars</b></td>
        <td>Finding actual vehicles for sale is the second most common thing car buyers do online, at 68%. For a nine-car dealer the cars <em>are</em> the offer. All nine load at once — pagination on this many is pure friction.</td>
        <td>Autotrader/Cox 2016, n=2,131 <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">4</td><td><b>The customer wall</b></td>
        <td>After the cars, because it corroborates them rather than replacing them. Still inside the first two screens, where 74% of viewing time goes.</td>
        <td>Spiegel, Northwestern 2017 <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">5</td><td><b>Who you are</b></td>
        <td>Name, face, where you are, how long you have been doing this. Below the wall because the testimonials give the biography its evidence.</td>
        <td>Stanford credibility guidelines 2 &amp; 4 <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">6</td><td><b>Cars delivered</b></td>
        <td>Volume evidence. Genuinely optional here — it does the same job as the wall.</td>
        <td><span class="bb-st bb-st--you">Your call</span></td></tr>
      <tr><td class="bb-num-cell">7</td><td><b>Contact</b></td>
        <td>The ask comes after the proof: do not make a demand before the trust needs below it are met. This is the second contact point — the header serves people who arrive already convinced.</td>
        <td>NN/g Hierarchy of Trust <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">8</td><td><b>Footer</b></td>
        <td>Used heavily as fallback navigation. Address, hours, legal identity.</td>
        <td>NN/g, 70+ users, 100 sites <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-dont" style="margin-top:var(--s-5)">
    <h3>Cut the auto-rotating carousel <span class="bb-st bb-st--prop">Proposed</span></h3>
    <ul>
      <li>Across 28,928 recorded clicks, roughly <b>1% of visitors clicked a carousel at
        all</b> — and 84% of those clicks landed on the first slide.</li>
      <li>Anything on slide 2 or later is, in practice, not published.</li>
    </ul>
  </div>

  <h3>Vehicle page — where the money decision happens</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:3ch">#</th><th style="width:22ch">Section</th><th>Why it sits here</th><th style="width:20ch">Evidence</th></tr></thead>
    <tbody>
      <tr><td class="bb-num-cell">0</td><td><b>Fixed bar: WhatsApp + Call</b></td>
        <td>Present from the first paint. 49% of phone use is one-handed and thumbs drive 75% of interactions; the bottom strip is the only reliably reachable zone. <b>Your site has no such bar today.</b></td>
        <td>Hoober, 1,300+ people observed <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">1</td><td><b>Title — make, model, year</b></td>
        <td>The "am I on the right car?" check. People scan headings first and the first two words carry the meaning.</td>
        <td>NN/g F-pattern research <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">2</td><td><b>Price and kilometres</b></td>
        <td>Researching price is the single most common thing car buyers do online — 71%, ahead of finding the cars themselves. And in Spain specifically, <b>41% of used-car buyers name mileage as the decisive factor.</b> With stock at 108,000–184,000 km, stating it plainly reads as confidence; burying it reads as concealment.</td>
        <td>Autotrader/Cox 2016 · GANVAM + DGT 2020, 700+ Spanish buyers <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">3</td><td><b>Photo gallery</b></td>
        <td>Viewing car images is the top mobile action at 44%. But on a product page only 18% of viewing time goes to photos against 82% to text — photos earn attention, text carries the decision. So: facts first, then photos. <b>Put the odometer in the first three photos</b> — used-car buyers rank it the single most important image type, and almost nobody does it.</td>
        <td>Google/Millward Brown 2013 · Cox, 521 consumers <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">4</td><td><b>Key specs — six to eight</b></td>
        <td>Short, headed chunks are the most effective way people scan.</td>
        <td>NN/g layer-cake research <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">5</td><td><b>Contact, inline</b></td>
        <td>The first natural decision point. Your enquiry form currently sits at line 937 of 1,485 — below the description, the spec table, the equipment list and the tags.</td>
        <td>Özpolat &amp; Jank 2015 <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">6</td><td><b>Your own description</b></td>
        <td>Where a one-man dealer's voice does work no spec table can.</td>
        <td>Baymard product-page benchmark <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">7</td><td><b>Two or three testimonials</b></td>
        <td>Next to the decision. A randomised field experiment across 250,000+ real transactions at 493 retailers found trust signals work best for small sellers and expensive baskets — but in the later stages, not the early ones. <b>Your vehicle page has no social proof at all</b> — the largest single omission on the page where the money decision is made.</td>
        <td>Özpolat &amp; Jank, DSS 73 (2015) <span class="bb-st bb-st--fixed">Fixed</span></td></tr>
      <tr><td class="bb-num-cell">8</td><td><b>Full spec table, folded</b></td>
        <td>Collapse only the long tail, never the price strip. Reading comprehension on a phone measures about half the desktop level, so length has a real cost.</td>
        <td>NN/g, n=50 <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">9</td><td><b>Who you are + full contact</b></td>
        <td>The form goes here, not higher — people do not want a form replacing a phone number.</td>
        <td>NN/g contact-page research <span class="bb-st bb-st--prop">Proposed</span></td></tr>
      <tr><td class="bb-num-cell">10</td><td><b>Other cars</b></td>
        <td>After the ask. Placed above it, it invites the buyer to leave the car they were about to enquire about.</td>
        <td>Baymard cross-sell research <span class="bb-st bb-st--prop">Proposed</span></td></tr>
    </tbody>
  </table>
  </div>

  <h3>Where the testimonials go — and an honest correction</h3>
  <p class="bb-prose">
    I told you earlier that your 25 photographs are your strongest asset because a
    photograph cannot be faked the way a written review can. <b>The research does not
    support that in the form I put it.</b> It is worth telling you before you repeat it to
    anyone.
  </p>

  <div class="bb-two">
    <div class="bb-dont">
      <h3>What the evidence actually says about photographs</h3>
      <ul>
        <li>The most rigorous study in the set — <b>115 people, real money at stake, 12 real
          shops</b> — found photographs of people had <b>no effect on trust</b>
          (p&nbsp;=&nbsp;.99), and <em>reduced</em> people's ability to tell good sellers
          from bad ones.</li>
        <li>A famous finding that trustworthy-looking faces earn more on Airbnb
          <b>failed a pre-registered replication with 1,020 participants.</b></li>
        <li>In a study of 2,440 comments about what makes a website credible,
          <b>testimonials never came up as a category at all.</b></li>
        <li>People trust reviews on external sites <em>more</em> than the same reviews on
          the company's own site.</li>
      </ul>
    </div>
    <div class="bb-do">
      <h3>What the evidence does support — strongly</h3>
      <ul>
        <li><b>A face captures the eye within about a third of a second.</b> In controlled
          eye-tracking, a face was looked at in <b>92.6% of trials</b>, and on the very
          first fixation 61% of the time. Faces pull attention <b>16.6× more</b> than
          matched controls, and keep doing it even when people are told to look away.</li>
        <li>So the photograph <b>buys the glance that gets the comment read.</b> Its value
          is as an entry point and as verifiable identity — not as a trust mechanism on
          its own.</li>
        <li>Reviews raise conversion <b>+380% on expensive products against +190% on cheap
          ones.</b> Your case for weighting social proof rests on the price of a car, not
          on the photographs.</li>
        <li>People read <b>the first few</b> testimonials, not all of them. Which ones
          surface first is the real design lever — not how many there are.</li>
      </ul>
    </div>
  </div>

  <p class="bb-prose" style="margin-top:var(--s-5)">
    So the placement splits into three, rather than being one block in one place:
  </p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:22ch">Where</th><th>What</th><th>Why</th></tr></thead>
    <tbody>
      <tr><td><b>High, in or under the hero</b></td><td>One line: <em>{{ $inventory['sold'] }} coches entregados · {{ $inventory['testimonials'] }} reseñas con foto · Málaga</em></td><td>Costs almost no vertical space, and sits where attention actually is. Keep it to one line — more than two trust signals measurably <em>lowers</em> completion.</td></tr>
      <tr><td><b>Mid-page, home</b></td><td>The full wall of {{ $inventory['testimonials'] }}</td><td>High enough to fall inside the first two screens; low enough to corroborate the cars instead of preceding them.</td></tr>
      <tr><td><b>Vehicle page, next to the contact block</b></td><td>Two or three</td><td>The placement the field-experiment data actually supports for an expensive purchase from a small seller. <b>Highest-value single change in this section.</b></td></tr>
      <tr><td><b>A link out to Google</b></td><td>If you have a profile</td><td>External reviews are trusted more than on-site ones. Cheap to add, and it does work the wall cannot.</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p><b>One number worth knowing about the market you are in.</b> Buyers of used cars
    from independent dealers report 56% satisfaction, against 65% from franchised dealers.
    You start from a measurable trust deficit that a main dealer does not have — which is
    the real argument for putting proof early and often, and for not claiming anything you
    cannot show.</p>
    <p style="margin-bottom:0">There is also an encouraging finding: in the Stanford study,
    the third most credible site out of 100 tested was a company almost nobody had heard
    of. It won on craft, clarity and contactability, not on brand. <b>Design quality was
    cited in 46.1% of all credibility comments — nearly double the next factor.</b> That
    is the one lever available to a nine-car dealer, and it is why this document exists.</p>
  </div>

  <h3>Where the research does not decide</h3>
  <p class="bb-prose">These are judgement calls, and I am flagging them as mine rather than
  presenting them as findings.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Question</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td>Testimonials high on the page or low</td><td><b>No study varies it.</b> Both positions are inferred from adjacent evidence. The three-way split above is a reconciliation, not a finding.</td></tr>
      <tr><td>Photographs attached to testimonials versus text alone</td><td><b>No study found.</b> Any percentage lift claimed for testimonial headshots is unsupported.</td></tr>
      <tr><td>Gallery before or after the price strip</td><td>Genuinely close. Price ranks #1 in buyer surveys; images rank #1 in mobile actions. I recommend facts first, but gallery-first is defensible.</td></tr>
      <tr><td>Your biography before or after the wall</td><td>No evidence either way.</td></tr>
      <tr><td>How tall a hero may be</td><td>The research says the fold matters; it gives no threshold. "Short enough that the first car row peeks" is a rule of thumb.</td></tr>
      <tr><td>WhatsApp as a channel</td><td>Every figure found was marketing from companies selling WhatsApp integrations. This rests on your knowledge of your market, not on research.</td></tr>
      <tr><td>Spain</td><td>Almost no public Spanish buyer-journey research exists. The mileage figure is the one solid Spain-specific input; everything else is American or Canadian and cultural transfer is assumed, not demonstrated.</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <h3>What did not survive checking</h3>
    <p>A widely-shared page of 29 conversion statistics was checked: <b>only three named a
    study. The other 26 linked to other marketing blogs</b> with no method, no sample and
    no date. Several famous numbers turned out to have no traceable source at all —
    including the one most often used to argue that testimonials belong at the top of a
    page, and a conversion figure attributed to a research institute that does not publish
    it.</p>
    <p style="margin-bottom:0">None of them are in this book. In a document you will show to
    people, an invented citation is worse than no citation — and every claim above can be
    checked.</p>
  </div>
</section>

{{-- ════════════════════════════════════════════ 3 ELEMENTS ══ --}}
<section class="bb-section" id="elements">
  <div class="bb-head">
    <span class="bb-num bb-label">03</span>
    <h2>Elements — what it will look like</h2>
  </div>

  @php
    $cardFor = function ($v, $extra = '') use ($euros, $km) {
      $img = $v?->thumbUrl(800);
      ob_start(); ?>
      <article class="mc-card <?= $extra ?>">
        <a class="mc-card__link" href="#elements">
          <div class="mc-frame mc-frame--card">
            <?php if ($img): ?><img class="mc-img mc-img--vehicle" src="<?= $img ?>"
              alt="<?= e($v->brand.' '.$v->model.' '.$v->year) ?>" width="800" height="600" loading="lazy" decoding="async"><?php endif; ?>
          </div>
          <div class="mc-card__body">
            <h3 class="mc-card__title"><?= e($v?->brand.' '.$v?->model) ?></h3>
            <div>
              <div class="mc-pair">
                <span class="mc-price"><?= $v ? $euros($v->price) : '—' ?></span>
                <?php if ($v?->mileage): ?><span class="mc-km"><?= $km($v->mileage) ?></span>
                <?php else: ?><span class="mc-km is-unknown">Km sin confirmar</span><?php endif; ?>
              </div>
            </div>
            <ul class="mc-chips">
              <li class="mc-chip"><?= e($v?->year) ?></li>
              <li class="mc-chip"><?= e($v?->fuel_es) ?></li>
              <li class="mc-chip"><?= e($v?->transmission_es) ?></li>
            </ul>
            <span class="mc-card__go">Ver el coche <span class="mc-card__arrow">&rarr;</span></span>
          </div>
        </a>
      </article>
      <?php return ob_get_clean();
    };
    $rowFor = function ($t) {
      $rel = ltrim(preg_replace('#^/?storage/#', '', $t->image_path ?? ''), '/');
      $src = $rel ? asset('storage/'.$rel) : null;
      $fs  = $rel ? storage_path('app/public/'.$rel) : null;
      $cls = '';
      if ($fs && is_file($fs)) { $sz = @getimagesize($fs);
        if ($sz && $sz[1]) { $rr = $sz[0]/$sz[1]; $cls = $rr < 0.62 ? 'is-tall' : ($rr > 1.05 ? 'is-wide' : ''); } }
      $q = trim((string) $t->quote);
      ob_start(); ?>
      <article class="tt-row">
        <div class="tt-photo <?= $cls ?>">
          <?php if ($src): ?><img class="mc-img mc-img--portrait" src="<?= $src ?>" alt="<?= e($t->author_name) ?>" loading="lazy" decoding="async" width="400" height="500"><?php endif; ?>
        </div>
        <div class="tt-body">
          <?php if (mb_strlen($q) > 2): ?><p class="tt-quote is-clamped"><?= e($q) ?></p><?php endif; ?>
          <span class="tt-attr"><b><?= e(str_replace('&', ' y ', $t->author_name)) ?></b><?= $t->author_location ? ' · '.e($t->author_location) : '' ?></span>
        </div>
      </article>
      <?php return ob_get_clean();
    };
    $goodQuotes = $sample->filter(fn($t) => mb_strlen(trim((string)$t->quote)) > 40)->values();
  @endphp

  <p class="bb-prose">
    Two screens, assembled from the real pieces in the order §2 recommends, at the size of
    a normal phone. The dashed line is the bottom of the first screen — on your current
    site nothing but a stock photograph sits above it.
  </p>

  {{-- ══ THE TWO ASSEMBLED PHONES ══ --}}
  <div class="bb-phones" style="margin-top:var(--s-6)">

    {{-- HOME --}}
    <div>
      <span class="bb-label">Home · 390px · the first two screens</span>
      <div class="ph ph--clip">
        <div class="ph__status"><span>9:41</span><i></i></div>
        <div class="ph__screen">
          <div class="ph__fold"><span>first screen ends here</span></div>

          <div class="mc-head">
            <div class="mc-head__in">
              <a class="mc-logo" href="#elements">IV&nbsp;MOTORCLASS</a>
              <span class="mc-btn mc-btn--cta" style="padding-inline:var(--s-3)">WhatsApp</span>
              <span class="mc-burger"><span class="mc-burger__bars"></span></span>
            </div>
          </div>

          <div class="pg-sec">
            <span class="pg-eyebrow">Málaga</span>
            <p class="pg-h1">{{ ['','Un','Dos','Tres','Cuatro','Cinco','Seis','Siete','Ocho','Nueve','Diez'][$inventory['available']] ?? $inventory['available'] }} coches. Los he conducido todos yo.</p>
            <p class="pg-lead">Los kilómetros están a la vista, y también lo que no me gusta de cada uno.</p>
            <div class="pg-proof">
              <span><b>{{ $inventory['sold'] }}</b> coches entregados</span>
              <span>·</span>
              <span><b>{{ $inventory['testimonials'] }}</b> reseñas con foto</span>
            </div>
            <span class="mc-btn mc-btn--outline mc-btn--block">Ver los coches</span>
          </div>

          <div class="pg-sec">
            <h2 class="pg-h">Disponibles hoy</h2>
            <p class="pg-sub">Los he comprado y conducido yo.</p>
            <div class="mc-cardgrid">
              @foreach($cars->take(2) as $v){!! $cardFor($v) !!}@endforeach
            </div>
          </div>

          <div class="pg-sec pg-sec--band">
            <h2 class="pg-h">{{ $inventory['testimonials'] }} personas se hicieron la foto</h2>
            <p class="pg-sub">No pedí ninguna. Están todas.</p>
            <div class="tt-list">
              @foreach($goodQuotes->take(2) as $t){!! $rowFor($t) !!}@endforeach
            </div>
          </div>

          <div class="pg-sec">
            <h2 class="pg-h">Quién soy</h2>
            <div class="pg-prose"><p>Compro los coches yo, los conduzco yo, y te contesto yo.</p></div>
          </div>
        </div>
        <div class="ph__home"></div>
      </div>
      <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
        Continues: who you are · cars delivered · contact · footer. The first car is visible
        <b>without scrolling</b>; today it is 3.1 screens down.</p>
    </div>

    {{-- VEHICLE --}}
    <div>
      <span class="bb-label">Vehicle page · 390px · the first two screens</span>
      <div class="ph ph--clip">
        <div class="ph__status"><span>9:41</span><i></i></div>
        <div class="ph__screen">
          <div class="ph__fold"><span>first screen ends here</span></div>

          <div class="mc-head">
            <div class="mc-head__in">
              <a class="mc-logo" href="#elements">IV&nbsp;MOTORCLASS</a>
              <span class="mc-burger"><span class="mc-burger__bars"></span></span>
            </div>
          </div>

          <div class="pg-sec" style="padding-top:var(--s-3)">
            <a class="mc-go" href="#elements" style="border:0;color:var(--mc-ink-2);font-size:var(--t-small)"><span class="mc-go__arrow">&larr;</span> Todos los coches</a>
            <p class="pg-h1" style="margin-top:var(--s-2)">{{ $car?->brand }} {{ $car?->model }} {{ $car?->year }}</p>
            <div class="mc-pair">
              <span class="mc-price">{{ $car ? $euros($car->price) : '—' }}</span>
              <span class="mc-km">{{ $car?->mileage ? $km($car->mileage) : '—' }}</span>
            </div>
            <p class="pg-note">Precio para particular. Transferencia no incluida.</p>

            <div class="mc-frame mc-frame--card" style="margin-top:var(--s-4)">
              @if($carImg)<img class="mc-img mc-img--vehicle" src="{{ $carImg }}" alt="" width="800" height="600" loading="lazy">@endif
            </div>
            <div class="pg-gal">
              <div class="is-on">@if($carImg)<img class="mc-img mc-img--vehicle" src="{{ $carImg }}" alt="" width="200" height="150" loading="lazy">@endif</div>
              <div>@if($carOdo ?? null)<img class="mc-img mc-img--vehicle" src="{{ $carOdo }}" alt="" width="200" height="150" loading="lazy">@endif<span class="pg-gal__tag">Odómetro</span></div>
              <div></div><div></div>
            </div>
          </div>

          <div class="pg-sec">
            <h2 class="pg-h">Los datos</h2>
            <dl class="mc-specs">
              <div class="mc-specs__row"><dt>Año</dt><dd>{{ $car?->year }}</dd></div>
              <div class="mc-specs__row"><dt>Kilómetros</dt><dd>{{ $car?->mileage ? $km($car->mileage) : '—' }}</dd></div>
              <div class="mc-specs__row"><dt>Combustible</dt><dd>{{ $car?->fuel_es }}</dd></div>
              <div class="mc-specs__row"><dt>Cambio</dt><dd>{{ $car?->transmission_es }}</dd></div>
              <div class="mc-specs__row"><dt>Dónde está</dt><dd>Málaga</dd></div>
            </dl>
          </div>

          <div class="pg-sec pg-sec--band">
            <p class="pg-note" style="margin:0">Contesto yo. Suelo tardar unas horas, no unos minutos. Los dos botones están abajo.</p>
          </div>

          <div class="pg-sec">
            <h2 class="pg-h">Lo que te digo yo</h2>
            <div class="pg-prose">
              <p>Lo compré en Málaga a su primer dueño. Tiene las revisiones al día y lo he conducido dos semanas antes de ponerlo a la venta.</p>
            </div>
          </div>

          <div class="pg-sec">
            <h2 class="pg-h">Quien me compró uno parecido</h2>
            <div class="tt-list">
              @foreach($goodQuotes->slice(2,1) as $t){!! $rowFor($t) !!}@endforeach
            </div>
          </div>
        </div>
        <div class="ph__bar">
          <div class="mc-bar">
            <div class="mc-bar__pair">
              <span class="mc-bar__price">{{ $car ? $euros($car->price) : '—' }}</span>
              <span class="mc-bar__km">{{ $car?->mileage ? $km($car->mileage) : '' }}</span>
            </div>
            <div class="mc-bar__act">
              <span class="mc-btn mc-btn--cta">WhatsApp</span>
              <span class="mc-btn mc-btn--outline" style="padding-inline:var(--s-3)">Tel</span>
            </div>
          </div>
        </div>
        <div class="ph__home"></div>
      </div>
      <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
        Price and kilometres before the gallery; the odometer slot in the first three; the
        bar with WhatsApp and Call pinned from the first paint. Today this page has neither
        the bar nor a single testimonial.</p>
    </div>
  </div>

  <p class="bb-prose" style="margin-top:var(--s-8)">
    Now the pieces those screens are made of. Each one shows where it appears, every state
    side by side, and has a box to tick. States are frozen copies rather than hover, because
    you are probably reading this on a phone — and because a document you can print is a
    document you can sign.
  </p>

  {{-- ══ EL-CARD-01 — in context ══ --}}
  <article class="bb-el" id="EL-CARD-01" style="margin-top:var(--s-6)">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-CARD-01</span>
      <span class="bb-el__name">Tarjeta de coche</span>
      <div class="bb-el__where"><span>Home</span><span>Catálogo</span><span>404</span></div>
    </div>
    <div class="bb-el__stage">
      <span class="bb-stagecap">As it appears in the catalogue — three real cars under the section heading</span>
      <h2 class="pg-h" style="max-width:none">Disponibles hoy</h2>
      <div class="mc-cardgrid">
        @foreach($cars->take(3) as $v){!! $cardFor($v) !!}@endforeach
      </div>
    </div>
    <div class="bb-el__states">
      <div class="bb-state">{!! $cardFor($car, 'is-hover') !!}<span class="bb-cap">Hover — the shadow lifts</span></div>
      <div class="bb-state">{!! $cardFor($car, 'is-focus') !!}<span class="bb-cap">Keyboard — the ring</span></div>
      <div class="bb-state">{!! $cardFor($sold ?? $car, 'mc-card--sold') !!}<span class="bb-cap">Sold — a variant, not an error</span></div>
    </div>
    <p class="bb-el__why">The whole card is <b>one link</b>. A card with a title link plus a
      button plus a photo link is three targets stacked in 300px, and it is why dealer grids
      are unusable one-handed. The photo never zooms on hover — movement on a photograph
      of a real car is exactly the stock-photo effect we are avoiding.</p>
    <div class="bb-sign"><span class="bb-label">EL-CARD-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-BTN-01 ══ --}}
  <article class="bb-el" id="EL-BTN-01">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-BTN-01</span>
      <span class="bb-el__name">Botones</span>
      <div class="bb-el__where"><span>Todo el sitio</span></div>
    </div>
    <div class="bb-el__stage">
      <span class="bb-stagecap">The whole family, at rest. One blue, one WhatsApp green, one outlined, one quiet.</span>
      <div style="display:flex;flex-wrap:wrap;gap:var(--s-3);align-items:center">
        <span class="mc-btn">Ver los coches</span>
        <span class="mc-btn mc-btn--cta">WhatsApp</span>
        <span class="mc-btn mc-btn--outline">Llamar</span>
        <span class="mc-btn mc-btn--quiet">Ver más fotos</span>
      </div>
    </div>
    <div class="bb-el__states">
      <div class="bb-state"><span class="mc-btn">Ver los coches</span><span class="bb-cap">Rest</span></div>
      <div class="bb-state"><span class="mc-btn is-hover">Ver los coches</span><span class="bb-cap">Hover</span></div>
      <div class="bb-state"><span class="mc-btn is-focus">Ver los coches</span><span class="bb-cap">Keyboard — two rings</span></div>
      <div class="bb-state"><span class="mc-btn is-active">Ver los coches</span><span class="bb-cap">Pressed</span></div>
      <div class="bb-state"><span class="mc-btn is-disabled">Ver los coches</span><span class="bb-cap">Disabled</span></div>
      <div class="bb-state"><span class="mc-btn is-loading"><span class="mc-btn__label" data-busy="Enviando…">Ver los coches</span><span class="mc-btn__prog"></span></span><span class="bb-cap">Sending — the line draws</span></div>
    </div>
    <div class="bb-el__stage bb-el__stage--navy on-navy">
      <span class="bb-stagecap" style="color:var(--mc-on-navy-2)">In the footer the same two flip to white — a green fill on navy has no contrast at its edge, and no recolouring fixes that</span>
      <div style="display:flex;flex-wrap:wrap;gap:var(--s-3)">
        <span class="mc-btn mc-btn--cta">WhatsApp</span>
        <span class="mc-btn mc-btn--outline">Llamar</span>
      </div>
    </div>
    <p class="bb-el__why">Blue appears <b>once per screen</b> — if there are two, neither is
      the main action. WhatsApp is <b>green with its own mark</b> everywhere, and Call is
      outlined in blue everywhere — the distribution car-planet uses. It is not the bright
      brand green: white text on <code>#25D366</code> measures 1.98:1 and fails everywhere,
      including on their own site. This is WhatsApp's hue at the lightness that clears
      4.5:1, so it is recognisably WhatsApp and still legible in sun.
      <span class="bb-st bb-st--prop">Proposed</span></p>
    <div class="bb-sign"><span class="bb-label">EL-BTN-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-HDR-01 ══ --}}
  <article class="bb-el" id="EL-HDR-01">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-HDR-01</span>
      <span class="bb-el__name">Cabecera</span>
      <div class="bb-el__where"><span>Todo el sitio</span></div>
    </div>
    <div class="bb-el__stage bb-el__stage--flush">
      <div class="mc-head">
        <div class="mc-head__in" style="padding:0 var(--s-5)">
          <a class="mc-logo" href="#EL-HDR-01">IV&nbsp;MOTORCLASS</a>
          <nav class="mc-nav" data-desk>
            <a class="mc-nav__i is-current" href="#EL-HDR-01">Coches</a>
            <a class="mc-nav__i" href="#EL-HDR-01">Entregados</a>
            <a class="mc-nav__i" href="#EL-HDR-01">Quién soy</a>
            <a class="mc-nav__i" href="#EL-HDR-01">Contacto</a>
          </nav>
          <a class="mc-btn mc-btn--cta" href="#EL-HDR-01">WhatsApp</a>
        </div>
      </div>
      <div style="height:64px;background:var(--mc-bg)"></div>
    </div>
    <div class="bb-el__stage bb-el__stage--band">
      <span class="bb-stagecap">At 390px the menu folds. WhatsApp stays outside it, always visible.</span>
      <div class="bb-el__stage--twin">
        <div class="ph">
          <div class="ph__status"><span>9:41</span><i></i></div>
          <div class="ph__screen">
            <div class="mc-head"><div class="mc-head__in">
              <a class="mc-logo" href="#EL-HDR-01">IV&nbsp;MOTORCLASS</a>
              <span class="mc-btn mc-btn--cta" style="padding-inline:var(--s-3)">WhatsApp</span>
              <span class="mc-burger"><span class="mc-burger__bars"></span></span>
            </div></div>
            <div style="height:120px"></div>
          </div>
        </div>
        <div class="ph">
          <div class="ph__status"><span>9:41</span><i></i></div>
          <div class="ph__screen">
            <div class="mc-head"><div class="mc-head__in">
              <a class="mc-logo" href="#EL-HDR-01">IV&nbsp;MOTORCLASS</a>
              <span class="mc-btn mc-btn--cta" style="padding-inline:var(--s-3)">WhatsApp</span>
              <span class="mc-burger" style="background:var(--mc-band);border-color:var(--mc-ink)"><span class="mc-burger__bars"></span></span>
            </div></div>
            <div style="padding:0 var(--s-4) var(--s-4);background:var(--mc-surface)">
              @foreach([['Coches',true],['Entregados',false],['Quién soy',false],['Contacto',false]] as [$l,$cur])
                <a class="mc-nav__i" href="#EL-HDR-01" style="display:flex;min-height:var(--mc-tap-pref);padding-block:var(--s-1);border-bottom:1px solid var(--mc-hairline);font-size:var(--t-h3);text-decoration:none{{ $cur ? ';border-left:3px solid var(--mc-blue);padding-left:var(--s-3);font-weight:600' : '' }}">{{ $l }}</a>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="bb-el__why"><b>WhatsApp never goes inside the menu.</b> It is how most of your
      customers will contact you, so it stays visible at every width. The menu holds the
      four navigation links and nothing that converts. Open, it pushes down under the
      header rather than sliding in from the side — four links do not justify a drawer, and
      every line of drawer machinery is a line that can break on a mid-range Android.</p>
    <div class="bb-sign"><span class="bb-label">EL-HDR-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-BAR-01 ══ --}}
  <article class="bb-el" id="EL-BAR-01">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-BAR-01</span>
      <span class="bb-el__name">Barra fija inferior</span>
      <div class="bb-el__where"><span>Ficha de coche</span><span>Solo móvil</span></div>
    </div>
    <div class="bb-el__stage bb-el__stage--band">
      <div class="bb-el__stage--twin">
        <div class="ph">
          <div class="ph__status"><span>9:41</span><i></i></div>
          <div class="ph__screen" style="height:300px;overflow:hidden">
            <div class="pg-sec" style="opacity:.45">
              <h2 class="pg-h">Los datos</h2>
              <dl class="mc-specs">
                <div class="mc-specs__row"><dt>Año</dt><dd>{{ $car?->year }}</dd></div>
                <div class="mc-specs__row"><dt>Combustible</dt><dd>{{ $car?->fuel_es }}</dd></div>
                <div class="mc-specs__row"><dt>Cambio</dt><dd>{{ $car?->transmission_es }}</dd></div>
                <div class="mc-specs__row"><dt>Color</dt><dd>{{ $car?->color_es ?? 'Gris' }}</dd></div>
              </dl>
            </div>
            <div class="ph__bar"><div class="mc-bar">
              <div class="mc-bar__pair"><span class="mc-bar__price">24.800 €</span><span class="mc-bar__km">184.000 km</span></div>
              <div class="mc-bar__act"><span class="mc-btn mc-btn--cta">WhatsApp</span><span class="mc-btn mc-btn--outline" style="padding-inline:var(--s-3)">Tel</span></div>
            </div></div>
          </div>
          <div class="ph__home"></div>
        </div>
        <div>
          <span class="bb-stagecap">The budget at 390px, with the longest realistic price and mileage</span>
          <div class="bb-scroll"><table class="bb-t" style="min-width:0">
            <tbody>
              <tr><td>Gutter</td><td class="bb-num-cell">16</td></tr>
              <tr><td>Price and km</td><td class="bb-num-cell">82</td></tr>
              <tr><td>Gap</td><td class="bb-num-cell">12</td></tr>
              <tr><td>WhatsApp</td><td class="bb-num-cell">137</td></tr>
              <tr><td>Gap</td><td class="bb-num-cell">12</td></tr>
              <tr><td>Call, "Tel"</td><td class="bb-num-cell">48</td></tr>
              <tr><td>Gutter</td><td class="bb-num-cell">16</td></tr>
              <tr><td><b>Total</b></td><td class="bb-num-cell"><b>323 of 390</b></td></tr>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
    <p class="bb-el__why"><b>Two actions, not four.</b> Four columns give 77px of usable width
      each, and "WhatsApp" at readable size already takes 58px. The price stays in the bar:
      on a €15–26k purchase the buyer re-checks it constantly, and a bar without it forces
      a scroll every time.</p>
    <div class="bb-note" style="margin:var(--s-4);border-left-color:var(--mc-accent)">
      <p style="margin:0"><b>Broken on your live site today, and no approval fixes it.</b>
      The page is missing <code>viewport-fit=cover</code>, so any fixed bar sits underneath
      the iPhone home indicator. One line. <span class="bb-st bb-st--fixed">Fixed</span></p>
    </div>
    <div class="bb-sign"><span class="bb-label">EL-BAR-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-FORM-01 ══ --}}
  <article class="bb-el" id="EL-FORM-01">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-FORM-01</span>
      <span class="bb-el__name">Pregúntame por este coche</span>
      <div class="bb-el__where"><span>Ficha de coche</span></div>
    </div>
    <div class="bb-el__stage bb-el__stage--band">
      <div class="bb-el__stage--twin" style="grid-template-columns:1fr;justify-items:center">
        <div style="display:grid;gap:var(--s-5);grid-template-columns:repeat(auto-fit,minmax(300px,390px));justify-content:center;width:100%">
          <div class="ph">
            <div class="ph__status"><span>9:41</span><i></i></div>
            <div class="ph__screen"><div class="pg-sec">
              <h2 class="pg-h">Pregúntame por este coche</h2>
              <form class="mc-form" onsubmit="return false">
                <label class="mc-field"><span class="mc-field__label">Cómo te llamas</span><input class="mc-input" type="text"></label>
                <label class="mc-field"><span class="mc-field__label">Tu teléfono</span><input class="mc-input" type="tel" placeholder="614 753 187"></label>
                <label class="mc-field"><span class="mc-field__label">Qué quieres saber <span class="mc-field__opt">(opcional)</span></span>
                  <textarea class="mc-textarea" rows="2">Me interesa el {{ $car?->brand }} {{ $car?->model }}. ¿Sigue disponible?</textarea></label>
                <label class="mc-check"><input type="checkbox"><span class="mc-check__t">Guardo tu nombre y tu teléfono solo para contestarte.</span></label>
                <a class="mc-go" href="#EL-FORM-01" style="font-size:var(--t-small);margin-bottom:var(--s-4)">Cómo trato tus datos <span class="mc-go__arrow">&rarr;</span></a>
                <span class="mc-btn mc-btn--block">Preguntar por este coche</span>
              </form>
            </div></div>
            <div class="ph__home"></div>
          </div>
          <div class="ph">
            <div class="ph__status"><span>9:41</span><i></i></div>
            <div class="ph__screen"><div class="pg-sec">
              <div class="mc-alert mc-alert--error">
                <p class="mc-alert__t">No he podido enviarlo. Falta una cosa:</p>
                <ul><li>Necesito un teléfono para poder contestarte.</li></ul>
              </div>
              <label class="mc-field"><span class="mc-field__label">Cómo te llamas</span><input class="mc-input" type="text" value="Marta"></label>
              <label class="mc-field"><span class="mc-field__label">Tu teléfono</span><input class="mc-input is-invalid" type="tel" value="">
                <span class="mc-err">Necesito un teléfono para poder contestarte.</span></label>
              <span class="mc-btn mc-btn--block is-loading"><span class="mc-btn__label" data-busy="Enviando…">Preguntar por este coche</span><span class="mc-btn__prog"></span></span>
              <div class="mc-alert mc-alert--ok" style="margin-top:var(--s-6)">
                <p class="mc-alert__t">Recibido. Te contesto yo, hoy o mañana por la mañana.</p>
                <p style="margin:0">Si prefieres no esperar, escríbeme por <a class="mc-link" href="#EL-FORM-01">WhatsApp</a>.</p>
              </div>
            </div></div>
            <div class="ph__home"></div>
          </div>
        </div>
      </div>
      <span class="bb-stagecap" style="margin:var(--s-4) 0 0;text-align:center">First: empty. Second: one field missing, then sending, then sent.</span>
    </div>
    <p class="bb-el__why">The message box is <b>already filled in with the question the buyer
      was going to ask</b>, and it is optional — an empty text box on a phone is where
      enquiries die. Every control is 16px and 48px tall: below 16px, Safari on iPhone zooms
      the page when you tap a field and never zooms back out. Two things were removed from
      your current form — the <em>Asunto</em> dropdown and the newsletter checkbox, for a
      newsletter that does not exist. <span class="bb-st bb-st--prop">Proposed</span></p>
    <div class="bb-sign"><span class="bb-label">EL-FORM-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-SMALL — the atoms, together ══ --}}
  <article class="bb-el" id="EL-SMALL">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-SMALL</span>
      <span class="bb-el__name">Las piezas pequeñas</span>
      <div class="bb-el__where"><span>Tarjeta</span><span>Ficha</span><span>Textos</span></div>
    </div>
    <div class="bb-el__stage">
      <div class="bb-atoms">
        <div class="bb-atom">
          <div class="bb-atom__n">Precio y kilómetros<small>EL-PRICE-01 · never one without the other</small></div>
          <div class="bb-atom__s">
            <div><div class="mc-pair"><span class="mc-price">24.800 €</span><span class="mc-km">184.000 km</span></div></div>
            <div><div class="mc-pair"><span class="mc-price">14.800 €</span><span class="mc-km is-unknown">Km sin confirmar</span></div></div>
          </div>
        </div>
        <div class="bb-atom">
          <div class="bb-atom__n">Estado<small>EL-BADGE-01 · always the word, never colour alone</small></div>
          <div class="bb-atom__s">
            <span class="mc-badge mc-badge--available">Disponible</span>
            <span class="mc-badge mc-badge--reserved">Reservado</span>
            <span class="mc-badge mc-badge--sold">Vendido</span>
          </div>
        </div>
        <div class="bb-atom">
          <div class="bb-atom__n">Datos rápidos<small>EL-CHIP-01 · not tappable, so allowed under 44px</small></div>
          <div class="bb-atom__s">
            <ul class="mc-chips"><li class="mc-chip">{{ $car?->year }}</li><li class="mc-chip">{{ $car?->fuel_es }}</li><li class="mc-chip">{{ $car?->transmission_es }}</li></ul>
          </div>
        </div>
        <div class="bb-atom">
          <div class="bb-atom__n">Enlaces<small>EL-LNK-01 · underlined at rest, so they work without colour</small></div>
          <div class="bb-atom__s">
            <span style="font-family:var(--f-voice);font-size:var(--t-prose)">Los kilómetros están en <a class="mc-link" href="#EL-SMALL">la lista de coches</a>.</span>
            <a class="mc-go" href="#EL-SMALL">Ver el coche <span class="mc-go__arrow">&rarr;</span></a>
          </div>
        </div>
        <div class="bb-atom">
          <div class="bb-atom__n">Avisos<small>EL-ALERT-01 · success replaces the form in place, never a toast</small></div>
          <div class="bb-atom__s" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--s-3);width:100%">
            <div class="mc-alert mc-alert--ok" style="margin:0"><p class="mc-alert__t" style="margin:0">Recibido.</p></div>
            <div class="mc-alert mc-alert--error" style="margin:0"><p class="mc-alert__t" style="margin:0">Falta el teléfono.</p></div>
            <div class="mc-alert mc-alert--note" style="margin:0"><p style="margin:0">Precio orientativo.</p></div>
          </div>
        </div>
        <div class="bb-atom">
          <div class="bb-atom__n">Sin foto<small>EL-IMG-02 · never a stock silhouette</small></div>
          <div class="bb-atom__s">
            <div class="mc-frame mc-frame--card mc-frame--empty" style="width:160px"><span class="mc-frame__none">Sin foto</span></div>
          </div>
        </div>
      </div>
    </div>
    <p class="bb-el__why">These do not each need a page. In the catalogue, where every car
      is available, <b>the "Disponible" badge is not printed</b> — a badge on 9 of 9 cards
      is decoration. Coral and green are the same grey to a red-green colourblind
      buyer, roughly one man in twelve, so no state is ever carried by colour alone.</p>
    <div class="bb-sign"><span class="bb-label">EL-SMALL</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>

  {{-- ══ EL-EMPTY + EL-FOOT ══ --}}
  <article class="bb-el" id="EL-FOOT-01">
    <div class="bb-el__bar">
      <span class="bb-el__id">EL-FOOT-01</span>
      <span class="bb-el__name">Pie, y el catálogo vacío</span>
      <div class="bb-el__where"><span>Todo el sitio</span><span>Catálogo</span></div>
    </div>
    <div class="bb-el__stage bb-el__stage--flush">
      <div class="mc-foot-demo on-navy" style="padding-inline:var(--s-5)">
        <div class="mc-foot-demo__cols">
          <div>
            <p style="margin:0 0 var(--s-2);font-size:var(--t-h3);font-weight:600;color:var(--mc-on-navy)">IV MOTORCLASS</p>
            <p style="margin:0 0 var(--s-2);font-size:var(--t-small)">Málaga, España</p>
            <p style="margin:0;font-size:var(--t-small)">Lun–Vie 10:00–19:00 · Sábado 10:00–14:00 · Domingo, consultar</p>
          </div>
          <div><h4>Coches</h4><a href="#EL-FOOT-01">Disponibles</a><a href="#EL-FOOT-01">Entregados</a></div>
          <div><h4>Contacto</h4><a href="#EL-FOOT-01">WhatsApp</a><a href="#EL-FOOT-01">614 753 187</a></div>
        </div>
      </div>
    </div>
    <div class="bb-el__stage">
      <span class="bb-stagecap">When everything is sold — good news for a one-man dealer, so it is not drawn as a failure</span>
      <div class="mc-empty">
        <p class="mc-empty__t">Ahora mismo no tengo ningún coche disponible.</p>
        <p style="font-family:var(--f-voice);font-size:var(--t-prose);line-height:1.62;color:var(--mc-ink-2);max-width:52ch">Suelo tener entre 8 y 10. Escríbeme y te aviso en cuanto entre algo que encaje con lo que buscas.</p>
        <div class="mc-cta" style="max-width:30rem">
          <span class="mc-btn mc-btn--cta">WhatsApp</span>
          <span class="mc-btn mc-btn--outline">Ver los {{ $inventory['sold'] }} entregados</span>
        </div>
      </div>
    </div>
    <p class="bb-el__why">On a phone the footer links become <b>48px rows with hairlines</b>,
      not a stack of small text. The address, phone and hours are real text, never an image
      — that block is what Google reads to place you in Málaga.</p>
    <div class="bb-sign"><span class="bb-label">EL-FOOT-01</span>
      <span class="bb-sign__box">Aprobado</span><span class="bb-sign__box">Con cambios</span>
      <span class="bb-sign__note">Nota:</span></div>
  </article>
</section>


{{-- ════════════════════════════════════════════════ 3 WALL ══ --}}
<section class="bb-section" id="wall">
  <div class="bb-head">
    <span class="bb-num bb-label">04</span>
    <h2>The customer wall</h2>
  </div>
  <p class="bb-prose">
    You asked for the photograph <em>and</em> the customer's words, more compactly than the
    slider you have now. We measured your {{ $inventory['testimonials'] }} testimonials
    before designing anything, because the right layout depends on how long people
    actually write.
  </p>

  @if(!empty($quotes['n']))
    <div class="bb-scroll">
    <table class="bb-t">
      <thead><tr><th>Measured on your database, right now</th><th class="bb-num-cell">Characters</th></tr></thead>
      <tbody>
        <tr><td>Shortest real comment</td><td class="bb-num-cell">{{ $quotes['min'] }}</td></tr>
        <tr><td>Typical comment (median)</td><td class="bb-num-cell"><b>{{ $quotes['median'] }}</b></td></tr>
        <tr><td>Long comment (top 10%)</td><td class="bb-num-cell">{{ $quotes['p90'] }}</td></tr>
        <tr><td>Longest</td><td class="bb-num-cell">{{ $quotes['max'] }}</td></tr>
      </tbody>
    </table>
    </div>

    <p class="bb-prose" style="margin-top:var(--s-5)">
      The important part is not the average — it is the <b>gap</b>. {{ $quotes['band'] }} of
      your {{ $quotes['n'] }} comments sit between 133 and 245 characters;
      {{ $quotes['n'] - $quotes['band'] - $quotes['tail'] }} are shorter, and
      {{ $quotes['tail'] }} run long — and the first long one is a full step away. That gap
      decides the design:
    </p>

    <div class="bb-scroll">
    <table class="bb-t">
      <thead><tr><th>If we show up to…</th><th class="bb-num-cell">…shown in full</th><th class="bb-num-cell">Gain</th><th>What the extra step buys</th></tr></thead>
      <tbody>
        @php $prev = null; $prevCap = null; @endphp
        @foreach($quotes['caps'] as $cap => $pctShown)
          @php
            // The verdict is computed, not typed: what does each extra step actually buy?
            $gain  = $prev === null ? null : (int) $pctShown - (int) $prev;
            $lines = $prevCap === null ? 0 : (int) ceil(($cap - $prevCap) / 34);   // 34 chars per line at desktop width
            $prev = $pctShown; $prevCap = $cap;
          @endphp
          <tr>
            <td>{{ $cap }} characters</td>
            <td class="bb-num-cell"><b>{{ $pctShown }}%</b></td>
            <td class="bb-num-cell">{{ $gain === null ? '—' : ($gain > 0 ? '+'.$gain : '0') }}</td>
            <td>
              @if($gain === 0)<span class="bb-exempt">nothing at all — this is the dead zone</span>
              @elseif($cap == 250)<span class="bb-pass">the knee — last cheap step</span>
              @elseif($gain !== null && $gain >= 20)<span class="bb-exempt">still climbing</span>
              @elseif($gain === null)<span class="bb-exempt">starting point</span>
              @else<span class="bb-exempt">{{ $gain }} points, at {{ $lines }} more line{{ $lines === 1 ? '' : 's' }} on every row</span>@endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
      Nine lines lands on that knee on a phone — about 240 characters at 390px. On a
      desktop the same nine lines hold about 340, so the limit is nine lines rather than a
      fixed character count: it adapts to the width instead of cutting words in half.</p>
  @endif

  <h3>The layout: photo left, words right</h3>
  <p class="bb-prose">
    <b>Between 208 and 244px per customer on a phone, instead of 745px</b> — 295 on a row
    that carries a <em>Ver más</em> — roughly three
    times denser than the slider you have, so two or three people are on screen at once
    instead of one. And the photograph stays large enough to be a photograph of a person
    next to a car, not a round avatar.
  </p>
  <p class="bb-prose">
    The reason it works: the photo already forces a certain height, and nine lines of text
    fits inside roughly that same height. For most of your comments the words cost nothing
    beyond the photograph; the longest ones in the band add about 36px.
  </p>

  @if($sample->count())
    <div class="tt-list" style="margin-top:var(--s-5)">
      @php $wallSample = $sample->take(3)->push($sample->first(fn($t) => mb_strlen(trim((string)$t->quote)) > 360)); @endphp
      @foreach($wallSample as $t)
        @php
          $rel = ltrim(preg_replace('#^/?storage/#', '', $t->image_path ?? ''), '/');
          $src = $rel ? asset('storage/'.$rel) : null;
          $fs  = $rel ? storage_path('app/public/'.$rel) : null;
          $cls = '';
          if ($fs && is_file($fs)) {
            $sz = @getimagesize($fs);
            if ($sz && $sz[1]) { $rr = $sz[0]/$sz[1]; $cls = $rr < 0.62 ? 'is-tall' : ($rr > 1.05 ? 'is-wide' : ''); }
          }
          $q = trim((string) $t->quote);
        @endphp
        <article class="tt-row">
          <div class="tt-photo {{ $cls }}">
            @if($src)<img class="mc-img mc-img--portrait" src="{{ $src }}"
              alt="{{ str_replace('&', ' y ', $t->author_name) }}" loading="lazy" decoding="async"
              width="400" height="{{ $cls === 'is-tall' ? 560 : ($cls === 'is-wide' ? 300 : 500) }}">@endif
          </div>
          <div class="tt-body">
            @if(mb_strlen($q) > 2)
              <p class="tt-quote is-clamped">{{ $q }}</p>
              @if(mb_strlen($q) > 220)<button class="tt-more" type="button" aria-expanded="false">Ver más</button>@endif
            @endif
            <span class="tt-attr"><b>{{ str_replace('&', ' y ', $t->author_name) }}</b>
              @if($t->author_location) · {{ $t->author_location }}@endif</span>
          </div>
        </article>
      @endforeach
    </div>
    <p class="bb-small" style="margin-top:var(--s-3);color:var(--mc-ink-3)">
      Four of {{ $inventory['testimonials'] }}. On the site we show five, then
      <em>Ver las {{ $inventory['testimonials'] }} opiniones</em> — showing all of them at
      once would be eight phone screens of a single section.</p>
  @endif

  <div class="bb-two" style="margin-top:var(--s-6)">
    <div class="bb-dont">
      <h3>The stars have to go <span class="bb-st bb-st--prop">Proposed</span></h3>
      <ul>
        <li>Your current page prints <b>five filled stars on every single testimonial</b>,
          with the label "5 de 5".</li>
        <li>There is no rating column in your database. They are not data — they are
          decoration asserting a score no customer ever gave.</li>
        <li>Twenty-five identical perfect scores reads as a template, and it makes a
          visitor wonder what else is decorative — including the photographs, which are
          real.</li>
        <li>If you want a rating, the only kind with weight is one someone else controls:
          <em>4,9 en Google · 87 reseñas</em>, linked to the actual profile.</li>
      </ul>
    </div>
    <div class="bb-do">
      <h3>Three things worth surfacing</h3>
      <ul>
        <li><b>Where people came from.</b> The photographs were taken in
          {{ $quotes['home'] }} — that is what the location column says for
          {{ $quotes['n'] + 1 - $quotes['awayN'] }} of them — and
          {{ $quotes['awayN'] }} of your {{ $inventory['testimonials'] }} customers are recorded
          elsewhere: {{ implode(', ', $quotes['awayCities']) }}. Right now that is greyed-out
          small print; a customer's city is the cheapest verifiable detail the row carries.</li>
        <li><b>One testimonial is broken</b> — the comment is a single comma. Worth editing
          or hiding.</li>
        <li><b>Four comments contain paragraph breaks</b> that the current page flattens
          into one block. Restoring them costs nothing and helps exactly the long comments
          that need it.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════════ 4 COLOUR ══ --}}
<section class="bb-section" id="colour">
  <div class="bb-head">
    <span class="bb-num bb-label">05</span>
    <h2>Colour</h2>
  </div>
  <p class="bb-prose">
    A clean blue used the way car-planet uses it — as ink and edges, rarely as a field —
    a coral for the price, WhatsApp's green for the one button that matters, and a lot of
    white in between so your photographs stay the loudest thing on the page.
  </p>

  <div class="bb-note">
    <p><b>Why this blue and not a brighter one.</b> Bright blues in the
    <code>#0066FF</code> family are the register of banks and apps, not of a dealer in
    Málaga. They also fight the photographs: your pictures are warm, sunlit, high-contrast
    phone shots, and a saturated blue panel next to a silver Golf pulls the eye off the
    car. This blue sits back behind the photography instead of arguing with it.</p>
    <p style="margin-bottom:0">It is also deliberately kept below hue 220. Above that,
    wide-gamut phone screens — which is most mid-range Android sold in Spain — shift blue
    toward violet, and a brand that is violet on half the devices is not a brand.
    <span class="bb-st bb-st--fixed">Fixed</span></p>
  </div>

  @foreach($colors as $group => $rows)
    <h3>{{ $group }}</h3>
    <div class="bb-grid">
      @foreach($rows as $c)
        <div class="bb-sw">
          <div class="bb-sw-chip" style="background:{{ $c['hex'] }};color:{{ $c['label'] }}">{{ $c['hex'] }}</div>
          <div class="bb-sw-body"><code>{{ $c['var'] }}</code><p>{{ $c['why'] }}</p></div>
        </div>
      @endforeach
    </div>
  @endforeach

  <h3>How much of each</h3>
  <p class="bb-prose">On a typical page, as an approximate share of the screen, in the
  proportion the reference keeps — we counted its tints and fills, not its pixels. It is
  what lets amateur photography carry a page.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Layer</th><th class="bb-num-cell">Share</th><th>Where it is allowed</th></tr></thead>
    <tbody>
      <tr><td><b>Photographs</b></td><td class="bb-num-cell">30–35%</td><td>Cards, gallery, the customer wall</td></tr>
      <tr><td><b>Neutral</b></td><td class="bb-num-cell">~55%</td><td>Everything else. This is the palette.</td></tr>
      <tr><td><b>Navy</b></td><td class="bb-num-cell">~6%</td><td>Footer only</td></tr>
      <tr><td><b>Blue</b></td><td class="bb-num-cell">~4%</td><td>Links, active menu item, outline buttons, the form's one filled button, one tinted section per page</td></tr>
      <tr><td><b>Coral</b></td><td class="bb-num-cell">~1%</td><td>The price, and nothing else</td></tr>
      <tr><td><b>WhatsApp green</b></td><td class="bb-num-cell">~1%</td><td>The WhatsApp button, and a sent-message confirmation. Nothing else</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-dont">
      <h3>Blue is never used for</h3>
      <ul>
        <li><b>The price.</b> Blue on a number reads as a link, and a price that looks
          tappable but isn't is the most damaging small failure on a car card.</li>
        <li><b>The WhatsApp button.</b> That is green on every page. If blue and green
          are both filled buttons on one screen, the green has stopped meaning "message
          him".</li>
        <li><b>Behind or over a photograph.</b> No blue hero behind a car, no tint overlay
          on the customer wall. Anything laid over a real photo reads as retouching.</li>
        <li><b>Spec values.</b> Blue in a data table implies everything is filterable.</li>
      </ul>
    </div>
    <div class="bb-note">
      <h3>One honest limitation</h3>
      <p>Coral and the WhatsApp green are close in brightness. To a
      red-green colourblind buyer — roughly one man in twelve — they are the same grey.</p>
      <p style="margin-bottom:0">So green is used <b>only</b> for confirmations ("message
      sent"), never for "available", and every status always carries the Spanish word.
      Colour never carries meaning on its own anywhere on this site.
      <span class="bb-st bb-st--fixed">Fixed</span></p>
    </div>
  </div>
</section>

{{-- ════════════════════════════════════════════════ 5 TYPE ══ --}}
<section class="bb-section" id="type">
  <div class="bb-head">
    <span class="bb-num bb-label">06</span>
    <h2>Type</h2>
  </div>

  <div class="bb-two">
    <div>
      <h3>DM Sans — everything</h3>
      <p class="bb-small">Headings, buttons, labels, prose, and every number. It is the
      typeface car-planet renders on 485 of its 509 measured text nodes, taken deliberately
      on your instruction to get as close to their register as possible. One family, no
      serif — exactly as they do it.</p>
      <p class="mc-pair" style="margin-top:0"><span class="mc-price">24.800 €</span><span class="mc-km">184.000 km</span></p>
    </div>
    <div>
      <h3>The customer's voice — italic, same family</h3>
      <p class="bb-small">An earlier draft set the customer quotes in a serif. That was a
      second typeface to load and a large part of what read as old-school. The quotes keep
      a distinct voice with DM Sans italic instead — a different speaker, no second font.</p>
      <p class="tt-quote" style="margin-top:var(--s-4)">Compramos el coche
      para nuestra hija. Nos gustó el estado impecable y el historial claro.</p>
    </div>
  </div>

  <div class="bb-scroll" style="margin-top:var(--s-6)">
  <table class="bb-t">
    <thead><tr><th>Size</th><th class="bb-num-cell">Phone → desktop</th><th>Used for</th></tr></thead>
    <tbody>
      @foreach($type as [$var,$px,$lh,$wt,$wd,$tr,$fam,$use])
        <tr><td><code>{{ $var }}</code></td><td class="bb-num-cell"><b>{{ $px }}</b></td><td>{{ $use }}</td></tr>
      @endforeach
    </tbody>
  </table>
  </div>

  <div class="bb-two" style="margin-top:var(--s-6)">
    <div class="bb-do">
      <h3>Two hard floors <span class="bb-st bb-st--fixed">Fixed</span></h3>
      <ul>
        <li><b>13px is the smallest text anywhere.</b> No small caps, no tiny uppercase.
          Those are what make a dealer site unreadable in sunlight.</li>
        <li><b>16px minimum inside any form field.</b> Below that, Safari on iPhone zooms
          the page when you tap and never zooms back out. This is not a preference.</li>
      </ul>
    </div>
    <div class="bb-note">
      <h3>Fonts cost nothing here</h3>
      <p style="margin-bottom:0">One family, free, loaded from Google in two faces — upright
      and italic — with a metric-matched fallback so the page does not jump when it
      arrives. Total added weight is smaller than one of your car photographs.</p>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════════ 6 MOBILE ══ --}}
<section class="bb-section" id="mobile">
  <div class="bb-head">
    <span class="bb-num bb-label">07</span>
    <h2>Mobile</h2>
  </div>
  <p class="bb-prose">
    Your customer is standing next to the car, one-handed, in sunlight, on 4G. Every
    decision in this book was made for that first and for the desktop second.
  </p>

  <div class="bb-note">
    <p><b>The number that matters.</b> An iPhone screen is 844px tall, but the browser's
    own bars eat 85 of them. The real working area is about <b>759px</b>, and
    <code>100vh</code> in CSS gives you the wrong one — roughly 85px taller than what the
    person can actually see. <span class="bb-st bb-st--fixed">Fixed</span></p>
  </div>

  <h3>Where the thumb reaches</h3>
  <div style="max-width:390px;border:1px solid var(--mc-rule);border-radius:var(--mc-r-l);overflow:hidden">
    <div style="display:flex;gap:var(--s-3);align-items:flex-start;height:180px;padding:var(--s-3) var(--s-4);background:var(--mc-band);font-size:var(--t-small);border-bottom:1px solid var(--mc-hairline)"><b style="flex:0 0 8ch;font-variant-numeric:tabular-nums">0–180</b><span>Hard to reach. Logo, secondary links. Nothing frequent.</span></div>
    <div style="display:flex;gap:var(--s-3);align-items:flex-start;height:250px;padding:var(--s-3) var(--s-4);font-size:var(--t-small);border-bottom:1px solid var(--mc-hairline)"><b style="flex:0 0 8ch;font-variant-numeric:tabular-nums">180–430</b><span>Reachable with effort. Main photo, headline.</span></div>
    <div style="display:flex;gap:var(--s-3);align-items:flex-start;height:230px;padding:var(--s-3) var(--s-4);background:var(--mc-blue-tint);font-size:var(--t-small);border-bottom:1px solid var(--mc-hairline)"><b style="flex:0 0 8ch;font-variant-numeric:tabular-nums">430–660</b><span><b>Natural thumb arc.</b> The price, the contact button.</span></div>
    <div style="display:flex;gap:var(--s-3);align-items:flex-start;height:99px;padding:var(--s-3) var(--s-4);font-size:var(--t-small)"><b style="flex:0 0 8ch;font-variant-numeric:tabular-nums">660–759</b><span>The fixed bar. WhatsApp and Call.</span></div>
  </div>

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-note" style="border-left-color:var(--mc-accent)">
      <h3>The biggest problem on your site today</h3>
      <p>Measured at 390px on the live pages: <b>45 things too small to tap reliably on the
      home page, 41 in the catalogue, 27 on a car page.</b> The minimum is 44×44px.</p>
      <p style="margin-bottom:0">Everything in this book is built at 44px minimum, 48
      preferred. Taking those three numbers to zero is the single largest usability gain
      available. <span class="bb-st bb-st--fixed">Fixed</span></p>
    </div>
    <div class="bb-do">
      <h3>What is removed on a phone, not shrunk</h3>
      <ul>
        <li>Breadcrumbs become a single 44px <em>← Todos los coches</em> link.</li>
        <li>The testimonial slider becomes the row list.</li>
        <li>Nothing that leaves the car page goes in the bottom third — that space belongs
          to WhatsApp and Call.</li>
      </ul>
      <p class="bb-small" style="margin-top:var(--s-3);margin-bottom:0">Shrinking a desktop
      component to fit a phone is how sites end up with a 12px chip nobody can hit.</p>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════════ 7 PHOTOS ══ --}}
<section class="bb-section" id="photos">
  <div class="bb-head">
    <span class="bb-num bb-label">08</span>
    <h2>Photographs</h2>
  </div>
  <p class="bb-prose">
    Your photographs are the only thing on this site a competitor cannot copy. The frames
    are chosen from the pictures you actually have, measured when this page loaded — not
    from a template.
  </p>

  @if(!empty($metrics['groups']))
    <div class="bb-scroll">
    <table class="bb-t">
      <thead><tr><th>Your library</th><th class="bb-num-cell">Files</th><th class="bb-num-cell">Portrait</th><th class="bb-num-cell">Landscape</th><th class="bb-num-cell">Most common shape</th></tr></thead>
      <tbody>
        @foreach($metrics['groups'] as $name => $g)
          @php $mk = array_key_first($g['modes'] ?? []); $mn = $g['modes'][$mk] ?? 0; @endphp
          <tr>
            <td><b>{{ $name === 'vehicles' ? 'Car photos' : 'Customer photos' }}</b></td>
            <td class="bb-num-cell">{{ $fmt($g['n']) }}</td>
            <td class="bb-num-cell">{{ $fmt($g['portrait']) }}</td>
            <td class="bb-num-cell">{{ $fmt($g['landscape']) }}</td>
            <td class="bb-num-cell">{{ $g['n'] ? round($mn/$g['n']*100) : 0 }}% at {{ ['1.3333'=>'4:3','0.75'=>'3:4','1.5'=>'3:2','0.6667'=>'2:3','1'=>'1:1','1.7778'=>'16:9'][(string)$mk] ?? $mk }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
    <p class="bb-prose" style="margin-top:var(--s-4)">
      Three quarters of your car photos are already exactly 4:3, so a 4:3 card frame crops
      nothing at all across most of the stock. A widescreen frame would take 25% of the
      height — split between the roof and the bottom of the wheel arches — and cut wheel
      arches make a car look sunken and cheap.
    </p>
  @endif

  <div class="bb-note">
    <p><b>A correction to my own earlier work.</b> I had the customer photos positioned to
    protect the top of the frame, on the assumption that faces sit in the top third. We
    then actually measured it — 40,000 pixels sampled per photo, sorted by depth.</p>
    <p><b>Faces are at 20–50% down, peaking at 30–40%. Only 8.8% of skin is in the top
    fifth</b>, which is sky and warehouse ceiling. Favouring the top preserved empty
    headroom and ate the car and the certificate. The framing here is now
    <code>{{ $tokens['--pos-portrait'] ?? '50% 40%' }}</code>, measured rather than assumed.</p>
    <p><b>Your live site still carries the wrong value.</b> When you asked me not to cut
    people's faces, I set the framing to <code>center 25%</code> on the home page. Faces
    are not cut at 25% — but it keeps empty sky and loses the bottom of the car and the
    certificate people are holding. Changing one value to <code>center 40%</code> fixes
    it. <span class="bb-st bb-st--fixed">Fixed</span></p>
    <p style="margin-bottom:0"><b>No frame may crop more than 25% of a photo's height.</b>
    Audited live against all {{ $crops['n'] }} of your customer photos:
    worst case <b>{{ $crops['worst'] }}%</b>,
    @if($crops['over'] === 0)<span class="bb-pass">zero over the limit</span>@else<span class="bb-fail">{{ $crops['over'] }} over the limit</span>@endif.
    Tall and landscape photos get a different frame rather than a harder crop.</p>
  </div>

  @if(!empty($metrics['exif']))
    @php $ex = $metrics['exif']; $pct = $ex['total'] ? round($ex['rotated']/$ex['total']*100,1) : 0; @endphp
    <div class="bb-note" style="border-left-color:var(--mc-accent);margin-top:var(--s-5)">
      <h3>A bug that is live on your site right now</h3>
      <p><b>{{ $fmt($ex['rotated']) }} of the {{ $fmt($ex['total']) }} files that carry orientation data ({{ $pct }}%) are
      being served rotated 90°.</b> The camera writes the orientation into the file; the
      browser respects it, but the code that makes the small versions ignores it. So the
      same photo looks upright when opened directly and sideways on every card and
      thumbnail.</p>
      <p style="margin-bottom:0">Fixed here, roughly fifteen lines. It affects
      {{ $fmt($ex['rotated']) }} of your car photographs. <span class="bb-st bb-st--fixed">Fixed</span></p>
    </div>
  @endif

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-do">
      <h3>Publishable</h3>
      <ul>
        <li>Daylight, the car where it actually stands.</li>
        <li>The odometer, readable, at full size.</li>
        <li>The customer with the car they bought.</li>
        <li>Any fault, photographed as clearly as the good side.</li>
      </ul>
    </div>
    <div class="bb-dont">
      <h3>Never</h3>
      <ul>
        <li>Stock photography of any kind. It is the loudest fake signal there is, and it
          would undo what your {{ $crops['n'] }} real photographs are doing.</li>
        <li>Manufacturer renders and studio cut-outs.</li>
        <li>Filters, vignettes, retouched panels.</li>
        <li>Any photo where the face is cropped.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ═══════════════════════════════════════════════ 8 VOICE ══ --}}
<section class="bb-section" id="voice">
  <div class="bb-head">
    <span class="bb-num bb-label">09</span>
    <h2>Voice</h2>
  </div>
  <p class="bb-prose">
    Everything follows from one fact a large group cannot claim: <b>there is a person behind
    this business and he has driven every car on the site.</b> So the site speaks as a
    person, not as a company.
  </p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:min(22ch,35%)">Rule</th><th>In practice</th></tr></thead>
    <tbody>
      <tr><td><b>First person</b></td><td><em>Yo</em> for anything involving judgement or contact. Never <em>nosotros</em> — a one-man business writing "our team" is the first thing a buyer notices.</td></tr>
      <tr><td><b>A fact, not an adjective</b></td><td>Every claim carries a number, a date or a place. Adjectives without evidence are what make copy read as written by a machine.</td></tr>
      <tr><td><b>Calm</b></td><td>No exclamation marks in commercial text, no manufactured urgency. A small seller who shouts sounds desperate.</td></tr>
    </tbody>
  </table>
  </div>

  <h3>Ready to use, in Spanish</h3>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th style="width:min(22ch,35%)">Where</th><th>Text</th></tr></thead>
    <tbody>
      <tr><td>Under the contact buttons</td><td>Contesto yo. Suelo tardar unas horas, no unos minutos.</td></tr>
      <tr><td>Catalogue standfirst</td><td>Los he comprado y conducido yo. Si preguntas por uno, te contesto yo.</td></tr>
      <tr><td>Back link</td><td>← Todos los coches</td></tr>
      <tr><td>Mileage unknown</td><td>Km sin confirmar</td></tr>
      <tr><td>Spec table heading</td><td>Los datos</td></tr>
      <tr><td>Enquiry button</td><td>Preguntar por este coche</td></tr>
      <tr><td>Sent</td><td>Recibido. Te contesto yo, hoy o mañana por la mañana.</td></tr>
      <tr><td>Missing phone</td><td>Necesito un teléfono para poder contestarte.</td></tr>
      <tr><td>Send failed</td><td>No ha salido. Si tienes prisa, escríbeme por WhatsApp al 614 753 187.</td></tr>
      <tr><td>Nothing in stock</td><td>Ahora mismo no tengo ningún coche disponible. Escríbeme y te aviso cuando entre algo.</td></tr>
      <tr><td>404</td><td>Esta página no existe. Estos son los coches que sí tengo.</td></tr>
      <tr><td>Opening hours</td><td>Lun–Vie 10:00–19:00 · Sábado 10:00–14:00 · Domingo, consultar</td></tr>
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <h3>Three sentences only you can approve <span class="bb-st bb-st--you">Your call</span></h3>
    <p>These are commercial and legal statements, not style choices. I have drafted them;
    they do not go live until you confirm each one is true of your business.</p>
    <div class="bb-scroll">
    <table class="bb-t">
      <tbody>
        <tr><td>Price note</td><td>Precio para particular. Transferencia e impuesto de matriculación no incluidos.</td></tr>
        <tr><td>Warranty</td><td>Garantía legal de 12 meses para vehículos de ocasión vendidos a particulares (art. 120 TRLGDCU).</td></tr>
        <tr><td>Finance</td><td>No hago financiación yo. Te puedo poner en contacto con una financiera; las condiciones las fija ella.</td></tr>
      </tbody>
    </table>
    </div>
  </div>
</section>

{{-- ═════════════════════════════════════════════ 9 SIGN-OFF ══ --}}
<section class="bb-section" id="signoff">
  <div class="bb-head">
    <span class="bb-num bb-label">10</span>
    <h2>Sign-off sheet</h2>
  </div>
  <p class="bb-prose">
    Nothing gets built until its line is ticked. For each element, four questions:
    <em>do I know where this appears on my site? can I tell the normal state from the
    pressed one without being told? can I tap it with my thumb without looking?
    would I say this?</em>
  </p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>ID</th><th>Element</th><th>Where</th><th>Approved</th><th>With changes</th></tr></thead>
    <tbody>
      @foreach([
        ['EL-CARD-01','Tarjeta de coche','Home, catálogo, 404'],
        ['EL-BTN-01','Botones — azul, WhatsApp, Llamar, discreto','Todo el sitio'],
        ['EL-HDR-01','Cabecera y menú','Todo el sitio'],
        ['EL-BAR-01','Barra fija inferior','Ficha, móvil'],
        ['EL-FORM-01','Pregúntame por este coche','Ficha'],
        ['EL-SMALL','Precio·km, estado, datos, enlaces, avisos, sin foto','Tarjeta, ficha'],
        ['EL-FOOT-01','Pie, y el catálogo vacío','Todo el sitio'],
      ] as [$id,$name,$where])
        <tr>
          <td><code>{{ $id }}</code></td><td><b>{{ $name }}</b></td><td>{{ $where }}</td>
          <td><label class="mc-check" style="justify-content:center"><input type="checkbox" name="{{ $id }}-ok" aria-label="Aprobado {{ $id }}"></label></td>
          <td><label class="mc-check" style="justify-content:center"><input type="checkbox" name="{{ $id }}-chg" aria-label="Con cambios {{ $id }}"></label></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>

  <div class="bb-two" style="margin-top:var(--s-5)">
    <div class="bb-note">
      <h3>Waiting on you <span class="bb-st bb-st--you">Your call</span></h3>
      <ul>
        <li>The three legal and commercial sentences in §9.</li>
        <li>Whether the WhatsApp green stays at the legible <code>#017B37</code> or moves to the brand's brighter <code>#25D366</code> with dark text on it.</li>
        <li>Whether we build filters now or wait until the stock passes about 25 cars.</li>
        <li>Whether the fabricated star ratings come off the testimonials.</li>
      </ul>
    </div>
    <div class="bb-note" style="border-left-color:var(--mc-accent)">
      <h3>Broken today, regardless of any decision <span class="bb-st bb-st--fixed">Fixed</span></h3>
      <ul>
        <li><code>viewport-fit=cover</code> is missing — any fixed bar sits under the
          iPhone home indicator. One line.</li>
        <li>{{ $fmt($metrics['exif']['rotated'] ?? 0) }} car photographs are served rotated 90°.
          Roughly fifteen lines, already written and tested here.</li>
        <li>Customer photos are framed at <code>center 25%</code> instead of
          <code>center 40%</code> — my error, one value.</li>
        <li>113 targets across three pages are too small to tap reliably.</li>
      </ul>
    </div>
  </div>
</section>

{{-- ══════════════════════════════════════════ A CONTRAST ══ --}}
<section class="bb-section" id="contrast">
  <div class="bb-head">
    <span class="bb-num bb-label">Appendix A</span>
    <h2>The contrast maths</h2>
  </div>
  <p class="bb-prose">
    You do not need to read this. It exists so that nobody has to take the colour section
    on trust. Every pair the site actually renders is computed when this page loads: text
    needs 4.5:1, a border or meaningful graphic needs 3:1.
  </p>

  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Pair</th><th>Used for</th><th class="bb-num-cell">Measured</th><th class="bb-num-cell">Needs</th><th>Verdict</th></tr></thead>
    <tbody>
      @foreach($contrast as $r)
        <tr>
          <td><span class="bb-pair-chip"><i style="background:{{ $r['bgHex'] }}"></i><i style="background:{{ $r['fgHex'] }}"></i>{{ $r['fgHex'] }} on {{ $r['bgHex'] }}</span></td>
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
    <p style="margin-bottom:0"><b>{{ $fails === 0 ? 'All '.count($contrast).' pairs clear their threshold.' : $fails.' pair(s) fail and must be fixed before use.' }}</b>
    Recomputed on every load, so changing a colour changes the verdict — the only way this
    stays true six months from now.</p>
  </div>

  <h3>Combinations the code must never produce</h3>
  <p class="bb-prose">Computed too, so the prohibition is evidence rather than opinion.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Pair</th><th>Never used for</th><th class="bb-num-cell">Measured</th><th>Use instead</th></tr></thead>
    <tbody>
      @foreach($forbidden as $f)
        <tr>
          <td><span class="bb-pair-chip"><i style="background:{{ $f['bgHex'] }}"></i><i style="background:{{ $f['fgHex'] }}"></i>{{ $f['fgHex'] }} on {{ $f['bgHex'] }}</span></td>
          <td>{{ $f['use'] }}</td>
          <td class="bb-num-cell"><span class="bb-fail">{{ number_format($f['ratio'], 2) }}:1</span></td>
          <td>{{ $f['instead'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>

  <div class="bb-note" style="margin-top:var(--s-5)">
    <p><b>Three failures were found and fixed while building this palette</b>, which is the
    point of computing rather than eyeballing:</p>
    <ul>
      <li>The keyboard focus ring on a blue button measured 2.40:1. It is now two rings —
        a white one inside an ink one — so the dark ring only ever sits against white.</li>
      <li>The input border passed on the page but failed at 2.79:1 inside a grey band. It
        was re-solved against the darkest surface it can land on and now clears 3:1 on all
        four.</li>
      <li>A coloured button on navy has no usable contrast at its edge, and no recolouring
        fixes it without turning navy black. So on navy the button flips to a white fill.</li>
    </ul>
  </div>
</section>

{{-- ══════════════════════════════════════════ B APPENDIX ══ --}}
<section class="bb-section" id="appendix">
  <div class="bb-head">
    <span class="bb-num bb-label">Appendix B</span>
    <h2>Space, motion, and the order of work</h2>
  </div>

  <h3>Spacing</h3>
  <p class="bb-prose">4px base, ten values, no others. A gap that is not on this ruler is a bug.</p>
  <div class="bb-ruler">
    @foreach($space as $s)
      <div><span style="width:{{ max(4,$s['px']) }}px"></span>
        <span class="bb-label" style="display:block;margin-top:var(--s-1)">{{ $s['px'] }}</span></div>
    @endforeach
  </div>

  <h3>Motion</h3>
  <p class="bb-prose">
    Almost none, on purpose. Cards lift slightly on hover, buttons change shade, and the
    sending button draws a thin line along its bottom edge while it works. That is the whole
    repertoire. The reference site animates nothing but a hover, and a site with six
    animation ideas looks like a template.
  </p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th>Token</th><th class="bb-num-cell">Value</th><th>Used for</th></tr></thead>
    <tbody>
      @foreach($motion['durations'] as $m)
        <tr><td><code>{{ $m['var'] }}</code></td><td class="bb-num-cell"><b>{{ $m['value'] }}</b></td><td>{{ $m['use'] }}</td></tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <p class="bb-small" style="margin-top:var(--s-4);color:var(--mc-ink-3)">
    No bounce and no spring anywhere. Bounce reads as playful; this business is asking
    someone for €20,000 on trust. And anyone who has switched on "reduce motion" in their
    phone settings sees none of it — nothing here carries meaning through movement.</p>

  <h3>Order of work</h3>
  <p class="bb-prose">Dependency order. Each step is only safe once the ones above it are done.</p>
  <div class="bb-scroll">
  <table class="bb-t">
    <thead><tr><th class="bb-num-cell" style="width:3ch">#</th><th>Step</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td class="bb-num-cell">1</td><td>Rotated photographs</td><td><span class="bb-pass">done here</span> · pending on the live site</td></tr>
      <tr><td class="bb-num-cell">2</td><td>Colour and type loaded before anything else</td><td><span class="bb-pass">done</span></td></tr>
      <tr><td class="bb-num-cell">3</td><td><code>viewport-fit=cover</code> on every page</td><td>Both sites</td></tr>
      <tr><td class="bb-num-cell">4</td><td>Elements built, in sign-off order</td><td>Blocked on §10</td></tr>
      <tr><td class="bb-num-cell">5</td><td>Vehicle card, shared by home and catalogue</td><td>After EL-CARD-01</td></tr>
      <tr><td class="bb-num-cell">6</td><td>Customer wall replaces the slider</td><td>After EL-CARD-01</td></tr>
      <tr><td class="bb-num-cell">7</td><td>Fixed bar with safe-area handling</td><td>Needs step 3, then a real iPhone</td></tr>
      <tr><td class="bb-num-cell">8</td><td>Touch-target sweep at 390px</td><td>Target: 0, from 113 today</td></tr>
      <tr><td class="bb-num-cell">9</td><td>Spanish text pass over every page</td><td>Blocked on the three sentences in §9</td></tr>
    </tbody>
  </table>
  </div>
</section>

</main>

<footer class="bb-foot on-navy">
  <div class="bb-wrap">
    <div class="bb-head"><h2>IV MOTORCLASS</h2></div>
    <p class="bb-small">Design system draft {{ $draft }} · Málaga, España<br>
      Generated from <code>public/css/mc-tokens.css</code> ·
      {{ count($tokens) }} values · {{ count($contrast) }} contrast pairs ·
      {{ $crops['n'] }} photographs audited</p>
    <p class="bb-small">A working document, not a public page. Mark it up and send it back.<br>
      <b>Nothing in this book has been applied to ivmotorclass.com.</b> Every element here
      lives only on this design copy; the live site is untouched until you sign each line
      of §10.</p>
    <p class="bb-mark" aria-hidden="true">MOTORCLASS</p>
  </div>
</footer>

<script>
(function(){
  document.querySelectorAll('.tt-more').forEach(function(b){
    var q0 = b.parentElement.querySelector('.tt-quote');
    if (q0 && q0.scrollHeight <= q0.clientHeight + 1) { b.hidden = true; return; }
    b.addEventListener('click', function(){
      var q = b.parentElement.querySelector('.tt-quote');
      var open = q.classList.toggle('is-clamped') === false;
      b.textContent = open ? 'Ver menos' : 'Ver más';
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  // the scroll fade exists only where a table actually overflows
  function fade(){ document.querySelectorAll('.bb-scroll').forEach(function(s){ s.classList.toggle('is-overflowing', s.scrollWidth > s.clientWidth + 1); }); }
  fade(); window.addEventListener('resize', fade);
  // desktop nav in the header specimen only appears above 900px on the real site
  function nav(){
    document.querySelectorAll('[data-desk]').forEach(function(n){
      n.style.display = window.innerWidth >= 900 ? 'flex' : 'none';
    });
  }
  nav(); window.addEventListener('resize', nav);
})();
</script>
</body>
</html>
