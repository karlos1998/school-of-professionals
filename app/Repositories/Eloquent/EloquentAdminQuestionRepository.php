<?php

namespace App\Repositories\Eloquent;

use App\Models\Answer;
use App\Models\Question;
use App\Repositories\Contracts\AdminQuestionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminQuestionRepository extends BaseEloquentRepository implements AdminQuestionRepositoryInterface
{
    public function paginateForExam(int $examId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateQuery(
            Question::query()
                ->where('exam_id', $examId)
                ->with('answers')
                ->orderBy('position'),
            $perPage,
        );
    }

    public function createForExam(int $examId, array $data): Question
    {
        return Question::query()->create([
            'exam_id' => $examId,
            'position' => $data['position'],
            'content' => $data['content'],
            'explanation' => $data['explanation'] ?? null,
        ]);
    }

    public function findById(int $questionId): ?Question
    {
        return Question::query()->find($questionId);
    }

    public function update(Question $question, array $data): Question
    {
        $question->update([
            'position' => $data['position'],
            'content' => $data['content'],
            'explanation' => $data['explanation'] ?? null,
        ]);

        return $question->refresh();
    }

    public function replaceAnswers(Question $question, array $answers): void
    {
        $question->answers()->delete();

        foreach ($answers as $answer) {
            Answer::query()->create([
                'question_id' => $question->id,
                'content' => $answer['content'],
                'is_correct' => (bool) $answer['is_correct'],
            ]);
        }
    }

    public function delete(Question $question): void
    {
        $question->delete();
    }
}
