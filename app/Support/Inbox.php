<?php

namespace App\Support;

use App\Models\ContactMessage;
use Illuminate\Support\Collection;

/**
 * The contact form has no defence on it, and the table shows what that costs.
 * Measured over the 233 rows in the v2 database on 2026-09-10:
 *
 *     197 of 233  carry a link
 *     168 of 233  have a body that is not unique
 *     104         distinct bodies in total
 *       1         written in a non-Latin script
 *
 * Five identical "The ultimate score a $25,000 promo code" arrived from one
 * address inside the same second. The panel showed all of it as one number —
 * "Total: 233 mensajes" — and left the owner to find the real ones by reading
 * twelve pages.
 *
 * Nothing here deletes anything. The rows are the same rows; this only says
 * which pile each belongs in, and the owner can open the other pile whenever
 * he likes. A classifier that throws messages away has to be right every time;
 * one that sorts them only has to be useful.
 *
 * The rules are the ones already proven on /vende (SellCarRequest), turned
 * around: there they refuse a submission, here they file one.
 */
class Inbox
{
    /** A link in a message to a used-car dealer in Málaga is not a question about a car. */
    private const LINK = '~(?:https?://|www\.)|\b[a-z0-9-]+\.(?:com|net|org|ru|cn|xyz|top|info|biz|club|online|site|shop)\b~i';

    /** Cyrillic, CJK, Arabic. The form is Spanish and the shop is in Málaga. */
    private const SCRIPT = '~[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{0600}-\x{06FF}]~u';

    /** How many messages one name may send before the name itself is the tell. */
    private const SAME_NAME = 3;

    /** A body longer than this with no space in it is a generated token, not a sentence. */
    private const RUN_ON = 15;

    /**
     * Split every message into the two piles.
     *
     * Duplication can only be judged against the whole table, so this is done
     * once over the collection rather than per row: a body that appears twice
     * makes BOTH copies junk, and a per-row test cannot see that.
     *
     * @return array{real: Collection, junk: Collection}
     */
    public static function sort(Collection $messages): array
    {
        $seen = $names = [];
        foreach ($messages as $m) {
            $k = self::fingerprint($m);
            $seen[$k] = ($seen[$k] ?? 0) + 1;

            $n = self::who($m);
            $names[$n] = ($names[$n] ?? 0) + 1;
        }

        $real = collect();
        $junk = collect();

        foreach ($messages as $m) {
            if (self::isJunk($m, $seen, $names)) {
                $junk->push($m);
            } else {
                $real->push($m);
            }
        }

        return ['real' => $real, 'junk' => $junk];
    }

    /**
     * @param array<string,int> $seen  fingerprint => how many times it appears
     * @param array<string,int> $names sender name => how many they sent
     */
    public static function isJunk(ContactMessage $m, array $seen = [], array $names = []): bool
    {
        $text = trim(($m->message ?? '') . ' ' . ($m->subject ?? '') . ' ' . ($m->name ?? ''));

        if ($text === '') {
            return true;
        }

        if (preg_match(self::LINK, $text)) {
            return true;
        }

        if (preg_match(self::SCRIPT, $text)) {
            return true;
        }

        if ($seen && (($seen[self::fingerprint($m)] ?? 0) > 1)) {
            return true;
        }

        /* One name, many messages. Measured: 66 distinct names sent 233
           messages. "DanielWeino" sent 70 of them from 14 different email
           addresses and "HarryGoW" 45 from 9, each with a different body, so
           neither the address nor the text catches them — the name does. Three
           is the threshold because the smallest offender above it, at 3, is an
           Instagram-marketing pitch, and a person asking about a car three
           times in five months is rarer than that. It costs nothing if it is
           wrong: the message is one press away in the other pile. */
        if ($names && (($names[self::who($m)] ?? 0) >= self::SAME_NAME)) {
            return true;
        }

        $body = trim((string) ($m->message ?? ''));

        /* One run-on token and nothing else. Nine of the survivors up to this
           point were bodies like "MERTHYTJTJ824135MAYTRYR" — 23 characters
           without a space. A sentence has spaces; the threshold is 15 so that
           a genuine one-word reply ("Gracias", "Interesado") is never touched. */
        if (mb_strlen($body) > self::RUN_ON && ! preg_match('~\s~u', $body)) {
            return true;
        }

        /* The same long number in the name AND in the message. The generator
           behind "NAEWTRER824135NEYHRTGE" / "MERTHYTJTJ824135MAYTRYR" stamps
           its serial into both halves; a person filling a form does not put a
           four-digit number in their own name. */
        if (preg_match_all('~\d{4,}~', (string) ($m->name ?? ''), $hit)) {
            foreach ($hit[0] as $run) {
                if (str_contains($body, $run)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function who(ContactMessage $m): string
    {
        return mb_strtolower(trim((string) ($m->name ?? '')));
    }

    /**
     * Duplicates are near-identical rather than identical: the five promo-code
     * messages carried five different telephone numbers. Whitespace is
     * collapsed, case is dropped, and only the opening of the body is compared,
     * because a bot that varies its text varies the tail.
     */
    private static function fingerprint(ContactMessage $m): string
    {
        $s = mb_strtolower(trim((string) ($m->message ?? '')));
        $s = preg_replace('~\s+~u', ' ', $s);

        return mb_substr($s, 0, 60);
    }

    /**
     * How many real messages are waiting. Read on every admin page for the
     * count beside "Mensajes", so it is memoised for the request and kept out
     * of the layout.
     *
     * @return array{real: int, junk: int, total: int}
     */
    public static function counts(): array
    {
        static $counts = null;

        if ($counts !== null) {
            return $counts;
        }

        $all = ContactMessage::query()->get(['id', 'name', 'subject', 'message']);
        $split = self::sort($all);

        return $counts = [
            'real'  => $split['real']->count(),
            'junk'  => $split['junk']->count(),
            'total' => $all->count(),
        ];
    }
}
