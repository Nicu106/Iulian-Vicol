<?php

namespace App\Http\Controllers\Admin;

use App\Support\CarPhotos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * The car form's photographs, one at a time, and the heartbeat that keeps the
 * form's session alive.
 *
 * Why one at a time. The form used to send thirty phone photographs in ONE
 * multipart POST: 300-450 MB from a phone on 4G, minutes of upload in which a
 * lift, a tunnel or the screen locking cost all thirty and the text with them,
 * and if the session had expired meanwhile it ended on "419 Page Expired". Now
 * the phone shrinks each photo to ~2400 px (0.5-1 MB) and sends it here on its
 * own, with progress and retries; the form itself only carries the paths.
 *
 * Limits these requests run under (measured through the v2 FPM socket,
 * 2026-10-16): upload_max_filesize 0 and post_max_size 0 (unlimited),
 * max_file_uploads 1000, memory_limit 512M and max_execution_time 120 — the
 * last two are php_admin_value in the pool, so no ini_set() can raise them.
 * nginx: client_max_body_size 0, fastcgi_read_timeout 300 s. A request here
 * writes one file, converts a HEIC at most, and is done in well under a second;
 * the resized copies are built later by WarmVehicleImages on the queue.
 */
class VehiclePhotoController extends Controller
{
    /** POST /admin/fotos  {photo, token, tag?} → {path, w, h} */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'regex:/^[A-Za-z0-9]{32,64}$/'],
            'photo' => ['required', 'file', 'max:61440'],   // 60 MB: an original that could not be shrunk on the phone
            'tag'   => ['nullable', 'string'],
        ], [
            'photo.required' => 'No ha llegado ninguna foto.',
            'photo.file'     => 'La foto no ha llegado entera.',
            'photo.uploaded' => 'La foto no ha llegado entera.',
            'photo.max'      => 'Esta foto pasa de 60 MB. Hazla desde la cámara normal, no en RAW.',
            'token.*'        => 'Falta el identificador del borrador. Recarga la página.',
        ]);

        try {
            $saved = CarPhotos::stage($request->file('photo'), $request->input('token'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Unsaved uploads older than three days go now and then; no timer needed.
        if (random_int(1, 25) === 1) {
            try { CarPhotos::pruneDrafts(); } catch (\Throwable) { /* next time */ }
        }

        return response()->json($saved + ['tag' => CarPhotos::tag($request->input('tag'))], 201);
    }

    /** DELETE /admin/fotos  {token, path} — a photo removed before the car was saved. */
    public function destroy(Request $request): JsonResponse
    {
        $token = (string) $request->input('token');

        return response()->json(['removed' => CarPhotos::unstage($request->input('path'), $token)]);
    }

    /**
     * GET /admin/ping — touches the session so a form left open on the phone
     * does not expire under him, and hands back the current CSRF token: after a
     * sign-in in another tab, or a "remember me" re-login, the token the page was
     * printed with is no longer the session's. A guest gets 401 (JSON), which is
     * how the page knows to say so before he presses Guardar.
     */
    public function ping(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'csrf' => csrf_token()])
            ->header('Cache-Control', 'no-store');
    }
}
