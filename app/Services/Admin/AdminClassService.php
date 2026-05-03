<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamClassCollection;
use App\Models\ExamClass;
use App\Repositories\Contracts\AdminClassRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

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

    /** @param array{name:string} $data */
    public function create(array $data): void
    {
        $this->classRepository->create([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name']),
        ]);
    }

    /** @param array{name:string} $data */
    public function update(int $classId, array $data): void
    {
        $examClass = $this->classRepository->findById($classId);
        if ($examClass === null) {
            throw (new ModelNotFoundException())->setModel(ExamClass::class, [$classId]);
        }

        $this->classRepository->update($examClass, [
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name'], $classId),
        ]);
    }

    public function delete(int $classId): void
    {
        $examClass = $this->classRepository->findById($classId);
        if ($examClass === null) {
            throw (new ModelNotFoundException())->setModel(ExamClass::class, [$classId]);
        }

        $this->classRepository->delete($examClass);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slugRoot = $baseSlug !== '' ? $baseSlug : 'klasa';
        $slug = $slugRoot;
        $counter = 1;

        while ($this->classRepository->slugExists($slug, $ignoreId)) {
            $slug = "{$slugRoot}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
