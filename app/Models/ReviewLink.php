<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A link the owner sends a customer (WhatsApp, or the QR on his phone) so that
 * they write their own opinion: /opinion/{token}.
 *
 * One link is one opinion. It is open until expires_at or until an opinion is
 * sent through it, whichever comes first; he can also close it by hand (which
 * sets expires_at to now). What comes in is a Testimonial with is_active false:
 * nothing reaches the home page until he has read it and ticked "Se enseña en
 * la portada". The customer ticks a consent box before sending, so used_at is
 * also the moment they agreed to their name, words and photo being published.
 *
 * The token is the only key: no name or phone in the address.
 */
class ReviewLink extends Model
{
    /** What he can choose, in days, and how the dialog says it. */
    public const PERIODS = [1 => '1 día', 3 => '3 días', 7 => '1 semana', 30 => '1 mes'];
    public const DEFAULT_DAYS = 7;

    protected $fillable = ['token', 'name', 'expires_at', 'used_at', 'testimonial_id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    /** False until the migration has run: the panel then offers only "La escribo yo". */
    public static function ready(): bool
    {
        static $ready;

        return $ready ??= Schema::hasTable('review_links');
    }

    public static function open(int $days, ?string $name): self
    {
        $days = array_key_exists($days, self::PERIODS) ? $days : self::DEFAULT_DAYS;
        $name = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $name)));

        return self::create([
            // 20 characters of [A-Za-z0-9]: 119 bits, and a QR that stays small
            'token'      => Str::random(20),
            'name'       => $name === '' ? null : Str::limit($name, 80, ''),
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function isOpen(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function url(): string
    {
        return route('opinion', $this->token);
    }

    /** "5 de noviembre, 21:30" in Spain's time (the app keeps UTC). */
    public function until(): string
    {
        return self::spain($this->expires_at)->isoFormat('D [de] MMMM, HH:mm');
    }

    public static function spain(Carbon $t): Carbon
    {
        return $t->copy()->setTimezone('Europe/Madrid')->locale('es');
    }

    public function testimonial()
    {
        return $this->belongsTo(Testimonial::class);
    }
}
