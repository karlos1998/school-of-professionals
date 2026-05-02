<?php

namespace App\Services\Admin;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminQuestionService
{
    public function paginateForExam(Exam $exam, int $perPage = 10): LengthAwarePaginator
    {
        return Question::query()
            ->whereBelongsTo($exam)
            ->with('answers')
            ->orderBy('position')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string, mixed> $data */
    public function create(Exam $exam, array $data): void
    {
        DB::transaction(function () use ($exam, $data): void {
            $question = Question::query()->create([
                'exam_id' => $exam->id,
                'position' => $data['position'],
                'content' => $data['content'],
                'explanation' => $data['explanation'] ?? null,
            ]);

            foreach ($data['answers'] as $answer) {
                Answer::query()->create([
                    'question_id' => $question->id,
                    'content' => $answer['content'],
                    'is_correct' => (bool) $answer['is_correct'],
                ]);
            }
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Question $question, array $data): void
    {
        DB::transaction(function () use ($question, $data): void {
            $question->update([
                'position' => $data['position'],
                'content' => $data['content'],
                'explanation' => $data['explanation'] ?? null,
            ]);

            $question->answers()->delete();

            foreach ($data['answers'] as $answer) {
                Answer::query()->create([
                    'question_id' => $question->id,
                    'content' => $answer['content'],
                    'is_correct' => (bool) $answer['is_correct'],
                ]);
            }
        });
    }

    public function delete(Question $question): void
    {
        $question->delete();
    }
}
