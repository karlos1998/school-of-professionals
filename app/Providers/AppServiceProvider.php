<?php

namespace App\Providers;

use App\Repositories\Contracts\AdminExamRepositoryInterface;
use App\Repositories\Contracts\AdminClassRepositoryInterface;
use App\Repositories\Contracts\AdminLookupRepositoryInterface;
use App\Repositories\Contracts\AdminQuestionRepositoryInterface;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Eloquent\EloquentAdminExamRepository;
use App\Repositories\Eloquent\EloquentAdminClassRepository;
use App\Repositories\Eloquent\EloquentAdminLookupRepository;
use App\Repositories\Eloquent\EloquentAdminQuestionRepository;
use App\Repositories\Eloquent\EloquentExamRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExamRepositoryInterface::class, EloquentExamRepository::class);
        $this->app->bind(AdminExamRepositoryInterface::class, EloquentAdminExamRepository::class);
        $this->app->bind(AdminClassRepositoryInterface::class, EloquentAdminClassRepository::class);
        $this->app->bind(AdminQuestionRepositoryInterface::class, EloquentAdminQuestionRepository::class);
        $this->app->bind(AdminLookupRepositoryInterface::class, EloquentAdminLookupRepository::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
