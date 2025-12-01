<?php

use App\Http\Controllers\API\V1\Asset\Category\CreateAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\DeleteAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\IndexAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\ShowAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\UpdateAssetCategoryController;
use App\Http\Controllers\API\V1\Report\Category\CreateReportCategoryController;
use App\Http\Controllers\API\V1\Report\Category\DeleteReportCategoryController;
use App\Http\Controllers\API\V1\Report\Category\IndexReportCategoryController;
use App\Http\Controllers\API\V1\Report\Category\ShowReportCategoryController;
use App\Http\Controllers\API\V1\Report\Category\UpdateReportCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('asset-categories')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexAssetCategoryController::class);
    Route::post('', CreateAssetCategoryController::class);
    Route::get('{id}', ShowAssetCategoryController::class);
    Route::put('{id}', UpdateAssetCategoryController::class);
    Route::delete('{id}', DeleteAssetCategoryController::class);
});

Route::prefix('report-categories')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexReportCategoryController::class);
    Route::post('', CreateReportCategoryController::class);
    Route::get('{id}', ShowReportCategoryController::class);
    Route::put('{id}', UpdateReportCategoryController::class);
    Route::delete('{id}', DeleteReportCategoryController::class);
});
