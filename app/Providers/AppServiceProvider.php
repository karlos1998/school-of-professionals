<?php

namespace App\Providers;

use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Eloquent\EloquentExamRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExamRepositoryInterface::class, EloquentExamRepository::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
