<?php

namespace App\DTOs\Admin;

use App\Models\User;

readonly class AdminLoginResultDto
{
    public function __construct(
        public bool $success,
        public ?User $user = null,
        public ?string $errorMessage = null,
    ) {}
}
