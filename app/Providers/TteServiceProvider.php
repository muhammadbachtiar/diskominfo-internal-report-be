<?php

namespace App\Providers;

use Domain\Report\Signing\TteSignerInterface;
use Illuminate\Support\ServiceProvider;
use Infra\Report\Signing\MockTteSigner;

class TteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TteSignerInterface::class, function () {
            $provider = config('tte.provider');
            // TODO: add BSrE provider when available
            return new MockTteSigner();
        });
    }
}

