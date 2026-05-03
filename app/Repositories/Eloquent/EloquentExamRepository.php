<?php

namespace App\Repositories\Eloquent;

use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentExamRepository implements ExamRepositoryInterface
{
    /** @return Collection<int, ExamAuthority> */
    public function getExamAuthorities(): Collection
    {
        return ExamAuthority::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Exam> */
    public function getExamsForAuthority(string $authoritySlug): Collection
    {
        return Exam::query()
            ->with([
                'authority:id,name,slug',
                'category:id,name,slug',
                'examClass:id,name,slug',
            ])
            ->withCount('questions')
            ->whereHas('authority', fn ($query) => $query->where('slug', $authoritySlug))
            ->orderBy('name')
            ->get([
                'id',
                'exam_authority_id',
                'exam_category_id',
                'exam_class_id',
                'name',
                'slug',
                'description',
            ]);
    }

    public function getExamWithQuestionsById(int $examId): ?Exam
    {
        return Exam::query()
            ->with([
                'authority:id,name,slug',
                'category:id,name,slug',
                'examClass:id,name,slug',
                'questions' => fn ($query) => $query
                    ->select(['id', 'exam_id', 'position', 'content', 'image_path', 'explanation'])
                    ->orderBy('position')
                    ->with([
                        'answers' => fn ($answerQuery) => $answerQuery
                            ->select(['id', 'question_id', 'content', 'is_correct'])
                            ->orderBy('id'),
                    ]),
            ])
            ->find($examId, [
                'id',
                'exam_authority_id',
                'exam_category_id',
                'exam_class_id',
                'name',
                'slug',
                'description',
            ]);
    }
}
