<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamCategoryCollection;
use App\Models\ExamCategory;
use App\Repositories\Contracts\AdminCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class AdminCategoryService
{
    public function __construct(public AdminCategoryRepositoryInterface $categoryRepository) {}

    /** @return array<string, mixed> */
    public function indexPayload(int $perPage = 50): array
    {
        $categories = $this->categoryRepository->paginate($perPage);
        /** @var array<string,mixed> $categoryCollection */
        $categoryCollection = (new ExamCategoryCollection($categories))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($categoryCollection, $categories);

        return [
            'categories' => $payload->toArray(),
        ];
    }

    /** @param array{name:string} $data */
    public function create(array $data): void
    {
        $this->categoryRepository->create([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name']),
        ]);
    }

    /** @param array{name:string} $data */
    public function update(int $categoryId, array $data): void
    {
        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw (new ModelNotFoundException)->setModel(ExamCategory::class, [$categoryId]);
        }

        $this->categoryRepository->update($category, [
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name'], $categoryId),
        ]);
    }

    public function delete(int $categoryId): void
    {
        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw (new ModelNotFoundException)->setModel(ExamCategory::class, [$categoryId]);
        }

        $this->categoryRepository->delete($category);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slugRoot = $baseSlug !== '' ? $baseSlug : 'kategoria';
        $slug = $slugRoot;
        $counter = 1;

        while ($this->categoryRepository->slugExists($slug, $ignoreId)) {
            $slug = "{$slugRoot}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
