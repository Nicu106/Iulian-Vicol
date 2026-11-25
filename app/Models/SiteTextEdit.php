<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One saved version of a new wording (App\Models\SiteText). Written once, never changed. */
class SiteTextEdit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['site_text_id', 'value', 'ip'];

    public function text()
    {
        return $this->belongsTo(SiteText::class, 'site_text_id');
    }
}
