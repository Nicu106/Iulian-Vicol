<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralRequest;
use App\Models\Referrer;
use App\Support\Journey;
use App\Support\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/** The public side of recommendations: the link, /recomienda, and the click beacon. */
class ReferralController extends Controller
{
    /**
     * /r/{code}: record who opened it, remember the code, go to the home page.
     * An unknown code goes to the home page too — a mistyped link should still
     * land somewhere useful, and should not tell anyone which codes exist.
     */
    public function visit(Request $request, string $code)
    {
        $to = redirect()->route('inicio');

        // WhatsApp's preview fetcher and other bots: not a person opening the
        // link. Not counted, and no cookie.
        if (Journey::isBot($request->userAgent())) {
            return $to;
        }

        $clicked = Referral::findByCode($code);
        if (! $clicked) {
            return $to;
        }

        $attributed = Referral::fromRequest($request);   // the first link opened wins
        $visitor = Journey::begin($request, $clicked, $attributed);
        Journey::record($visitor, 'open', ['referrer_id' => $clicked->id]);

        // No scheduler runs on this box, so old rows are cleared here, now and
        // then, like session garbage collection.
        if (random_int(1, 50) === 1) {
            Journey::prune();
        }

        $minutes = Referral::days() * 24 * 60;
        $to->withCookie(cookie(Journey::COOKIE, $visitor->uuid, $minutes, '/', null, $request->isSecure(), true, false, 'lax'));

        // Not HttpOnly: the script that adds the code to WhatsApp links reads it.
        // It holds a random code and nothing else.
        if (! $attributed) {
            $to->withCookie(cookie(Referral::COOKIE, $clicked->code, $minutes, '/', null, $request->isSecure(), false, false, 'lax'));
        }

        return $to;
    }

    /** POST /r/e — what the page script reports as a WhatsApp or e-mail press leaves the site. */
    public function event(Request $request)
    {
        $type = $request->input('t');
        if (in_array($type, ['whatsapp', 'email'], true)) {
            Journey::track($request, $type, ['path' => '/' . ltrim((string) $request->input('p'), '/')]);
        }

        return response()->noContent();
    }

    public function show()
    {
        return view('pages.recomienda', [
            'stamp'  => Crypt::encryptString((string) time()),
            'mine'   => Referral::findByCode(session('ref_code')),
            'reward' => config('referral.reward'),
        ]);
    }

    /**
     * One link per phone. Asking again with the same number gives back the same
     * link rather than a second one that would split that person's count.
     */
    public function store(ReferralRequest $request)
    {
        $phone = Referral::normalizePhone($request->input('phone'));

        $referrer = Referrer::where('phone', $phone)->first() ?? Referrer::create([
            'code'   => Referral::makeCode((string) $request->input('name')),
            'name'   => trim((string) $request->input('name')),
            'phone'  => $phone,
            'source' => 'web',
        ]);

        // A referred visitor asking for their own link is worth knowing.
        Journey::track($request, 'form_refer', ['path' => '/recomienda']);

        // Kept in the session rather than put in the URL, so a link page cannot
        // be opened by guessing codes.
        session(['ref_code' => $referrer->code]);

        return redirect()->to(route('refer') . '#tu-enlace');
    }
}
