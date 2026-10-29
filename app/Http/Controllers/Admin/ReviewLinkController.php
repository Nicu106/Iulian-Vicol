<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewLink;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The links he sends so a customer writes their own opinion (App\Models\ReviewLink).
 * The dialog on /admin/testimonials ("Añadir opinión" → "Le mando un enlace")
 * creates one here and gets back what it shows: the address, the QR, until when
 * it is open and the WhatsApp message ready to send.
 */
class ReviewLinkController extends Controller
{
    /** POST /admin/testimonials/enlaces — {days, name?} */
    public function store(Request $request): JsonResponse
    {
        abort_unless(ReviewLink::ready(), 503);
        $data = $request->validate([
            'days' => 'required|integer|in:' . implode(',', array_keys(ReviewLink::PERIODS)),
            'name' => 'nullable|string|max:80',
        ]);

        return response()->json(self::payload(ReviewLink::open((int) $data['days'], $data['name'] ?? null)));
    }

    /** GET /admin/testimonials/enlaces/{link} — an open link again ("Ver el QR") */
    public function show(ReviewLink $link): JsonResponse
    {
        abort_unless($link->isOpen(), 410);

        return response()->json(self::payload($link));
    }

    /** POST /admin/testimonials/enlaces/{link}/cerrar — nobody can write through it any more */
    public function close(ReviewLink $link)
    {
        if ($link->isOpen()) {
            $link->update(['expires_at' => now()]);
        }

        return redirect()->route('admin.testimonials.index')->with('status', 'Enlace cerrado.');
    }

    private static function payload(ReviewLink $link): array
    {
        $url = $link->url();
        $hello = $link->name ? 'Hola ' . $link->name . ', gracias' : 'Hola, gracias';

        return [
            'id'    => $link->id,
            'url'   => $url,
            'name'  => $link->name,
            'until' => $link->until(),
            'qr'    => self::qr($url),
            'wa'    => 'https://wa.me/?text=' . rawurlencode($hello . ' por confiar en mí. Si te apetece, déjame tu opinión aquí: ' . $url),
        ];
    }

    /** The QR as inline SVG: dark modules only, no quiet zone (the dialog's white frame is it). */
    private static function qr(string $url): string
    {
        return (new QRCode(new QROptions([
            'outputType' => 'svg', 'outputBase64' => false, 'eccLevel' => EccLevel::M,
            'addQuietzone' => false, 'svgUseFillAttributes' => true, 'drawLightModules' => false,
            'connectPaths' => true, 'svgAddXmlHeader' => false,
        ])))->render($url);
    }
}
