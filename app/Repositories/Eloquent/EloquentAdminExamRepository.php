<?php

namespace App\Repositories\Eloquent;

use App\Models\Exam;
use App\Repositories\Contracts\AdminExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminExamRepository extends BaseEloquentRepository implements AdminExamRepositoryInterface
{
    /**
     * @param array{authority:string|null,search:string|null} $filters
     * @return LengthAwarePaginator<int, Exam>
     */
    public function paginate(int $perPage = 50, array $filters = ['authority' => null, 'search' => null]): LengthAwarePaginator
    {
        $authority = $filters['authority'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Exam::query()
            ->with(['authority', 'category', 'examClass'])
            ->withCount('questions')
            ->latest();

        if (is_string($authority) && $authority !== '') {
            $query->whereHas('authority', fn ($authorityQuery) => $authorityQuery->where('slug', $authority));
        }

        if (is_string($search) && $search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $this->paginateQuery(
            $query,
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
