<?php

namespace App\Repositories\Eloquent;

use App\Models\Exam;
use App\Repositories\Contracts\AdminExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminExamRepository extends BaseEloquentRepository implements AdminExamRepositoryInterface
{
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->paginateQuery(
            Exam::query()
                ->with(['authority', 'category', 'examClass'])
                ->withCount('questions')
                ->latest(),
            $perPage,
        );
    }

    public function findById(int $examId): ?Exam
    {
        return Exam::query()->find($examId);
    }

    public function create(array $data): Exam
    {
        return Exam::query()->create($data);
    }

    public function update(Exam $exam, array $data): Exam
    {
        $exam->update($data);

        return $exam->refresh();
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
    }
}
