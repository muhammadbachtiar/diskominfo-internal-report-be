<?php

use App\Http\Controllers\API\V1\Report\Action\AddReportActionController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports/{report}/actions')->middleware(['auth:api'])->group(function () {
    Route::post('', AddReportActionController::class);
});
