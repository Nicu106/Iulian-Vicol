<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A car's photographs: which ones it has, which is the cover, and which group
 * each belongs to. One set of rules for the add form and the edit form.
 *
 * Where a photograph lives
 *   gallery_images  ["/storage/vehicles/{slug}/{name}", …]  in the order he set
 *                   in the form (2026-10-17: drag and drop): Exterior, Interior,
 *                   Desperfectos, then unsorted, each group in his order
 *   cover_image     one path; a gallery entry, or a separate file (every car
 *                   from the old site has one until its form is saved again)
 *   image_tags      {"<path>": "exterior" | "interior" | "flaw"}; a path with no
 *                   entry is "sin clasificar" and shows only under "Todas"
 *
 * How a photograph gets here (2026-10-16)
 *   With JavaScript, the phone shrinks each photo and sends it on its own to
 *   POST /admin/fotos, which keeps it under vehicles/_drafts/{token}/ and answers
 *   with its path. The form then carries only those paths ("staged[]"), so saving
 *   a car is a small POST however many photos it has, and a photo that failed is
 *   retried on its own instead of taking the other twenty-nine down with it. On
 *   save they are moved into vehicles/{slug}/. A car that does not exist yet
 *   works the same way — the token, not the car, owns the files until then.
 *   Without JavaScript the three file inputs post as multipart, as before.
 *
 * Nothing here trusts a path from the form: an existing photo can be re-tagged
 * or removed only if it is already on this car, and a staged one is adopted only
 * from under its own token.
 */
final class CarPhotos
{
    /** Order = the page's story and the order new photos are appended in. */
    public const TAGS = ['exterior', 'interior', 'flaw'];

    public const DRAFTS = 'vehicles/_drafts';

    /** How long an unsaved upload is kept before the sweep removes it. */
    public const DRAFT_DAYS = 3;

    private const EXT = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    public static function tag(mixed $t): ?string
    {
        return is_string($t) && in_array($t, self::TAGS, true) ? $t : null;
    }

    public static function validToken(mixed $t): bool
    {
        return is_string($t) && preg_match('/^[A-Za-z0-9]{32,64}$/', $t) === 1;
    }

    public static function newToken(): string
    {
        return Str::random(40);
    }

    /** "/storage/x" → "x" on the public disk, or null if it is not a vehicle file. */
    private static function rel(?string $url): ?string
    {
        if (!is_string($url) || !str_starts_with($url, '/storage/vehicles/') || str_contains($url, '..')) {
            return null;
        }

        return substr($url, strlen('/storage/'));
    }

    /**
     * Keep one uploaded photograph under the token's draft folder and return its
     * public path. HEIC/HEIF (an iPhone photo that reached us untranscoded) is
     * converted to JPEG with heif-convert; anything else that is not a JPEG, PNG,
     * WebP or GIF the browser can show is refused with a sentence, not a 500.
     *
     * @return array{path:string,w:int,h:int}
     * @throws \InvalidArgumentException with the message to show him
     */
    public static function stage(UploadedFile $file, string $token): array
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('La foto no ha llegado entera. Se vuelve a intentar.');
        }
        $src = $file->getRealPath();
        $mime = (string) $file->getMimeType();
        $name = strtolower((string) $file->getClientOriginalName());
        $tmp = null;

        // AVIF (some Android cameras) is the same container with AV1 inside;
        // heif-convert reads both (libde265 and aom are installed).
        if (in_array($mime, ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence', 'image/avif'], true)
            || preg_match('/\.(heic|heif|avif)$/', $name)) {
            $tmp = self::heicToJpeg($src);
            if ($tmp === null) {
                throw new \InvalidArgumentException('Esta foto está en HEIC y no se ha podido convertir. En el iPhone: Ajustes › Cámara › Formatos › «Más compatible».');
            }
            $src = $tmp;
            $mime = 'image/jpeg';
        }

        try {
            $info = @getimagesize($src);
            $ext = self::EXT[$info['mime'] ?? ''] ?? null;
            if (!$info || !$ext || $info[0] < 1 || $info[1] < 1) {
                throw new \InvalidArgumentException('Esto no es una foto que se pueda poner en la web (JPG, PNG, WebP o HEIC).');
            }

            $dir = self::DRAFTS . '/' . $token;
            $fileName = Str::random(40) . '.' . $ext;
            $stream = fopen($src, 'rb');
            $ok = Storage::disk('public')->put($dir . '/' . $fileName, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (!$ok) {
                throw new \RuntimeException('could not write ' . $dir . '/' . $fileName);
            }

            return ['path' => '/storage/' . $dir . '/' . $fileName, 'w' => (int) $info[0], 'h' => (int) $info[1]];
        } finally {
            if ($tmp) {
                @unlink($tmp);
            }
        }
    }

    /** heif-convert ships with libheif-examples (1.17 on this box). */
    private static function heicToJpeg(string $src): ?string
    {
        $bin = '/usr/bin/heif-convert';
        if (!is_executable($bin)) {
            return null;
        }
        $out = tempnam(sys_get_temp_dir(), 'heic') . '.jpg';
        try {
            $r = Process::timeout(90)->run([$bin, '--quiet', '-q', '88', $src, $out]);
        } catch (\Throwable) {
            return null;
        }
        // a file with several top-level images is written as out-1.jpg, out-2.jpg…
        if (!is_file($out)) {
            $alt = glob(substr($out, 0, -4) . '-*.jpg') ?: [];
            sort($alt);
            if ($alt) {
                rename($alt[0], $out);
                array_map('unlink', array_slice($alt, 1));
            }
        }

        return $r->successful() && is_file($out) && filesize($out) > 0 ? $out : null;
    }

    /** Remove one staged file, only from under its own token. */
    public static function unstage(?string $path, string $token): bool
    {
        $rel = self::stagedRel($path, $token);

        return $rel !== null && Storage::disk('public')->delete($rel);
    }

    private static function stagedRel(?string $path, ?string $token): ?string
    {
        if (!self::validToken($token) || !is_string($path)) {
            return null;
        }
        $prefix = '/storage/' . self::DRAFTS . '/' . $token . '/';
        if (!str_starts_with($path, $prefix)) {
            return null;
        }
        $name = substr($path, strlen($prefix));

        return preg_match('/^[A-Za-z0-9]{20,64}\.(jpe?g|png|webp|gif)$/', $name)
            ? self::DRAFTS . '/' . $token . '/' . $name
            : null;
    }

    /**
     * Move a staged photo into the car's folder. Idempotent: if a double press
     * already moved it, the moved file is answered again instead of a loss.
     */
    private static function adopt(?string $path, ?string $token, string $slug): ?string
    {
        $from = self::stagedRel($path, $token);
        if ($from === null) {
            return null;
        }
        $to = 'vehicles/' . $slug . '/' . basename($from);
        $disk = Storage::disk('public');
        if (!$disk->exists($from)) {
            return $disk->exists($to) ? '/storage/' . $to : null;
        }
        if (!$disk->move($from, $to)) {
            return null;
        }

        return '/storage/' . $to;
    }

    /**
     * Apply what the form says about the photographs to one car's three fields.
     *
     * Reads:  photos[i][path|tag|remove]   the photos already on the car
     *         removed_gallery_images       older form: JSON list to remove
     *         photo_token + staged[i][path|tag]   uploaded one by one
     *         photos_exterior[] / photos_interior[] / photos_flaw[] / gallery_images[]
     *                                      plain multipart, without JavaScript
     *         cover_image (file), cover_from (a path already on the car, or staged)
     *         photo_order                  JSON list of every photo in the form, in
     *                                      the order he left them (see order())
     *
     * @param  array  $car  gallery_images, cover_image, image_tags as they are now
     * @return array{gallery_images: array, cover_image: ?string, image_tags: ?array}
     */
    public static function apply(Request $r, string $slug, array $car): array
    {
        $gallery = array_values(array_filter((array) ($car['gallery_images'] ?? []), 'is_string'));
        $cover = is_string($car['cover_image'] ?? null) && $car['cover_image'] !== '' ? $car['cover_image'] : null;
        $tags = is_array($car['image_tags'] ?? null) ? $car['image_tags'] : [];
        $owned = fn ($p) => is_string($p) && ($p === $cover || in_array($p, $gallery, true));

        // 1. what is already on the car: its group, and whether it goes
        $remove = [];
        foreach ((array) $r->input('photos', []) as $row) {
            if (!is_array($row) || !$owned($row['path'] ?? null)) {
                continue;
            }
            $p = $row['path'];
            if (array_key_exists('tag', $row)) {
                if ($t = self::tag($row['tag'])) {
                    $tags[$p] = $t;
                } else {
                    unset($tags[$p]);
                }
            }
            if (!empty($row['remove'])) {
                $remove[] = $p;
            }
        }
        if ($r->filled('removed_gallery_images')) {
            foreach ((array) json_decode((string) $r->input('removed_gallery_images'), true) as $p) {
                if ($owned($p)) {
                    $remove[] = $p;
                }
            }
        }

        // 2. new photographs: the ones sent one by one, then plain multipart
        $new = [];
        $staged = [];   // staged path → where it ended up, for cover_from
        $token = $r->input('photo_token');
        foreach ((array) $r->input('staged', []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $moved = self::adopt($row['path'] ?? null, $token, $slug);
            if ($moved) {
                $new[] = [$moved, self::tag($row['tag'] ?? null)];
                $staged[$row['path']] = $moved;
            }
        }
        foreach (['photos_exterior' => 'exterior', 'photos_interior' => 'interior', 'photos_flaw' => 'flaw', 'gallery_images' => null] as $field => $t) {
            foreach ((array) ($r->file($field) ?? []) as $f) {
                if ($f instanceof UploadedFile && $f->isValid()) {
                    try {
                        $new[] = ['/storage/' . $f->store('vehicles/' . $slug, 'public'), $t];
                    } catch (\Throwable $e) {
                        \Log::error('Gallery image store failed: ' . $e->getMessage());
                    }
                }
            }
        }
        // Appended exterior → interior → flaws → unsorted, each in the order it
        // was chosen, so "Todas" on a new car reads in the page's own order.
        $rank = array_flip(self::TAGS);
        $keys = array_keys($new);
        usort($keys, fn ($a, $b) => [$rank[$new[$a][1]] ?? 9, $a] <=> [$rank[$new[$b][1]] ?? 9, $b]);
        foreach ($keys as $k) {
            [$p, $t] = $new[$k];
            $gallery[] = $p;
            if ($t) {
                $tags[$p] = $t;
            }
        }

        // 3. removals: the list, the tag and the file
        if ($remove) {
            $gallery = array_values(array_filter($gallery, fn ($p) => !in_array($p, $remove, true)));
            foreach (array_unique($remove) as $p) {
                unset($tags[$p]);
                if ($rel = self::rel($p)) {
                    try { Storage::disk('public')->delete($rel); } catch (\Throwable) { /* gone already */ }
                }
            }
            if (in_array($cover, $remove, true)) {
                $cover = null;
            }
        }
        $gallery = array_values(array_unique($gallery));

        // 4. the order he set by dragging. Only with JavaScript — without it the
        //    form has no way to say an order, and the gallery keeps the one it had.
        $order = self::orderOf($r->input('photo_order'));
        if ($order !== null) {
            $order = array_map(fn ($p) => $staged[$p] ?? $p, $order);
            // The cover the old site uploaded as a separate file is a photograph
            // like the others in the form, so it takes its place among them: a
            // position needs an entry in the list. It also means choosing another
            // cover no longer drops that file from the car without a word.
            if ($cover !== null && !in_array($cover, $gallery, true) && in_array($cover, $order, true)) {
                $gallery[] = $cover;
            }
            $gallery = self::order($gallery, $tags, $order);
        }

        // 5. the cover: an upload of its own, or one of the photos on the car
        $coverFile = $r->file('cover_image');
        if ($coverFile instanceof UploadedFile && $coverFile->isValid()) {
            try {
                $cover = '/storage/' . $coverFile->store('vehicles/' . $slug, 'public');
            } catch (\Throwable $e) {
                \Log::error('FAILED to store cover: ' . $e->getMessage());
            }
        } elseif ($r->filled('cover_from')) {
            $want = (string) $r->input('cover_from');
            $want = $staged[$want] ?? $want;
            if ($want === $cover || in_array($want, $gallery, true)) {
                $cover = $want;
            }
        }
        // None chosen (or it was removed): the first exterior photograph, not
        // simply the first one — on a car whose interior went up first that was
        // a dashboard in the catalogue.
        if ($cover === null && $gallery) {
            $cover = collect($gallery)->first(fn ($p) => ($tags[$p] ?? null) === 'exterior') ?? $gallery[0];
        }

        // 6. a tag only for a photograph that is still on the car
        $tags = array_filter($tags, fn ($t, $p) => self::tag($t) && ($p === $cover || in_array($p, $gallery, true)), ARRAY_FILTER_USE_BOTH);

        return ['gallery_images' => $gallery, 'cover_image' => $cover, 'image_tags' => $tags ?: null];
    }

    /**
     * Sort a car's gallery into the order the form sent: by group first
     * (Exterior, Interior, Desperfectos, then unsorted — the car page's story,
     * and what "Todas" reads), then by where he left each photo in its group.
     * A photo the list does not name keeps its place after the ones it does.
     *
     * Only paths already in $gallery come out: the list orders, it never adds.
     *
     * @param  list<string>  $gallery
     * @param  array<string,string>  $tags
     * @param  list<string>  $order
     * @return list<string>
     */
    public static function order(array $gallery, array $tags, array $order): array
    {
        $pos = [];
        foreach ($order as $i => $p) {
            $pos[$p] ??= $i;
        }
        $rank = array_flip(self::TAGS);
        $was = array_flip($gallery);
        usort($gallery, fn ($a, $b) => [$rank[$tags[$a] ?? ''] ?? 9, $pos[$a] ?? PHP_INT_MAX, $was[$a]]
                                   <=> [$rank[$tags[$b] ?? ''] ?? 9, $pos[$b] ?? PHP_INT_MAX, $was[$b]]);

        return $gallery;
    }

    /** photo_order as the form sends it (a JSON list) or as an array; null if absent, empty or not a list. */
    public static function orderOf(mixed $v): ?array
    {
        if (is_string($v)) {
            $v = trim($v) === '' ? null : json_decode($v, true);
        }
        if (!is_array($v) || !array_is_list($v)) {
            return null;
        }
        $v = array_values(array_filter(array_slice($v, 0, 2000), fn ($p) => is_string($p) && $p !== ''));

        return $v ?: null;
    }

    /**
     * The photos in the order the form shows them and the car page's group tabs
     * follow: the gallery's own order, with the cover first only while it is a
     * separate file outside the gallery (where else could it go). Each with its
     * group. The edit form groups these.
     *
     * Until 2026-10-17 the cover always came first, which was the old site's
     * order. Now that he can move it, it is wherever he left it.
     *
     * @return list<array{path:string,tag:?string,i:int}>
     */
    public static function listFor(array $car): array
    {
        $gallery = array_values(array_filter((array) ($car['gallery_images'] ?? []), fn ($p) => is_string($p) && $p !== ''));
        $cover = is_string($car['cover_image'] ?? null) && $car['cover_image'] !== '' ? $car['cover_image'] : null;
        $all = array_values(array_unique($cover !== null && !in_array($cover, $gallery, true) ? array_merge([$cover], $gallery) : $gallery));
        $tags = is_array($car['image_tags'] ?? null) ? $car['image_tags'] : [];

        return array_map(fn ($p, $i) => ['path' => $p, 'tag' => self::tag($tags[$p] ?? null), 'i' => $i], $all, array_keys($all));
    }

    /**
     * Uploads that never became part of a car: the form was abandoned, the phone
     * died. Removed after DRAFT_DAYS, judged by the newest file in the folder.
     * Called now and then from the upload endpoint; there is no separate timer.
     */
    public static function pruneDrafts(?int $days = null): int
    {
        $disk = Storage::disk('public');
        $limit = time() - 86400 * ($days ?? self::DRAFT_DAYS);
        $n = 0;
        foreach ($disk->directories(self::DRAFTS) as $dir) {
            if (!self::validToken(basename($dir))) {
                continue;
            }
            $newest = 0;
            foreach ($disk->files($dir) as $f) {
                $newest = max($newest, (int) $disk->lastModified($f));
            }
            if ($newest < $limit) {
                $disk->deleteDirectory($dir);
                $n++;
            }
        }

        return $n;
    }
}
