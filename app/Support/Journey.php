<?php

namespace App\Support;

use App\Models\ReferralEvent;
use App\Models\ReferralVisitor;
use App\Models\Referrer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * What a person did after opening a recommendation link.
 *
 * Scope, on purpose:
 *   - only people who arrived through a link are recorded; everyone else on the
 *     site is not
 *   - a person is a random id in the mc_rv cookie. No IP is stored, no name, no
 *     fingerprint. What is kept about the device is what the browser announces:
 *     mobile / tablet / desktop, the system, the browser
 *   - rows go 90 days after the person was last seen
 *
 * Link previews are not people. When a link is sent in WhatsApp, WhatsApp's own
 * servers fetch it to draw the preview, before anyone has pressed anything —
 * counted, every link sent would read as "opened". Those requests, and other
 * bots, are recognised by their user agent and neither counted nor given a
 * cookie.
 */
final class Journey
{
    public const COOKIE = 'mc_rv';

    /** Past this much silence, the next page starts a new visit. */
    private const VISIT_GAP = 30 * 60;

    /** A reload within this many seconds is the same page view, not a second one. */
    private const RELOAD = 10;

    public static function isBot(?string $ua): bool
    {
        $ua = (string) $ua;

        return $ua === '' || (bool) preg_match(
            '~bot|crawl|spider|slurp|preview|facebookexternalhit|facebot|whatsapp/|telegram|twitter|linkedin|discord|slack|skype|headless|curl|wget|python|go-http|okhttp|java/|axios|node-fetch|lighthouse~i',
            $ua
        );
    }

    /** @return array{device:string,os:string,browser:string,via:string} */
    public static function describe(?string $ua, ?string $referer): array
    {
        $ua = (string) $ua;
        $ref = (string) $referer;

        $device = preg_match('~iPad|Tablet|Android(?!.*Mobile)~i', $ua) ? 'tablet'
            : (preg_match('~Mobi|iPhone|iPod|Android~i', $ua) ? 'mobile' : 'desktop');

        $os = match (true) {
            (bool) preg_match('~iPhone|iPad|iPod~', $ua) => 'iOS',
            str_contains($ua, 'Android')                 => 'Android',
            str_contains($ua, 'Windows')                 => 'Windows',
            str_contains($ua, 'Mac OS X')                => 'macOS',
            str_contains($ua, 'Linux')                   => 'Linux',
            default                                      => 'otro',
        };

        // Order matters: Chrome's user agent also says Safari, Edge's says Chrome.
        $browser = match (true) {
            str_contains($ua, 'Instagram')                   => 'Instagram',
            (bool) preg_match('~FBAN|FBAV|FB_IAB~', $ua)     => 'Facebook',
            str_contains($ua, 'SamsungBrowser')              => 'Samsung',
            str_contains($ua, 'Edg/')                        => 'Edge',
            (bool) preg_match('~CriOS|Chrome/~', $ua)        => 'Chrome',
            (bool) preg_match('~FxiOS|Firefox/~', $ua)       => 'Firefox',
            str_contains($ua, 'Safari/')                     => 'Safari',
            default                                          => 'otro',
        };

        // Android passes "android-app://com.whatsapp/" when a link is opened from
        // WhatsApp; iOS passes nothing, so "directo" there usually means WhatsApp
        // or Messages too — it cannot be told apart.
        $via = match (true) {
            (bool) preg_match('~whatsapp~i', $ref)       => 'whatsapp',
            $browser === 'Instagram'                     => 'instagram',
            $browser === 'Facebook'                      => 'facebook',
            (bool) preg_match('~t\.me|telegram~i', $ref) => 'telegram',
            default                                      => 'directo',
        };

        return compact('device', 'os', 'browser', 'via');
    }

    public static function visitor(Request $request): ?ReferralVisitor
    {
        $uuid = (string) $request->cookie(self::COOKIE);
        if (! Str::isUuid($uuid)) {
            return null;
        }

        return ReferralVisitor::where('uuid', $uuid)->first();
    }

    /**
     * The person opening a link. Someone already known keeps their attribution;
     * someone new is attributed to the link they already carry in mc_ref (the
     * first one they opened), or else to this one.
     */
    public static function begin(Request $request, Referrer $clicked, ?Referrer $attributed): ReferralVisitor
    {
        if ($known = self::visitor($request)) {
            return $known;
        }

        return ReferralVisitor::create([
            'uuid'          => (string) Str::uuid(),
            'referrer_id'   => ($attributed ?? $clicked)->id,
            'visits'        => 1,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
        ] + self::describe($request->userAgent(), $request->headers->get('referer')));
    }

    public static function record(ReferralVisitor $visitor, string $type, array $data = []): void
    {
        $now = now();
        $path = isset($data['path']) ? mb_substr((string) $data['path'], 0, 255) : null;

        if (in_array($type, ['page', 'car'], true)
            && ReferralEvent::where('visitor_id', $visitor->id)->where('type', $type)->where('path', $path)
                ->where('created_at', '>=', $now->copy()->subSeconds(self::RELOAD))->exists()) {
            return;
        }

        ReferralEvent::create([
            'visitor_id'  => $visitor->id,
            'referrer_id' => $data['referrer_id'] ?? null,
            'type'        => $type,
            'path'        => $path,
            'vehicle_id'  => $data['vehicle_id'] ?? null,
            'created_at'  => $now,
        ]);

        $patch = ['last_seen_at' => $now];
        if ($visitor->last_seen_at && $visitor->last_seen_at->diffInSeconds($now, true) > self::VISIT_GAP) {
            $patch['visits'] = $visitor->visits + 1;
        }
        $visitor->forceFill($patch)->save();
    }

    /** Record for whoever is making this request, if they came through a link. */
    public static function track(Request $request, string $type, array $data = []): void
    {
        if (self::isBot($request->userAgent())) {
            return;
        }
        if ($visitor = self::visitor($request)) {
            self::record($visitor, $type, $data);
        }
    }

    /** Delete people not seen for config('referral.days'); their events go with them. */
    public static function prune(): int
    {
        return ReferralVisitor::where('last_seen_at', '<', now()->subDays(Referral::days()))->delete();
    }
}
