<?php

namespace App\Services\Admin;

use App\Models\Exam;
use App\Models\Question;
use App\Services\TemporaryUploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuestionImageService
{
    public function __construct(
        private readonly TemporaryUploadService $temporaryUploadService,
    ) {}

    /** @param array<string, mixed> $data */
    public function prepareForCreate(Exam $exam, array $data): array
    {
        $data['image_path'] = $this->resolveImagePath(
            exam: $exam,
            position: (int) $data['position'],
            submittedPath: $data['image_path'] ?? null,
            question: null,
        );

        return $data;
    }

    /** @param array<string, mixed> $data */
    public function prepareForUpdate(Exam $exam, Question $question, array $data): array
    {
        $data['image_path'] = $this->resolveImagePath(
            exam: $exam,
            position: (int) $data['position'],
            submittedPath: array_key_exists('image_path', $data) ? $data['image_path'] : $question->image_path,
            question: $question,
        );

        return $data;
    }

    public function deleteForQuestion(Question $question): void
    {
        $this->deleteStoredImage($question->image_path);
    }

    private function resolveImagePath(Exam $exam, int $position, mixed $submittedPath, ?Question $question): ?string
    {
        $submittedPath = is_string($submittedPath) && trim($submittedPath) !== ''
            ? trim($submittedPath)
            : null;

        $currentPath = $question?->image_path;

        if ($submittedPath === null) {
            $this->deleteStoredImage($currentPath);

            return null;
        }

        if (! $this->temporaryUploadService->isTemporaryPath($submittedPath)) {
            if ($question instanceof Question && $submittedPath === $currentPath) {
                return $submittedPath;
            }

            throw ValidationException::withMessages([
                'image_path' => 'Nieprawidłowa ścieżka obrazu.',
            ]);
        }

        $targetPath = $this->temporaryUploadService->promoteToDisk(
            temporaryPath: $submittedPath,
            targetDisk: $this->imageDisk(),
            targetPath: $this->targetPath($exam, $position, $submittedPath),
        );

        if ($currentPath !== $targetPath) {
            $this->deleteStoredImage($currentPath);
        }

        return $targetPath;
    }

    private function targetPath(Exam $exam, int $position, string $temporaryPath): string
    {
        $extension = pathinfo($temporaryPath, PATHINFO_EXTENSION) ?: 'jpg';

        return trim((string) config('exam_sync.image_directory'), '/')
            .'/'
            .$exam->slug
            .'/'
            .sprintf('%04d.%s', $position, Str::lower($extension));
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        Storage::disk($this->imageDisk())->delete($path);
    }

    private function imageDisk(): string
    {
        return (string) config('exam_sync.image_disk');
    }
}
