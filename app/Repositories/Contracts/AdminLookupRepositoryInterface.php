<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AdminLookupRepositoryInterface
{
    /** @return Collection<int, array{id:int,name:string}> */
    public function examAuthorities(): Collection;

    /** @return Collection<int, array{id:int,name:string}> */
    public function examCategories(): Collection;

    /** @return Collection<int, array{id:int,name:string}> */
    public function examClasses(): Collection;
}
