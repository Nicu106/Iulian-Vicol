<?php

namespace App\Jobs;

use App\Support\IndexNow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Sends changed URLs to IndexNow from the queue (App\Support\IndexNow). */
class IndexNowPing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 600;

    /** @param list<string> $urls */
    public function __construct(public array $urls)
    {
    }

    public function handle(): void
    {
        IndexNow::send($this->urls);
    }
}
