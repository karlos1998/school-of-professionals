<?php

namespace App\DTOs\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class PaginatedResourcePayloadDto
{
    /**
     * @param  list<array<string, mixed>>  $data
     */
    public function __construct(
        public array $data,
        public PaginationDto $pagination,
    ) {}

    /**
     * @param  array<string, mixed>  $resourceCollectionData
     */
    public static function fromCollectionAndPaginator(array $resourceCollectionData, LengthAwarePaginator $paginator): self
    {
        /** @var list<array<string, mixed>> $data */
        $data = $resourceCollectionData['data'] ?? [];

        return new self(
            data: $data,
            pagination: PaginationDto::fromPaginator($paginator),
        );
    }

    /**
     * @return array{
     *   data:list<array<string,mixed>>,
     *   pagination:array{current_page:int,last_page:int,per_page:int,total:int}
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => $this->pagination->toArray(),
        ];
    }
}
