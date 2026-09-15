<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralRequest;
use App\Models\Referrer;
use App\Models\ReferralVisit;
use App\Support\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/** The public side of recommendations: the link, and /recomienda. */
class ReferralController extends Controller
{
    /**
     * /r/{code}: count the visit, remember the code, go to the home page.
     * An unknown code goes to the home page too — a mistyped link should still
     * land somewhere useful, and should not tell anyone which codes exist.
     */
    public function visit(Request $request, string $code)
    {
        $to = redirect()->route('inicio');
        $referrer = Referral::findByCode($code);

        if (! $referrer) {
            return $to;
        }

        ReferralVisit::firstOrCreate([
            'referrer_id' => $referrer->id,
            'visitor'     => Referral::visitorHash($request),
            'day'         => now()->toDateString(),
        ]);

        // The first link opened wins; a second one is counted but does not
        // take the visitor over.
        if (Referral::fromRequest($request)) {
            return $to;
        }

        // Not HttpOnly: the script that adds the code to WhatsApp links has to
        // read it. It holds a random code and nothing else.
        return $to->withCookie(cookie(
            Referral::COOKIE, $referrer->code, Referral::days() * 24 * 60,
            '/', null, $request->isSecure(), false, false, 'lax'
        ));
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

        // Kept in the session rather than put in the URL, so a link page cannot
        // be opened by guessing codes.
        session(['ref_code' => $referrer->code]);

        return redirect()->to(route('refer') . '#tu-enlace');
    }
}
