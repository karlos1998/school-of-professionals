<?php

namespace App\Services\Admin;

use App\Models\Exam;
use App\Repositories\Contracts\AdminExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class AdminExamService
{
    public function __construct(public AdminExamRepositoryInterface $examRepository) {}

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->examRepository->paginate($perPage);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Exam
    {
        $data['slug'] = Str::slug((string) $data['name']).'-'.Str::random(6);

        return $this->examRepository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Exam $exam, array $data): Exam
    {
        return $this->examRepository->update($exam, $data);
    }

    public function delete(Exam $exam): void
    {
        $this->examRepository->delete($exam);
    }
}
