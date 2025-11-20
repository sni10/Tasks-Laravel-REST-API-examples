<?php

namespace App\Providers;

use App\Contracts\CommentRepositoryInterface;
use App\Contracts\CommentServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TeamRepositoryInterface;
use App\Contracts\TeamServiceInterface;
use App\Contracts\TokenRepositoryInterface;
use App\Contracts\TokenServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Repositories\EloquentCommentRepository;
use App\Repositories\EloquentTaskRepository;
use App\Repositories\EloquentTeamRepository;
use App\Repositories\EloquentTokenRepository;
use App\Repositories\EloquentUserRepository;
use App\Services\CommentService;
use App\Services\TaskService;
use App\Services\TeamService;
use App\Services\TokenService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
        $this->app->bind(
            TaskRepositoryInterface::class,
            EloquentTaskRepository::class
        );
        $this->app->bind(
            TeamRepositoryInterface::class,
            EloquentTeamRepository::class
        );
        $this->app->bind(
            CommentRepositoryInterface::class,
            EloquentCommentRepository::class
        );
        $this->app->bind(
            TokenRepositoryInterface::class,
            EloquentTokenRepository::class
        );

        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
