<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\ExamSettingsRepositoryInterface;

class AdminExamSettingsService
{
    public const QUESTION_LIMIT_KEY = 'exam.session.question_limit';
    public const PASSING_THRESHOLD_KEY = 'exam.session.passing_threshold';

    public function __construct(public ExamSettingsRepositoryInterface $settingsRepository) {}

    /** @return array<string, mixed> */
    public function indexPayload(): array
    {
        return [
            'settings' => [
                'question_limit' => $this->questionLimit(),
                'passing_threshold' => $this->passingThreshold(),
            ],
        ];
    }

    /** @param array{question_limit:int,passing_threshold:int} $data */
    public function update(array $data): void
    {
        $this->settingsRepository->setInt(self::QUESTION_LIMIT_KEY, $data['question_limit']);
        $this->settingsRepository->setInt(self::PASSING_THRESHOLD_KEY, $data['passing_threshold']);
    }

    public function questionLimit(): int
    {
        return $this->settingsRepository->getInt(self::QUESTION_LIMIT_KEY, (int) config('exam.session.question_limit', 20));
    }

    public function passingThreshold(): int
    {
        return $this->settingsRepository->getInt(self::PASSING_THRESHOLD_KEY, (int) config('exam.session.passing_threshold', 16));
    }
}
