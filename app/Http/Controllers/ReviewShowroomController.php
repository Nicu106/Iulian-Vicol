<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Support\Img;
use Illuminate\Http\Response;

/**
 * /muestras/opiniones — a private showroom: four ways of showing the customer
 * reviews, for the client to choose one. Not linked from anywhere, noindex,
 * not in the sitemap. /muestras/opiniones/{n} renders one proposal on its own,
 * which is what the showroom's frames load.
 *
 * (2026-10-08) The client turned down all ten of the first round. This is
 * the second: three ideas pushed far, plus the one he drew himself.
 *
 *  1 La entrega    a dark pinned sequence, one customer per screen
 *  2 El muro       every delivery photograph in one justified wall
 *  3 Sus palabras  the strongest phrase of a review, huge, line by line
 *  4 Pares         the client's sketch: photo and words as a layered pair
 *
 * Two of them are pinned scroll sequences (PINNED). A pinned sequence cannot
 * live in a frame that is as tall as its content (there is nothing to scroll
 * inside it), so the showroom gives those a frame of a screen's height that
 * scrolls on its own (?embed=2); the other two keep the content-height frame
 * (?embed=1).
 *
 * Every proposal reads the same live data the home page reads, prepared once
 * here so the views only decide layout:
 *
 *  - the text is the customer's, untouched in the database. Here it is only
 *    tidied for print: CRLF paragraphs become paragraphs, runs of spaces
 *    collapse, a space before a comma goes, and a review typed entirely inside
 *    quotation marks loses the outer pair (each layout draws its own).
 *  - the name: "Diego&Paula" prints "Diego & Paula" with a no-break space
 *    before the ampersand so a couple never splits across lines on "&";
 *    a slip of the shift key ("MIguel") is printed "Miguel". The record keeps
 *    what the owner typed. author_location is never printed: most rows say
 *    Santander, including people whose own words say Valencia or Valladolid.
 *  - the photograph's real shape (EXIF-aware, via Img::size) — the box takes
 *    it, so nothing is cropped (CLAUDE.md §5).
 *  - an excerpt cut at a sentence end (sentences()), for the layouts that show
 *    a review's opening and open the rest on demand.
 */
class ReviewShowroomController extends Controller
{
    /** version => [page name, review title, what it is, approach].
     *  The page name is the page's own <title> (Spanish, as the page was made);
     *  everything else is review chrome, in English (the index, the version bar).
     *  Versions of one approach are kept side by side: 4 is Pares as first built
     *  and is frozen (git tag opiniones-v4.0); 4.4, 4.5 … are copies with their
     *  own view (v4-4.blade.php) and their own css/js (op44.css, op44.js), so an
     *  iteration never touches an earlier one, nor the shared file of 1–3.
     *  (4.1 warm paper, 4.2 night and 4.3 colour from the photo were shown on
     *  2026-10-16 and dropped: the client chose 4.4.)
     *  Keys "1".."4" are ints to PHP; "4.4" etc. stay strings. */
    public const VARIANTS = [
        '4.6' => ['Pares · La fila', 'A row of many',
                  'Pares as a carousel row, in the brand\'s colours: every review a white print and a navy card (with a whisper of its photo\'s hue), several in view at once (1.3 on a phone, 2.3 on a tablet, 3.5 on a laptop, 4.3 on a large screen), the next one always showing, all of them newest first. A fixed structure: the arrows by the heading, every pair one size, every quote five lines, the whole review and photo in an overlay. Tap the card behind to bring it forward.', 'Pares'],
        '4.5' => ['Pares · Editorial', 'Editorial, in the site\'s voice',
                  'Iteration of 4.4. One pair at a time on a fixed stage: the photo as a white print, the words on a navy card under it; tap the card behind and it comes forward. The brandbook\'s navy falling into its deep, a brand-blue light behind the pair, each photo only tinting it. DM Sans and 2.4\'s details. Heading, arrows and stage never move.', 'Pares'],
        '4.4' => ['Pares · Editorial', 'Editorial',
                  'A magazine page: the first sentence of each review as a large serif pull-quote (Fraunces) that wraps around the photo, the whole review on tap. Thin rules, brand ink and one red accent.', 'Pares'],
        4     => ['Pares', 'Pares 4.0',
                  "The client's own sketch as first built: photo card in front, quote card behind on the diagonal; tap the quote to bring it forward, tap the photo to open it.", 'Pares'],
        1     => ['La entrega', 'La entrega',
                  'One customer per screen, full screen: the whole photo over its own light, and the words. Moves with the scroll.', 'La entrega'],
        2     => ['El muro', 'El muro',
                  'Every delivery together in one wall of whole photos. Any of them comes to the front with its review.', 'El muro'],
        3     => ['Sus palabras', 'Sus palabras',
                  'The strongest phrase of each review, huge, line by line. The photo comes along; the whole review is a tap away.', 'Sus palabras'],
    ];

    /** Pinned scroll sequences: shown in a screen-tall frame that scrolls inside. */
    public const PINNED = [1, 3];

    /** 1 · La entrega: the sequence, in this order. Chosen for the words (a
     *  story each, an opening that stands alone) and for the photograph (people
     *  and car both in it, light enough to carry a dark stage). A review that
     *  is deleted simply drops out. */
    public const ENTREGA = [22, 21, 7, 26, 15, 24, 18, 8];

    /** 3 · Sus palabras: review id => its strongest phrase. Each MUST be a
     *  verbatim substring of the review (checked in phrases(); a phrase that is
     *  no longer in the text — the owner edited it — drops out, it is never
     *  shown altered). */
    public const PHRASES = [
        21 => 'parecía recién salido de fábrica',
        7  => 'el coche estaba mejor en persona que en las fotos',
        15 => 'este coche está como nuevo',
        26 => 'Incluso vino a recogernos al aeropuerto',
        25 => 'nuestra intención es conservar este coche otros 20 años',
        22 => 'hemos cruzado España para ir de vacaciones sin ningún problema',
        28 => 'va finísimo en carretera',
        20 => 'una compra de 10 en todos los sentidos',
    ];

    /** For review only (2026-10-16: the people judging the new versions do not
     *  read Spanish). Versions 4.4 and later show these English renderings of
     *  the reviews unless ?lang=es is asked for; the version bar switches. The
     *  live site and the frozen versions (1–4) never use them. A review that has
     *  no rendering here (a new one) is shown as written. Faithful, not
     *  polished: the customer's tone and slips are kept where they carry meaning. */
    public const EN = [
        5  => ['We are delighted with the car!'],
        6  => ['I found the car I wanted in unbeatable condition; my friend, who is a mechanic at Mercedes, tested it and it passed first time.'],
        7  => ['I fell in love with the car and travelled from Valencia. Julián gave me confidence and peace of mind from the very first minute we spoke. The car was better in person than in the photos; I drove it home and it runs beautifully. You have a friend in Valencia! Thank you.'],
        8  => ['I am a mechanical engineer. I travelled from Valladolid, tried the car and it did not disappoint, a one-of-a-kind car on the market. Worth mentioning: a small problem that came up afterwards was solved seriously and quickly.'],
        9  => ['Friendly treatment, excellent service before and after the sale, they delivered everything they promised.'],
        10 => ['We replaced our Peugeot 107 after 20 years. We found this car, which suited us in every way. We had been to other dealers before and they did not inspire confidence; IV Motorclass surprised us with the treatment, the presentation of the car and how quickly the purchase went. We took it home the same day and we are very happy.'],
        11 => ['We bought the car for our daughter. We liked its spotless condition, the clear history and a car with nothing wrong.'],
        12 => ['We have bought 3 cars in total from this company! They have treated us very well, offering spotless cars. We are delighted and have ordered another one for some relatives.'],
        13 => ['Online sale, direct conversation with very clear videos showing the car, and they delivered it to my front door. We have made several trips with it and everything is great.'],
        14 => ['We saw it online. Julián looked after us and explained everything in detail; they brought it to our home and everything was exactly as described. Very good!'],
        15 => ['My brother recommended it. When we saw it in person we thought "this car is like new". I took it home the same day and I am very happy. Thank you for the trust and the speed.'],
        16 => ['My first car in Spain. Julián explained every step to me and made everything very easy, and he helps me whenever I have a question. Recommended.'],
        17 => ['He sent me every detail about the car before I travelled. Once there, we could see it and test it without any rush. Everything was very easy and the car is superb. That is how it should be.'],
        18 => ['We bought the car as a Three Kings gift for our daughter. From the start, Julián answered all our questions and was clear when showing us the car, and after sorting out a few details, the paperwork was done in no time. Highly recommended.'],
        19 => ['I needed a car for driving long distances and I found the best one, in its exterior, its interior and its engine. Iulian explained everything clearly, and from today we are more than neighbours: I am taking away a good friend.'],
        20 => ['This weekend I bought a Mercedes C220 at IV Motorclass, and honestly a great purchase, the car in superb condition. Everything was handled by phone with Iulian, who gave me several trustworthy options; a great, sincere and reliable person. A sale with no tricks, honest from minute one, offering ways to buy without putting any obstacles in the way. A 10 out of 10 purchase in every sense. I am from Granada and he is from Santander; that says it all about how trustworthy they are.'],
        21 => ['I could not be happier with my new BMW 2 Series and with the experience at IV MotorClass! From minute one, Julián gave me incredible confidence and a warmth that is very hard to find these days; I felt in the best hands and superbly advised at every moment.',
               'But what made me fall in love was the handover: the car was absolutely immaculate, gleaming and cared for down to the smallest detail, it looked brand new from the factory! A thousand thanks, Julián, for your patience, for being so human and for making this process so special and simple. I will recommend you to everyone with my eyes closed!'],
        22 => ['I found a one-of-a-kind car in excellent condition. We took it home the same day and we have crossed Spain on holiday without any problem. Both the service and the car, absolutely amazing.'],
        23 => ['As a customer, what has given me the most confidence is knowing there is a company behind it that truly answers for what it sells: if any problem comes up, they deal with you personally, they face it and they look for a solution instead of leaving you stranded after the sale.'],
        24 => ['Exactly the model I was looking for. They even carried out a service that was not yet due, to make sure the car was perfect and to avoid problems in the future. Few dealers do that: caring this much about the customer even before handing over the car. A 10 out of 10 for attention and service.'],
        25 => ['After 20 years with the same car, we finally needed to change it. We had several options in mind, but as soon as we saw this car and met Iulian, we were sure.',
               'His knowledge, his attention to detail and the confidence he puts into choosing every car show perfectly in the cars he sells: they look brand new from the factory.',
               'We told him we intend to keep this car another 20 years, and after the experience we have had, we could not be happier with our choice. Without a doubt, 100% recommended.'],
        26 => ['We can only say thank you for how well everything went. We bought a BMW and the experience was a 10 out of 10 from start to finish.',
               'We want to give a special mention to Julian, who treated us wonderfully from the very first moment. He was very kind, attentive and always ready to help. He even came to pick us up at the airport and kindly invited us for a few beers, which made everything feel even more personal.',
               'You can tell he knows cars very well. He explained everything patiently, answered all our questions and gave us a lot of confidence. The car was immaculate, really clean and in even better condition than we expected.',
               'It is a pleasure to find companies that work like this, and professionals like him, who make the whole process easy and pleasant. Without a doubt we would trust them again, and we recommend them with complete peace of mind. A true 5-star experience.'],
        27 => ['Excellent experience. The car was exactly as advertised, in very good condition and with no surprises. Warm, professional and transparent treatment. Highly recommended; I would buy again without hesitation.'],
        28 => ['Good afternoon, Iulian.',
               'I wanted to thank you for the whole process of selling and delivering the Audi A1. The car is in excellent mechanical and cosmetic condition, exactly as you assured me from the start, and it runs beautifully on the road.',
               'I also want to highlight the paperwork: total transparency with the Audi digital service history, impeccable documents and a very fast agency, which issued the temporary DGT permit on the spot. The whole process conveyed great professionalism and peace of mind.',
               'It has been a real pleasure buying the car from you. I will keep your contact for future purchases and I will recommend IV MotorClass without hesitation; in fact, I already have, to a colleague at work.',
               'I wish you every success.'],
        29 => ['Very quick handling, and honestly great.'],
        30 => ['Great treatment, I would do it all again, everything perfect and trustworthy!'],
        31 => ['A very good experience buying the car. As it was a purchase from a distance I had quite a few doubts, but from the first moment Iulian gave me all the information and documents I needed to check that everything was in order.',
               'He gave me the service history, had no problem with a mechanic inspecting the car before the purchase, and gave me legal documents to verify the car properly. At every step he made it easy to take the decision calmly.',
               'He also kindly picked me up at the airport and explained the car\'s features, along with some tips on using it.',
               'The car is in very good condition and runs perfectly. I am very happy with both the purchase and the treatment I received. Without a doubt, a highly recommended experience.'],
    ];

    public function index(): Response
    {
        return response()
            ->view('muestras.opiniones', [
                'variants' => self::VARIANTS,
                'pinned'   => self::PINNED,
                'count'    => $this->reviews()->count(),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function show(string $v)
    {
        if (preg_match('/^(\d+)\.0$/', $v, $m)) {
            return redirect('/muestras/opiniones/' . $m[1]);
        }
        $view = 'muestras.v' . str_replace('.', '-', $v);
        abort_unless(isset(self::VARIANTS[$v]) && view()->exists($view), 404);
        // "4" is the int 4 (as the frozen views were built with); "4.4" stays a string
        $n = ctype_digit($v) ? (int) $v : $v;
        $reviews = $this->reviews();
        $embed   = (int) request()->query('embed', 0);
        // 4.4 and later are shown in English for review (EN), unless ?lang=es
        $en = is_string($n) && request()->query('lang') !== 'es';
        if ($en) {
            $reviews = $this->english($reviews);
        }

        $data = [
            'n'       => $n,
            // inside the showroom: no repeated heading. 1 = the frame follows
            // the content's height; 2 = a screen-tall frame that scrolls itself
            'embed'   => $embed > 0,
            'framed'  => $embed === 2,
            'variant' => self::VARIANTS[$n],
            'reviews' => $reviews,
            'json'    => $this->json($reviews),
            'en'      => $en,
        ];
        if ($n === 1) {
            $data['beats'] = $this->pick($reviews, self::ENTREGA);
        }
        if ($n === 2) {
            // the wall's first review in front: the first of La entrega's
            // (a strong one), not whichever the owner happened to add first
            $data['start'] = optional($this->pick($reviews, self::ENTREGA)->first())->id;
        }
        if ($n === 3) {
            $data['beats'] = $this->phrases($reviews);
        }

        $html = view($view, $data)->render();
        // the review bar is added here, not in the page, so every version's page
        // stays exactly as it was designed. Not inside the showroom's frames.
        if ($embed === 0) {
            $bar = view('muestras._bar', ['current' => $n, 'variants' => self::VARIANTS,
                'lang' => is_string($n) ? ($en ? 'en' : 'es') : null])->render();
            $html = preg_replace('~</body>~i', $bar . '</body>', $html, 1);
        }

        return response($html)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** The reviews the home page shows, in the owner's order, ready to print. */
    private function reviews()
    {
        return Testimonial::where('is_active', true)
            ->whereNotNull('image_path')
            ->orderBy('order_index')->orderBy('id')
            ->get()
            ->filter(fn ($t) => mb_strlen(trim((string) $t->quote)) > 20)   // same floor as the home page
            ->values()
            ->map(function ($t) {
                $paras = self::paragraphs((string) $t->quote);
                $text  = implode(' ', $paras);
                $size  = Img::size($t->image_path) ?: [1200, 1600];
                $ratio = $size[1] > 0 ? $size[0] / $size[1] : 0.75;
                $name  = self::name((string) $t->author_name);

                return (object) [
                    'id'      => (int) $t->id,
                    'name'    => $name,
                    'caption' => $name . ', con su coche',
                    'paras'   => $paras,
                    'text'    => $text,
                    'len'     => mb_strlen($text),
                    'img'     => $t->image_path,
                    'w'       => (int) $size[0],
                    'h'       => (int) $size[1],
                    'ratio'   => round($ratio, 4),
                    // what a box may take; contain mats whatever lies outside
                    'box'     => round(max(0.45, min(2.2, $ratio)), 4),
                ];
            });
    }

    /** The same reviews with their English rendering (EN), for review only. */
    private function english($reviews)
    {
        return $reviews->map(function ($r) {
            $r = clone $r;
            if (isset(self::EN[$r->id])) {
                $r->paras = self::EN[$r->id];
                $r->text  = implode(' ', $r->paras);
                $r->len   = mb_strlen($r->text);
            }
            $r->caption = $r->name . ', with their car';
            return $r;
        });
    }

    /** The listed reviews that still exist, in the listed order. */
    private function pick($reviews, array $ids)
    {
        $by = $reviews->keyBy('id');
        return collect($ids)->filter(fn ($id) => $by->has($id))->map(fn ($id) => $by[$id])->values();
    }

    /** 3 · each listed review with its phrase, if the phrase is really in it. */
    private function phrases($reviews)
    {
        $by = $reviews->keyBy('id');
        $out = [];
        foreach (self::PHRASES as $id => $p) {
            if ($by->has($id) && mb_strpos($by[$id]->text, $p) !== false) {
                $r = clone $by[$id];
                $r->phrase = $p;
                $out[] = $r;
            }
        }
        return collect($out);
    }

    /** Print-tidy paragraphs. The database row is never touched. */
    public static function paragraphs(string $q): array
    {
        $q = trim(str_replace(["\r\n", "\r"], "\n", $q));
        // a review typed entirely inside quotation marks: the layout draws its own
        if (preg_match('/^["“«](.*)["”»]$/su', $q, $m) && !preg_match('/["“”«»]/u', $m[1])) {
            $q = trim($m[1]);
        }
        $out = [];
        foreach (preg_split('/\n\s*/u', $q) as $p) {
            $p = preg_replace('/[ \t\x{00A0}]+/u', ' ', trim($p));
            $p = preg_replace('/ +([,;:.!?])/u', '$1', $p);
            if ($p !== '') {
                $out[] = $p;
            }
        }
        return $out ?: [''];
    }

    public static function name(string $n): string
    {
        $n = trim(preg_replace('/\s+/u', ' ', $n));
        // "MIguel" → "Miguel": two capitals then lower case is a held shift key,
        // not an acronym (an acronym has no lower case after it)
        $n = preg_replace_callback('/\b(\p{Lu})(\p{Lu})(?=\p{Ll})/u', fn ($m) => $m[1] . mb_strtolower($m[2]), $n);
        // couples: a no-break space before "&", an ordinary one after
        return preg_replace('/\s*&\s*/u', "\u{00A0}& ", $n);
    }

    /**
     * The opening of a review, cut at a sentence end.
     *
     * Whole sentences are taken while they fit in $limit. Nothing is cut when
     * what is left would be a few words (under 60 characters): a "Leer más"
     * that reveals half a line is a broken promise. When even the first
     * sentence is far over the budget (some reviews are one sentence of 300
     * characters, strung on commas), it is cut at a word with "…".
     * Returns ['short' => string, 'cut' => bool].
     */
    public static function sentences(string $text, int $limit): array
    {
        $text = trim($text);
        $len  = mb_strlen($text);
        if ($len <= $limit + 60) {
            return ['short' => $text, 'cut' => false];
        }
        $parts = preg_split('/(?<=[.!?…])\s+(?=\S)/u', $text);
        $short = '';
        foreach ($parts as $p) {
            $next = $short === '' ? $p : $short . ' ' . $p;
            if (mb_strlen($next) > $limit) {
                // the first sentence alone may run a little over the budget
                if ($short === '' && mb_strlen($p) <= $limit * 1.4) {
                    $short = $p;
                }
                break;
            }
            $short = $next;
        }
        // a first sentence of a few words ("Justo el modelo que estaba
        // buscando.") is an opening, not a quotation: take the next one too
        $taken = $short === '' ? 0 : count(preg_split('/(?<=[.!?…])\s+(?=\S)/u', $short));
        if ($short !== '' && mb_strlen($short) < $limit * .4 && isset($parts[$taken])
            && mb_strlen($short . ' ' . $parts[$taken]) <= $limit * 1.4) {
            $short .= ' ' . $parts[$taken];
        }
        if ($short !== '' && $len - mb_strlen($short) >= 60) {
            return ['short' => $short, 'cut' => true];
        }
        // one long sentence: at a word, never inside one
        $head = mb_substr($text, 0, $limit + 1);
        $sp = mb_strrpos($head, ' ');
        $short = mb_substr($text, 0, $sp === false ? $limit : $sp);
        $short = preg_replace('/[\s,;:.\-–—(«"“]+$/u', '', $short);

        return ['short' => $short . '…', 'cut' => true];
    }

    /**
     * 1 · La entrega's backdrop: the same photograph, blurred far past
     * recognition, pre-rendered once as a ~2 KB file, so the stage never runs
     * a CSS blur on a full-screen layer while it crossfades (that is a filter
     * recomputed per frame on a phone's GPU; a bitmap is not). Made from the
     * 320px derivative (already turned upright), shrunk to 28px, enlarged to
     * 224 and blurred ten passes: smooth at any size it is drawn. Cached
     * under public/img/muestras/amb/, keyed by the photo's path and mtime, so
     * a replaced photograph gets a new backdrop and nobody runs anything.
     */
    public static function ambient(string $img): ?string
    {
        $fs = public_path(ltrim($img, '/'));
        $mt = (int) @filemtime($fs);
        $name = 'img/muestras/amb/' . substr(md5($img . '|' . $mt), 0, 16) . '.webp';
        if (is_file(public_path($name))) {
            return '/' . $name;
        }
        $small = Img::url($img, 320);
        if (!$small || !str_starts_with($small, '/storage/cache/')) {
            return null;
        }
        try {
            $im = @imagecreatefromwebp(storage_path('app/public/cache/' . basename($small)));
            if (!$im) {
                return null;
            }
            $w = imagesx($im); $h = imagesy($im);
            $tw = 28; $th = max(1, (int) round($h * $tw / max(1, $w)));
            $t = imagecreatetruecolor($tw, $th);
            imagecopyresampled($t, $im, 0, 0, 0, 0, $tw, $th, $w, $h);
            $big = imagescale($t, $tw * 8, $th * 8, IMG_BICUBIC);
            for ($k = 0; $k < 10; $k++) {
                imagefilter($big, IMG_FILTER_GAUSSIAN_BLUR);
            }
            @mkdir(dirname(public_path($name)), 0775, true);
            $ok = imagewebp($big, public_path($name), 70);
            imagedestroy($im); imagedestroy($t); imagedestroy($big);
            return $ok ? '/' . $name : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 4.4 · Editorial: the pull-quote — the review's first sentence, whole.
     * A first sentence that runs on (some reviews are one sentence of 300
     * characters strung on commas) is cut at a word with "…" near $limit; the
     * whole review is a tap away. ['short' => string, 'cut' => bool]
     */
    public static function pull(string $text, int $limit = 150): array
    {
        $text = trim($text);
        $parts = preg_split('/(?<=[.!?…])\s+(?=\S)/u', $text);
        $first = $parts[0] ?? $text;
        if (mb_strlen($first) > $limit * 1.25) {
            $head = mb_substr($first, 0, $limit + 1);
            $sp = mb_strrpos($head, ' ');
            $first = mb_substr($first, 0, $sp === false ? $limit : $sp);
            $first = preg_replace('/[\s,;:.\-–—(«"“]+$/u', '', $first) . '…';
        }
        return ['short' => $first, 'cut' => mb_strlen($first) < mb_strlen($text)];
    }

    /**
     * 4.5 · each review's own colour, and how its photograph sits in the
     * 3:4 frame. Read once from the 320px derivative (already upright) and
     * cached as JSON per photo path + mtime (storage/app/muestras-looks), so a
     * replaced photograph is measured again and nothing else ever is.
     *
     * Colour (the brandbook's palette, nothing else): the grounds are the
     * tokens' navy (--mc-navy) and deep (--mc-deep); the one accent is the
     * tokens' blue (--mc-blue for the light, --mc-blue-light for the words on
     * navy). The photograph only tints them: the hue its colourful parts lean
     * to (OKLab, chroma-weighted mean of a/b), snapped to the nearest of seven
     * hues, mixed in at 10 % (the field, the card) and 15 % (the light). A
     * picture with almost no colour leaves the brand's own colours as they are.
     *   field  the top of the section's ground (it falls into --mc-deep)
     *   card   the quote card: white on it is ≥ 4.5:1 (measured ~13:1)
     *   glow   the light behind the pair
     *   tint   the quotation mark and the name: --mc-blue-light, ≥ 4.5:1 on the card
     * Fit, against the 3:4 frame (FRAME):
     *   cover    when filling the frame crops 13 % or less of one side; the
     *            crop window is placed where the picture is busiest (edges and
     *            skin: the people, the car), as object-position
     *   contain  otherwise: the whole picture, edge to edge on its long side,
     *            over a blurred copy of itself (ambient()) tinted with the field
     */
    public const FRAME = 0.75;

    /** the seven hues a photograph's tint is snapped to (only ever mixed in at 10-15 %) */
    private const TONES = [15 => 'red', 42 => 'copper', 155 => 'green', 205 => 'petrol', 255 => 'navy', 285 => 'indigo', 325 => 'aubergine'];

    public static function look(string $img, float $ratio): array
    {
        $fs = public_path(ltrim($img, '/'));
        $mt = (int) @filemtime($fs);
        $file = storage_path('app/muestras-looks/' . substr(md5($img . '|' . $mt . '|' . $ratio . '|brand6'), 0, 16) . '.json');
        if (is_file($file) && ($t = json_decode((string) file_get_contents($file), true))) {
            return $t;
        }
        $h = 255.0; $c = 0.0; $fx = 50.0; $fy = 50.0; $trim = [0.0, 0.0, 0.0, 0.0];   // top right bottom left
        $small = Img::url($img, 320);
        $im = $small && str_starts_with($small, '/storage/cache/') ? @imagecreatefromwebp(storage_path('app/public/cache/' . basename($small))) : false;
        if ($im) {
            // black bars baked into the file (a phone's screenshot, a letterboxed export):
            // rows or columns at an edge that are near-black and flat are not the picture
            $W = imagesx($im); $Hh = imagesy($im);
            // a bar row: 70 % or more of it true black (a phone's home bar may sit in it)
            $flat = function (bool $row, int $k) use ($im, $W, $Hh) {
                $n = $row ? $W : $Hh; $dark = 0; $m = 0;
                for ($i = 0; $i < $n; $i += 2) {
                    $rgb = $row ? imagecolorat($im, $i, $k) : imagecolorat($im, $k, $i);
                    $l = 0.3 * (($rgb >> 16) & 255) + 0.59 * (($rgb >> 8) & 255) + 0.11 * ($rgb & 255);
                    $dark += $l < 12 ? 1 : 0; $m++;
                }
                return $dark / $m >= .55;
            };
            // and it ends at an edge: a letterbox stops abruptly, a night sky fades
            $mean = function (bool $row, int $k) use ($im, $W, $Hh) {
                $n = $row ? $W : $Hh; $sum = 0; $m = 0;
                for ($i = 0; $i < $n; $i += 2) { $rgb = $row ? imagecolorat($im, $i, $k) : imagecolorat($im, $k, $i);
                    $sum += 0.3 * (($rgb >> 16) & 255) + 0.59 * (($rgb >> 8) & 255) + 0.11 * ($rgb & 255); $m++; }
                return $sum / $m;
            };
            // from an edge inwards: up to 3 % of rows that are not bar may come first
            // (a phone's home bar lies on the very edge), then the bar itself
            $scan = function (bool $row, bool $fromEnd) use ($flat, $W, $Hh) {
                $n = $row ? $Hh : $W; $at = fn ($k) => $fromEnd ? $n - 1 - $k : $k;
                $k = 0; while ($k < $n * .03 && !$flat($row, $at($k))) $k++;
                if ($k >= $n * .03) return 0;
                while ($k < $n * .3 && $flat($row, $at($k))) $k++;
                return $k;
            };
            $t0 = $scan(true, false); $b0 = $scan(true, true); $l0 = $scan(false, false); $r0 = $scan(false, true);
            // only a real bar (2 % or more) counts; one more row each side for the soft edge
            $f = fn ($px, $n) => $px >= $n * .02 ? round(min(.3, ($px + 1) / $n), 4) : 0.0;
            $sharp = function (bool $row, int $in, int $dir) use ($mean, $W, $Hh) {   // 2-4 rows past the bar vs the bar
                $n = $row ? $Hh : $W; $a = $mean($row, max(0, min($n - 1, $in - $dir * 2)));
                $b = ($mean($row, max(0, min($n - 1, $in + $dir * 2))) + $mean($row, max(0, min($n - 1, $in + $dir * 4)))) / 2;
                return $b - $a > 25;
            };
            if ($t0 && !$sharp(true, $t0, 1)) $t0 = 0;
            if ($b0 && !$sharp(true, $Hh - 1 - $b0, -1)) $b0 = 0;
            if ($l0 && !$sharp(false, $l0, 1)) $l0 = 0;
            if ($r0 && !$sharp(false, $W - 1 - $r0, -1)) $r0 = 0;
            $trim = [$f($t0, $Hh), $f($r0, $W), $f($b0, $Hh), $f($l0, $W)];
            $ratio = $ratio * (1 - $trim[1] - $trim[3]) / max(.1, 1 - $trim[0] - $trim[2]);
        }
        $crop = $ratio < self::FRAME ? 1 - $ratio / self::FRAME : 1 - self::FRAME / $ratio;
        $fit = $crop <= 0.13 && !array_filter($trim) ? 'cover' : 'contain';
        if ($im) {
            $w = imagesx($im); $hh = imagesy($im);
            $tw = 64; $th = max(2, (int) round($hh * $tw / max(1, $w)));
            $t = imagecreatetruecolor($tw, $th);
            imagecopyresampled($t, $im, 0, 0, 0, 0, $tw, $th, $w, $hh);
            $sa = $sb = $sw = 0.0; $px = [];
            for ($y = 0; $y < $th; $y++) {
                for ($x = 0; $x < $tw; $x++) {
                    $rgb = imagecolorat($t, $x, $y);
                    $r = ($rgb >> 16) & 255; $g = ($rgb >> 8) & 255; $b = $rgb & 255;
                    $px[$y][$x] = [$r, $g, $b];
                    [, $A, $B] = self::oklab($r, $g, $b);
                    $k = sqrt($A * $A + $B * $B);
                    $sa += $k * $A; $sb += $k * $B; $sw += $k;
                }
            }
            imagedestroy($im); imagedestroy($t);
            if ($sw > 0) {
                $A = $sa / $sw; $B = $sb / $sw;
                $c = sqrt($A * $A + $B * $B);
                if ($c >= 0.008) {
                    $h = fmod(rad2deg(atan2($B, $A)) + 360, 360);
                }
            }
            // where the picture is busiest: edges, and skin counted three times
            if ($fit === 'cover' && $crop > 0.001) {
                $rows = array_fill(0, $th, 0.0); $cols = array_fill(0, $tw, 0.0);
                for ($y = 1; $y < $th - 1; $y++) {
                    for ($x = 1; $x < $tw - 1; $x++) {
                        [$r, $g, $b] = $px[$y][$x];
                        $l = fn ($q) => 0.3 * $q[0] + 0.59 * $q[1] + 0.11 * $q[2];
                        $e = abs($l($px[$y][$x + 1]) - $l($px[$y][$x - 1])) + abs($l($px[$y + 1][$x]) - $l($px[$y - 1][$x]));
                        $skin = $r > 95 && $g > 40 && $b > 20 && max($r, $g, $b) - min($r, $g, $b) > 15 && abs($r - $g) > 15 && $r > $g && $r > $b;
                        $v = $e + ($skin ? 3 * 40 : 0);
                        $rows[$y] += $v; $cols[$x] += $v;
                    }
                }
                $best = function (array $line, float $keep) {
                    $n = count($line); $win = max(1, (int) round($n * $keep)); $span = $n - $win;
                    if ($span <= 0) return 50.0;
                    $sum = array_sum(array_slice($line, 0, $win)); $bestS = $sum; $bestO = 0;
                    for ($o = 1; $o <= $span; $o++) { $sum += $line[$o + $win - 1] - $line[$o - 1]; if ($sum > $bestS) { $bestS = $sum; $bestO = $o; } }
                    return round($bestO / $span * 100, 1);
                };
                if ($ratio < self::FRAME) { $fy = $best($rows, 1 - $crop); } else { $fx = $best($cols, 1 - $crop); }
            }
        }
        // the nearest of the seven tones
        $key = 255;
        if ($c >= 0.008) {
            $d = 999;
            foreach (array_keys(self::TONES) as $k) {
                $dd = abs(fmod($h - $k + 540, 360) - 180);
                if ($dd < $d) { $d = $dd; $key = $k; }
            }
        }
        $src = $c >= 0.008 ? self::oklch(0.55, 0.12, $key) : null;
        $field = $src ? self::mix('#0E2E57', $src, 0.10) : '#0E2E57';     // --mc-navy
        $card  = $src ? self::mix('#0E2E57', $src, 0.08) : '#0E2E57';
        $glow  = $src ? self::mix('#1558D6', $src, 0.15) : '#1558D6';     // --mc-blue
        $tint  = '#6CAAE7';                                               // --mc-blue-light, on navy only
        $out = ['field' => $field, 'card' => $card, 'glow' => $glow, 'tint' => $tint, 'tone' => $key, 'hue' => round($h), 'chroma' => round($c, 4),
                'crWhite' => round(self::contrast('#FFFFFF', $card), 2), 'crTint' => round(self::contrast($tint, $card), 2),
                'fit' => $fit, 'crop' => round($crop, 3), 'fx' => $fx, 'fy' => $fy, 'trim' => $trim, 'ratio' => round($ratio, 4),
                'win' => self::window($ratio, $trim)];
        @mkdir(dirname($file), 0775, true);
        @file_put_contents($file, json_encode($out));
        return $out;
    }

    /**
     * contain: the window the picture's own area takes in the 3:4 frame (whole,
     * edge to edge on its long side, centred), and where the file sits in that
     * window so that baked-in bars fall outside it. Percentages, for the view.
     */
    private static function window(float $ratio, array $trim): array
    {
        [$t, $r, $b, $l] = $trim;
        $F = self::FRAME;
        $ww = $ratio < $F ? $ratio / $F * 100 : 100.0;
        $wh = $ratio < $F ? 100.0 : $F / $ratio * 100;
        // within 3 % of the frame: the window is the whole frame (never a sliver
        // of a band), and the picture covers it, centred
        if ($ww >= 97) { $ww = 100.0; }
        if ($wh >= 97) { $wh = 100.0; }
        $Ww = $ww / 100 * $F; $Wh = $wh / 100;
        $aW = max($Ww, $Wh * $ratio); $aH = $aW / $ratio;            // the picture's own area, covering the window
        $aw = $aW / $Ww * 100; $ah = $aH / $Wh * 100;
        $ax = (100 - $aw) / 2; $ay = (100 - $ah) / 2;
        $iw = $aw / max(.1, 1 - $l - $r); $ih = $ah / max(.1, 1 - $t - $b);   // the whole file, its bars outside the window
        return ['ww' => round($ww, 3), 'wh' => round($wh, 3), 'iw' => round($iw, 3), 'ih' => round($ih, 3),
                'il' => round($ax - $l * $iw, 3), 'it' => round($ay - $t * $ih, 3)];
    }

    /** #RRGGBB mixed: $t of $b into $a, in sRGB */
    private static function mix(string $a, string $b, float $t): string
    {
        $out = '#';
        foreach ([1, 3, 5] as $i) {
            $out .= sprintf('%02X', (int) round(hexdec(substr($a, $i, 2)) * (1 - $t) + hexdec(substr($b, $i, 2)) * $t));
        }
        return $out;
    }

    private static function oklab(int $r, int $g, int $b): array
    {
        $f = fn ($v) => ($v /= 255) <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        [$r, $g, $b] = [$f($r), $f($g), $f($b)];
        $l = (0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b) ** (1 / 3);
        $m = (0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b) ** (1 / 3);
        $s = (0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b) ** (1 / 3);
        return [0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s,
                1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s,
                0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s];
    }

    /** OKLCH to #RRGGBB; the chroma is reduced until the colour is inside sRGB. */
    private static function oklch(float $L, float $C, float $h): string
    {
        for (; $C >= 0; $C -= 0.002) {
            $a = $C * cos(deg2rad($h)); $b = $C * sin(deg2rad($h));
            $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
            $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
            $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
            $rgb = [ 4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
                    -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
                    -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s];
            if (min($rgb) >= -0.0005 && max($rgb) <= 1.0005) {
                return '#' . implode('', array_map(function ($v) {
                    $v = max(0, min(1, $v));
                    $v = $v <= 0.0031308 ? 12.92 * $v : 1.055 * $v ** (1 / 2.4) - 0.055;
                    return sprintf('%02X', (int) round($v * 255));
                }, $rgb));
            }
        }
        return '#111C2E';
    }

    /** WCAG contrast of two #RRGGBB colours. */
    public static function contrast(string $x, string $y): float
    {
        $lum = function ($hex) {
            $f = fn ($v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
            return 0.2126 * $f(hexdec(substr($hex, 1, 2))) + 0.7152 * $f(hexdec(substr($hex, 3, 2))) + 0.0722 * $f(hexdec(substr($hex, 5, 2)));
        };
        $a = $lum($x); $b = $lum($y);
        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    /** What the page script needs to draw a review in a viewer. */
    private function json($reviews): string
    {
        $out = $reviews->map(fn ($r) => [
            'id'      => $r->id,
            'name'    => $r->name,
            'caption' => $r->caption,
            'paras'   => $r->paras,
            'w'       => $r->w,
            'h'       => $r->h,
            'src'     => Img::url($r->img, 1080) ?? $r->img,
            'srcset'  => Img::srcset($r->img, 1600),
            'lqip'    => Img::lqip($r->img),
        ])->values();

        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
