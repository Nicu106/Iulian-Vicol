<?php

namespace App\Console\Commands;

use App\Models\SiteText;
use Illuminate\Console\Command;

/**
 * The site's texts for /muestras/textos (App\Models\SiteText).
 *
 *   php artisan texts:sync             loads database/data/site-texts.json; what
 *                                      the client already wrote stays (the key is the text)
 *   php artisan texts:sync --changes   what the client changed: section, old, new, when
 */
class SiteTexts extends Command
{
    protected $signature = 'texts:sync {--changes : print what was changed instead of loading}';

    protected $description = 'Load the site texts for /muestras/textos, or list what the client changed';

    public function handle(): int
    {
        if ($this->option('changes')) {
            return $this->changes();
        }

        $rows = json_decode((string) file_get_contents(base_path(SiteText::DATA)), true);
        if (!is_array($rows)) {
            $this->error('Cannot read ' . SiteText::DATA);

            return self::FAILURE;
        }
        $new = 0;
        foreach ($rows as $i => $r) {
            $t = SiteText::firstOrNew(['key' => SiteText::keyFor($r['section'], $r['text'])]);
            $new += $t->exists ? 0 : 1;
            $t->fill(['section' => $r['section'], 'position' => $i + 1, 'current_text' => $r['text']])->save();
        }
        $this->info(count($rows) . ' texts, ' . $new . ' new.');

        return self::SUCCESS;
    }

    private function changes(): int
    {
        $list = SiteText::whereNotNull('proposed')->where('proposed', '!=', '')->orderBy('position')->get();
        foreach ($list as $t) {
            $this->line('<comment>' . $t->section . '</comment>  ' . $t->updated_at?->timezone('Europe/Madrid')->format('d.m H:i'));
            $this->line('  antes: ' . $t->current_text);
            $this->line('  nuevo: ' . $t->proposed);
        }
        $this->info($list->count() . ' de ' . SiteText::count() . ' cambiados.');

        return self::SUCCESS;
    }
}
