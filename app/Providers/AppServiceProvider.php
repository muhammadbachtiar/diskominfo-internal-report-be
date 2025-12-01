<?php

namespace App\Providers;

use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Domain\Asset\Repositories\AssetLocationRepositoryInterface;
use Domain\Asset\Repositories\AssetMaintenanceRepositoryInterface;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Infra\Asset\Repositories\EloquentAssetLoanRepository;
use Infra\Asset\Repositories\EloquentAssetLocationRepository;
use Infra\Asset\Repositories\EloquentAssetMaintenanceRepository;
use Infra\Asset\Repositories\EloquentAssetRepository;
use Infra\Asset\Repositories\EloquentAssetStatusHistoryRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AssetRepositoryInterface::class, EloquentAssetRepository::class);
        $this->app->bind(AssetLoanRepositoryInterface::class, EloquentAssetLoanRepository::class);
        $this->app->bind(AssetLocationRepositoryInterface::class, EloquentAssetLocationRepository::class);
        $this->app->bind(AssetStatusHistoryRepositoryInterface::class, EloquentAssetStatusHistoryRepository::class);
        $this->app->bind(AssetMaintenanceRepositoryInterface::class, EloquentAssetMaintenanceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
