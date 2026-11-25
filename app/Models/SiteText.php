<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One text of the site and the client's new wording for it (/muestras/textos).
 * Every save is also a SiteTextEdit, kept for good.
 */
class SiteText extends Model
{
    public const DATA = 'database/data/site-texts.json';

    protected $fillable = ['key', 'section', 'position', 'current_text', 'proposed'];

    protected $casts = ['position' => 'integer'];

    public static function keyFor(string $section, string $text): string
    {
        return substr(sha1($section . "\n" . $text), 0, 16);
    }

    public function changed(): bool
    {
        return trim((string) $this->proposed) !== '';
    }

    /** The text as the page shows it: escaped, its notes as tags, its {placeholders} marked. */
    public function originalHtml(): string
    {
        $html = e($this->current_text);
        foreach (['[titlu în Google și în tab] ' => 'titlu Google', '[descriere în Google] ' => 'descriere Google', '[text gri în câmp] ' => 'text în câmp'] as $note => $tag) {
            if (str_starts_with($html, e($note))) {
                $html = '<span class="tag">' . $tag . '</span> ' . substr($html, strlen(e($note)));
            }
        }

        return preg_replace('/\{[^{}]+\}/u', '<span class="ph">$0</span>', $html);
    }

    public function edits()
    {
        return $this->hasMany(SiteTextEdit::class);
    }
}
