<?php

namespace App\DTOs\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class PaginationDto
{
    public function __construct(
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {}

    /** @param LengthAwarePaginator<int, mixed> $paginator */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
        );
    }

    /** @return array{current_page:int,last_page:int,per_page:int,total:int} */
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
        ];
    }
}
