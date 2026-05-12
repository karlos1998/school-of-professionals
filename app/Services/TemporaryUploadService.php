<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class TemporaryUploadService
{
    /**
     * @return array{
     *     path: string,
     *     url: string,
     *     name: string,
     *     size: int,
     *     mime_type: string|null
     * }
     */
    public function store(UploadedFile $file): array
    {
        $disk = $this->temporaryDisk();
        $path = $file->store($this->temporaryDirectory(), $disk);

        if (! is_string($path)) {
            throw new RuntimeException('Unable to store temporary upload.');
        }

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'name' => $file->getClientOriginalName(),
            'size' => (int) $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    public function promoteToDisk(string $temporaryPath, string $targetDisk, string $targetPath): string
    {
        if (! $this->isTemporaryPath($temporaryPath)) {
            throw new RuntimeException('Invalid temporary upload path.');
        }

        $temporaryDisk = Storage::disk($this->temporaryDisk());
        $stream = $temporaryDisk->readStream($temporaryPath);

        if ($stream === false) {
            throw new RuntimeException('Temporary upload was not found.');
        }

        try {
            Storage::disk($targetDisk)->put($targetPath, $stream, 'public');
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $temporaryDisk->delete($temporaryPath);

        return $targetPath;
    }

    public function delete(string $path): void
    {
        if ($this->isTemporaryPath($path)) {
            Storage::disk($this->temporaryDisk())->delete($path);
        }
    }

    public function isTemporaryPath(?string $path): bool
    {
        if (! is_string($path) || $path === '' || str_contains($path, '..')) {
            return false;
        }

        return Str::startsWith($path, $this->temporaryDirectory().'/');
    }

    public function pruneExpired(): int
    {
        $disk = Storage::disk($this->temporaryDisk());
        $expiresBefore = now()->subHours((int) config('uploads.temporary_ttl_hours'))->getTimestamp();
        $deleted = 0;

        foreach ($disk->allFiles($this->temporaryDirectory()) as $path) {
            if ($disk->lastModified($path) > $expiresBefore) {
                continue;
            }

            if ($disk->delete($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function temporaryDisk(): string
    {
        return (string) config('uploads.temporary_disk');
    }

    private function temporaryDirectory(): string
    {
        return trim((string) config('uploads.temporary_directory'), '/');
    }
}
