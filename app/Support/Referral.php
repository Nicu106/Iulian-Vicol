<?php

namespace App\Support;

use App\Models\ReferralReward;
use App\Models\Referrer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Recommendations without accounts — the rules, in one place.
 *
 *   1. A person gets a personal link, /r/{code}: made by the owner after a sale
 *      (admin), or asked for on /recomienda (web). Only that person's own name
 *      and phone are stored. The friend's data is never asked for: it would be
 *      a third party's data without consent, and contacting them would be
 *      unsolicited.
 *   2. Opening the link stores the code in a cookie for config('referral.days').
 *      The FIRST link opened wins: a second one does not overwrite it.
 *   3. While the cookie is there, every WhatsApp link on the site carries the
 *      code in its prefilled text (script in layouts/site), because buyers here
 *      write on WhatsApp rather than fill in forms.
 *   4. When the owner sells a car he sets "Vino de parte de" on it. A pending
 *      reward is then created — one per car, never for a person's own purchase.
 *   5. The owner approves and marks it paid. Nothing is paid for a click or a
 *      form: only for a car sold and delivered.
 */
final class Referral
{
    public const COOKIE = 'mc_ref';

    /** No 0/O or 1/I: the code is read aloud and typed from a phone screen. */
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function days(): int
    {
        return (int) config('referral.days', 90);
    }

    /** Digits only, with the country code: "+34 614 75 31 87" and "614753187" are one person. */
    public static function normalizePhone(?string $phone): string
    {
        $d = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (strlen($d) === 9) {
            $d = '34' . $d;   // a Spanish number written without the prefix
        }

        return $d;
    }

    /** The first name, then three random characters: MARIA7K2 is easy to say and hard to guess. */
    public static function makeCode(string $name): string
    {
        $stem = strtoupper(preg_replace('/[^A-Za-z]/', '', Str::ascii(Str::before(trim($name), ' '))));
        $stem = substr($stem !== '' ? $stem : 'IV', 0, 5);

        do {
            $suffix = '';
            for ($i = 0; $i < 3; $i++) {
                $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $code = $stem . $suffix;
        } while (Referrer::where('code', $code)->exists());

        return $code;
    }

    public static function findByCode(?string $code): ?Referrer
    {
        $c = strtoupper(trim((string) $code));
        if (! preg_match('/^[A-Z0-9]{4,16}$/', $c)) {
            return null;
        }

        return Referrer::where('code', $c)->first();
    }

    public static function fromRequest(Request $request): ?Referrer
    {
        return self::findByCode($request->cookie(self::COOKIE));
    }

    /**
     * Keep a car's reward in step with the car. Called by ReferralObserver on
     * every save that touches status, referred_by or the buyer's phone, so the
     * edit form, "Marcar vendido" and the bulk actions all obey the same rule.
     */
    public static function syncReward(Vehicle $vehicle): ?ReferralReward
    {
        $reward = ReferralReward::where('vehicle_id', $vehicle->id)->first();
        $eligible = $vehicle->status === 'sold' && $vehicle->referred_by;

        if ($eligible) {
            $referrer = Referrer::find($vehicle->referred_by);
            // Never for a person's own purchase: the link made for the buyer of
            // this car, or the same phone as the buyer.
            if (! $referrer
                || (int) $referrer->vehicle_id === (int) $vehicle->id
                || ($vehicle->buyer_phone && self::normalizePhone($vehicle->buyer_phone) === $referrer->phone)) {
                $eligible = false;
            }
        }

        if (! $eligible) {
            // A pending reward goes if the sale is undone. One already approved
            // or paid is money the owner has decided on, and is left alone.
            if ($reward && $reward->status === ReferralReward::PENDING) {
                $reward->delete();
            }

            return null;
        }

        if ($reward) {
            if ($reward->status === ReferralReward::PENDING && (int) $reward->referrer_id !== (int) $vehicle->referred_by) {
                $reward->update(['referrer_id' => $vehicle->referred_by]);
            }

            return $reward;
        }

        return ReferralReward::create([
            'referrer_id' => $vehicle->referred_by,
            'vehicle_id'  => $vehicle->id,
            'status'      => ReferralReward::PENDING,
        ]);
    }

    /** The message the owner sends a person with their link. */
    public static function messageFor(Referrer $referrer): string
    {
        $first = Str::before(trim($referrer->name), ' ');
        $reward = config('referral.reward');

        return "Hola {$first}, gracias por confiar en IV MOTORCLASS.\n\n"
             . "Si alguien que conoces busca coche, pásale este enlace:\n{$referrer->link}\n\n"
             . ($reward
                 ? "Si compra un coche, sabré que viene de tu parte. {$reward}."
                 : 'Si compra un coche, sabré que viene de tu parte.');
    }
}
