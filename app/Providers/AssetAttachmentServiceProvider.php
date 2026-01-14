<?php

namespace App\Providers;

use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infra\Asset\Repositories\AssetAttachmentRepository;

class AssetAttachmentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            AssetAttachmentRepositoryInterface::class,
            AssetAttachmentRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}