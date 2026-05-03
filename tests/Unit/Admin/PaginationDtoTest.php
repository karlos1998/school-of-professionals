<?php

use App\DTOs\Admin\PaginationDto;
use Illuminate\Pagination\LengthAwarePaginator;

it('builds pagination dto from paginator', function (): void {
    $paginator = new LengthAwarePaginator(
        items: collect([1, 2, 3]),
        total: 25,
        perPage: 10,
        currentPage: 2,
        options: ['path' => '/admin-panel/tests'],
    );

    $dto = PaginationDto::fromPaginator($paginator);

    expect($dto->toArray())->toBe([
        'current_page' => 2,
        'last_page' => 3,
        'per_page' => 10,
        'total' => 25,
    ]);
});
