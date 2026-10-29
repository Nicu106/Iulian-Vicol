<?php

namespace App\Http\Controllers;

use App\Support\Schema;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * /terminos (who is behind the site and the conditions of use) and /privacidad
 * (personal data and cookies). The holder's identity comes from config/legal.php,
 * which reads .env; the contact details are the footer's (Schema::PHONE/EMAIL).
 * Written 2026-10-29 from a sourced brief of the Spanish and Andalusian rules
 * (LSSI, GDPR + LOPDGDD, AEPD cookie guides, TRLGDCU, Decreto 256/2003 and
 * Decreto 82/2022 of the Junta de Andalucía) — see the commit message.
 */
class LegalController extends Controller
{
    public function terms(): View
    {
        return view('pages.legal.terminos', self::data());
    }

    public function privacy(): View
    {
        return view('pages.legal.privacidad', self::data());
    }

    private static function data(): array
    {
        $l = config('legal');
        $holder = trim((string) ($l['holder'] ?? ''));

        return [
            'holder'   => $holder,
            // who "IV MOTORCLASS" is in a sentence: the holder's name when it is set
            'who'      => $holder !== '' ? $holder . ' (IV MOTORCLASS)' : 'IV MOTORCLASS',
            'nif'      => trim((string) ($l['nif'] ?? '')),
            'address'  => trim((string) ($l['address'] ?? '')),
            'registry' => trim((string) ($l['registry'] ?? '')),
            'updated'  => Carbon::parse($l['updated'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'phone'    => '+34 614 753 187',
            'tel'      => Schema::PHONE,
            'email'    => Schema::EMAIL,
        ];
    }
}
