<?php

use App\Http\Controllers\API\V1\Asset\Location\CreateLocationController;
use App\Http\Controllers\API\V1\Asset\Location\DeleteLocationController;
use App\Http\Controllers\API\V1\Asset\Location\IndexLocationController;
use App\Http\Controllers\API\V1\Asset\Location\ShowLocationController;
use App\Http\Controllers\API\V1\Asset\Location\UpdateLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('locations')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexLocationController::class);
    Route::post('', CreateLocationController::class);
    Route::prefix('{location}')->group(function () {
        Route::get('', ShowLocationController::class);
        Route::put('', UpdateLocationController::class);
        Route::patch('', UpdateLocationController::class);
        Route::delete('', DeleteLocationController::class);
    });
});
