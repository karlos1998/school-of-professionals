<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PaginatedResourcePayloadDto;
use App\Http\Resources\Admin\ExamAuthorityCollection;
use App\Models\ExamAuthority;
use App\Repositories\Contracts\AdminAuthorityRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminAuthorityService
{
    public function __construct(public AdminAuthorityRepositoryInterface $authorityRepository) {}

    /** @return array<string, mixed> */
    public function indexPayload(int $perPage = 50): array
    {
        $authorities = $this->authorityRepository->paginate($perPage);
        /** @var array<string,mixed> $authorityCollection */
        $authorityCollection = (new ExamAuthorityCollection($authorities))->response()->getData(true);
        $payload = PaginatedResourcePayloadDto::fromCollectionAndPaginator($authorityCollection, $authorities);

        return [
            'authorities' => $payload->toArray(),
        ];
    }

    /** @param array{name:string,slug:string} $data */
    public function create(array $data): void
    {
        $this->authorityRepository->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'sort_order' => $this->authorityRepository->nextSortOrder(),
        ]);
    }

    /** @param array{name:string} $data */
    public function update(int $authorityId, array $data): void
    {
        $authority = $this->authorityRepository->findById($authorityId);
        if ($authority === null) {
            throw (new ModelNotFoundException)->setModel(ExamAuthority::class, [$authorityId]);
        }

        $this->authorityRepository->update($authority, [
            'name' => $data['name'],
        ]);
    }

    /** @param list<int> $orderedIds */
    public function reorder(array $orderedIds): void
    {
        $existingIds = ExamAuthority::query()
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->all();

        if (count($existingIds) !== count($orderedIds)) {
            throw (new ModelNotFoundException)->setModel(ExamAuthority::class, array_values(array_diff($orderedIds, $existingIds)));
        }

        $this->authorityRepository->reorder($orderedIds);
    }
}
