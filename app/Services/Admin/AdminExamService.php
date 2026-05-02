<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamCollection;
use App\Repositories\Contracts\AdminLookupRepositoryInterface;
use App\Repositories\Contracts\AdminExamRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class AdminExamService
{
    public function __construct(
        public AdminExamRepositoryInterface $examRepository,
        public AdminLookupRepositoryInterface $lookupRepository,
    ) {}

    /** @return array<string, mixed> */
    public function indexPayload(int $perPage = 50): array
    {
        $exams = $this->examRepository->paginate($perPage);
        /** @var array<string, mixed> $examCollection */
        $examCollection = (new ExamCollection($exams))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($examCollection, $exams);

        return [
            'exams' => $payload->toArray(),
            'authorities' => $this->lookupRepository->examAuthorities(),
            'categories' => $this->lookupRepository->examCategories(),
            'classes' => $this->lookupRepository->examClasses(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): void
    {
        $data['slug'] = Str::slug((string) $data['name']).'-'.Str::random(6);

        $this->examRepository->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $examId, array $data): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            throw (new ModelNotFoundException())->setModel('exam', [$examId]);
        }

        $this->examRepository->update($exam, $data);
    }

    public function delete(int $examId): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            throw (new ModelNotFoundException())->setModel('exam', [$examId]);
        }

        $this->examRepository->delete($exam);
    }
}
