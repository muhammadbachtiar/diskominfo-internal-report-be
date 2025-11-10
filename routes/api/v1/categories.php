<?php

use App\Http\Controllers\API\V1\Asset\Category\CreateAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\IndexAssetCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('asset-categories')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexAssetCategoryController::class);
    Route::post('', CreateAssetCategoryController::class);
});
