<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamClassCollection;
use App\Repositories\Contracts\AdminClassRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminClassService
{
    public function __construct(public AdminClassRepositoryInterface $classRepository) {}

    /** @return array<string, mixed> */
    public function indexPayload(int $perPage = 50): array
    {
        $classes = $this->classRepository->paginate($perPage);
        /** @var array<string,mixed> $classCollection */
        $classCollection = (new ExamClassCollection($classes))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($classCollection, $classes);

        return [
            'classes' => $payload->toArray(),
        ];
    }

    /** @param array{name:string,slug:string} $data */
    public function create(array $data): void
    {
        $this->classRepository->create($data);
    }

    /** @param array{name:string,slug:string} $data */
    public function update(int $classId, array $data): void
    {
        $examClass = $this->classRepository->findById($classId);
        if ($examClass === null) {
            throw (new ModelNotFoundException())->setModel('exam_class', [$classId]);
        }

        $this->classRepository->update($examClass, $data);
    }

    public function delete(int $classId): void
    {
        $examClass = $this->classRepository->findById($classId);
        if ($examClass === null) {
            throw (new ModelNotFoundException())->setModel('exam_class', [$classId]);
        }

        $this->classRepository->delete($examClass);
    }
}
