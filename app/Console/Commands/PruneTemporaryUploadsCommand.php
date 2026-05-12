<?php

namespace App\Console\Commands;

use App\Services\TemporaryUploadService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('uploads:prune-temporary')]
#[Description('Deletes expired temporary uploads')]
class PruneTemporaryUploadsCommand extends Command
{
    public function handle(TemporaryUploadService $temporaryUploadService): int
    {
        $deleted = $temporaryUploadService->pruneExpired();

        $this->info(sprintf('Deleted %d temporary uploads.', $deleted));

        return self::SUCCESS;
    }
}
