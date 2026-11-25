<?php

namespace App\Http\Controllers;

use App\Models\SiteText;
use App\Models\SiteTextEdit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * /muestras/textos: every text of the site beside a box for its new wording,
 * behind the /muestras PIN (2026-11-25, the client: "să fie posibil de editat și
 * noi pe server să vedem ce s-a schimbat ... să nu pierdem aceste date ... cod de
 * 4 cifre"). The new wordings are applied to the site afterwards, by hand.
 *
 * Each box saves itself. A save writes the text's new wording AND a new row in
 * site_text_edits (never changed or deleted) AND a line in textos/cambios.jsonl
 * on the local disk (storage/app/private): three copies, so no later save, mistake or
 * broken database loses what the client wrote. php artisan texts:sync --changes
 * lists what changed.
 */
class SiteTextController extends Controller
{
    public const LOG = 'textos/cambios.jsonl';

    public function index()
    {
        $texts = SiteText::orderBy('position')->get();

        return response()->view('muestras.textos', [
            'sections' => $texts->groupBy('section'),
            'total'    => $texts->count(),
            'changed'  => $texts->filter->changed()->count(),
        ])->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store');
    }

    /** POST /muestras/textos/{text} — {value}: the new wording, '' to clear it */
    public function update(Request $request, SiteText $text): JsonResponse
    {
        $value = $request->validate(['value' => 'nullable|string|max:5000'])['value'] ?? '';
        $value = rtrim(str_replace("\r\n", "\n", $value));

        if ($value !== (string) $text->proposed) {
            DB::transaction(function () use ($text, $value, $request) {
                $text->update(['proposed' => $value]);
                SiteTextEdit::create(['site_text_id' => $text->id, 'value' => $value, 'ip' => $request->ip()]);
            });
            // appended in place, under a lock: two saves at once both get their line
            $log = Storage::disk('local')->path(self::LOG);
            if (!is_dir(dirname($log))) {
                @mkdir(dirname($log), 0775, true);
            }
            @file_put_contents($log, json_encode([
                'at' => now()->toIso8601String(), 'id' => $text->id, 'key' => $text->key,
                'section' => $text->section, 'current_text' => $text->current_text, 'value' => $value,
            ], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
        }

        return response()->json([
            'ok'      => true,
            'at'      => $text->updated_at?->timezone('Europe/Madrid')->format('H:i'),
            'changed' => SiteText::whereNotNull('proposed')->where('proposed', '!=', '')->count(),
        ]);
    }
}
