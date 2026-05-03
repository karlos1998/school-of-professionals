<?php

namespace App\Jobs;

use App\Services\ExamSync\ExamSyncService;
use App\Services\ExamSync\SourceType;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncExamCatalogItemJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * @param  array{
     *  title: string,
     *  class: array{label: string, slug: string, url: string}
     * }  $item
     */
    public function __construct(
        public string $source,
        public array $item,
    ) {}

    public function handle(ExamSyncService $syncService): void
    {
        $syncService->sync(SourceType::from($this->source), [$this->item]);
    }
}
