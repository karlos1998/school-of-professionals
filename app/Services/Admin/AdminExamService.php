<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamCollection;
use App\Models\Exam;
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

    /**
     * @param array{authority:string|null,search:string|null} $filters
     * @return array{
     *   exams: array{
     *     data:list<array<string,mixed>>,
     *     pagination:array{current_page:int,last_page:int,per_page:int,total:int}
     *   },
     *   authorities:\Illuminate\Support\Collection<int, array{id:int,name:string}>,
     *   categories:\Illuminate\Support\Collection<int, array{id:int,name:string}>,
     *   classes:\Illuminate\Support\Collection<int, array{id:int,name:string}>,
     *   filters:array{authority:string|null,search:string|null}
     * }
     */
    public function indexPayload(int $perPage = 50, array $filters = ['authority' => null, 'search' => null]): array
    {
        $exams = $this->examRepository->paginate($perPage, $filters);
        /** @var array<string, mixed> $examCollection */
        $examCollection = (new ExamCollection($exams))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($examCollection, $exams);

        return [
            'exams' => $payload->toArray(),
            'authorities' => $this->lookupRepository->examAuthorities(),
            'categories' => $this->lookupRepository->examCategories(),
            'classes' => $this->lookupRepository->examClasses(),
            'filters' => [
                'authority' => $filters['authority'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
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
            throw new ModelNotFoundException()->setModel(Exam::class, [$examId]);
        }

        $this->examRepository->update($exam, $data);
    }

    public function delete(int $examId): void
    {
        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            throw new ModelNotFoundException()->setModel(Exam::class, [$examId]);
        }

        $this->examRepository->delete($exam);
    }
}
