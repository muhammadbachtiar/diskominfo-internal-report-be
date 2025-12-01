<?php

use App\Http\Controllers\API\V1\Asset\Category\CreateAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\Category\IndexAssetCategoryController;
use App\Http\Controllers\API\V1\Asset\CRUD\CreateAssetController;
use App\Http\Controllers\API\V1\Asset\CRUD\DeleteAssetController;
use App\Http\Controllers\API\V1\Asset\CRUD\DetailAssetController;
use App\Http\Controllers\API\V1\Asset\CRUD\IndexAssetController;
use App\Http\Controllers\API\V1\Asset\CRUD\UpdateAssetController;
use App\Http\Controllers\API\V1\Asset\Loan\ActivateAssetController;
use App\Http\Controllers\API\V1\Asset\Loan\DeactivateAssetController;
use App\Http\Controllers\API\V1\Asset\Maintenance\CompleteAssetMaintenanceController;
use App\Http\Controllers\API\V1\Asset\Maintenance\StartAssetMaintenanceController;
use App\Http\Controllers\API\V1\Asset\Report\AttachAssetToReportController;
use App\Http\Controllers\API\V1\Asset\Status\RetireAssetController;
use App\Http\Controllers\API\V1\Permission\Apps\IndexAppsListController;
use App\Http\Controllers\API\V1\Permission\CRUD\IndexPermissionController;
use App\Http\Controllers\API\V1\Roles\CRUD\CreateRoleController;
use App\Http\Controllers\API\V1\Roles\CRUD\DeleteRoleController;
use App\Http\Controllers\API\V1\Roles\CRUD\DetailRoleController;
use App\Http\Controllers\API\V1\Roles\CRUD\IndexRoleController;
use App\Http\Controllers\API\V1\Roles\CRUD\UpdateRoleController;
use App\Http\Controllers\API\V1\Shared\UploadPhotoController;
use App\Http\Controllers\API\V1\User\Auth\EditProfileController;
use App\Http\Controllers\API\V1\User\Auth\GetDataAuthController;
use App\Http\Controllers\API\V1\User\Auth\LoginController;
use App\Http\Controllers\API\V1\User\CRUD\CreateUserController;
use App\Http\Controllers\API\V1\User\CRUD\DeleteUserController;
use App\Http\Controllers\API\V1\User\CRUD\DetailUserController;
use App\Http\Controllers\API\V1\User\CRUD\IndexUserController;
use App\Http\Controllers\API\V1\User\CRUD\UpdateUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Report\CRUD\CreateReportController;
use App\Http\Controllers\API\V1\Report\CRUD\DetailReportController as ReportDetailController;
use App\Http\Controllers\API\V1\Report\CRUD\GetReportPdfController;
use App\Http\Controllers\API\V1\Report\CRUD\ExportReportController;
use App\Http\Controllers\API\V1\Notification\IndexNotificationController;
use App\Http\Controllers\API\V1\Notification\MarkAllReadNotificationController;
use App\Http\Controllers\API\V1\Notification\MarkReadNotificationController;
use App\Http\Controllers\API\V1\Report\CRUD\IndexReportController;
use App\Http\Controllers\API\V1\Report\CRUD\ReviewReportController;
use App\Http\Controllers\API\V1\Report\CRUD\SubmitReportController;
use App\Http\Controllers\API\V1\Report\Evidence\FinalizeEvidenceController;
use App\Http\Controllers\API\V1\Report\Evidence\PresignEvidenceController;
use App\Http\Controllers\API\V1\Report\Evidence\GetEvidenceController;
use App\Http\Controllers\API\V1\Shared\VerifyController;
use App\Http\Controllers\Docs\OpenApiController;
use App\Http\Controllers\API\V1\Report\CRUD\UpdateReportController;
use App\Http\Controllers\API\V1\Report\CRUD\DeleteReportController;
use App\Http\Controllers\API\V1\Report\Evidence\DeleteEvidenceController;
use App\Http\Controllers\API\V1\Report\Assignees\SyncAssigneesController;
use App\Http\Controllers\API\V1\Report\Assignees\RemoveAssigneeController;
use App\Http\Controllers\API\V1\Report\Assets\DetachAssetFromReportController;
use App\Http\Controllers\API\V1\Report\Assets\ListReportAssetsController;
use App\Http\Controllers\API\V1\Unit\CRUD\IndexUnitController;
use App\Http\Controllers\API\V1\Unit\CRUD\CreateUnitController;
use App\Http\Controllers\API\V1\Unit\CRUD\DetailUnitController;
use App\Http\Controllers\API\V1\Unit\CRUD\UpdateUnitController;
use App\Http\Controllers\API\V1\Unit\CRUD\DeleteUnitController;

Route::post(uri: 'login', action: LoginController::class);

Route::middleware(['auth:api'])->group(function () {
    Route::get(uri: 'auth', action: GetDataAuthController::class);
    Route::put(uri: 'auth', action: EditProfileController::class);
    Route::patch(uri: 'auth', action: EditProfileController::class);
});
Route::post(uri: 'upload', action: UploadPhotoController::class);
Route::prefix('user')->middleware(['auth:api'])->group(function () {
    Route::get(uri: '', action: IndexUserController::class);
    Route::post(uri: '', action: CreateUserController::class);
    Route::prefix('{user}')->group(function () {
        Route::get(uri: '', action: DetailUserController::class);
        Route::put(uri: '', action: UpdateUserController::class);
        Route::patch(uri: '', action: UpdateUserController::class);
        Route::delete(uri: '', action: DeleteUserController::class);
    });
});

Route::prefix('roles')->middleware(['auth:api'])->group(function () {
    Route::get(uri: '', action: IndexRoleController::class);
    Route::post(uri: '', action: CreateRoleController::class);
    Route::prefix('{role}')->group(function () {
        Route::get(uri: '', action: DetailRoleController::class);
        Route::put(uri: '', action: UpdateRoleController::class);
        Route::delete(uri: '', action: DeleteRoleController::class);
    });
});

Route::prefix('permission')->middleware(['auth:api'])->group(function () {
    Route::get(uri: '', action: IndexPermissionController::class);
    Route::get(uri: '/apps', action: IndexAppsListController::class);
});
Route::prefix('reports')->middleware(['auth:api'])->group(function (){
    Route::get('', IndexReportController::class);
    Route::post('', CreateReportController::class);
    Route::get('export', ExportReportController::class);
    Route::prefix('{report}')->group(function (){
        Route::get('', ReportDetailController::class);
        Route::get('pdf', GetReportPdfController::class);
        Route::put('', UpdateReportController::class);
        Route::patch('', UpdateReportController::class);
        Route::delete('', DeleteReportController::class);
        Route::post('submit', SubmitReportController::class);
        Route::post('review', ReviewReportController::class);
        Route::prefix('assignees')->group(function (){
            Route::post('', SyncAssigneesController::class);
            Route::delete('{user}', RemoveAssigneeController::class);
        });
        Route::prefix('assets')->group(function () {
            Route::get('', ListReportAssetsController::class);
            Route::delete('{asset}', DetachAssetFromReportController::class);
        });
        Route::prefix('evidences')->group(function (){
            Route::post('presign', PresignEvidenceController::class);
            Route::post('finalize', FinalizeEvidenceController::class);
            Route::get('{evidence}', GetEvidenceController::class);
            Route::delete('{evidence}', DeleteEvidenceController::class);
        });
    });
});

Route::prefix('assets')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexAssetController::class);
    Route::post('', CreateAssetController::class);
    Route::prefix('{asset}')->group(function () {
        Route::get('', DetailAssetController::class);
        Route::put('', UpdateAssetController::class);
        Route::patch('', UpdateAssetController::class);
        Route::delete('', DeleteAssetController::class);
        Route::post('activate', ActivateAssetController::class);
        Route::post('deactivate', DeactivateAssetController::class);
        Route::post('maintenance', StartAssetMaintenanceController::class);
        Route::post('maintenance/complete', CompleteAssetMaintenanceController::class);
        Route::post('retire', RetireAssetController::class);
        Route::post('reports/{report}', AttachAssetToReportController::class);
    });
});

Route::prefix('asset-categories')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexAssetCategoryController::class);
    Route::post('', CreateAssetCategoryController::class);
});

Route::get('verify', VerifyController::class);
// Serve OpenAPI spec under API prefix
Route::get('openapi.yaml', [OpenApiController::class, 'spec']);

Route::get('health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('metrics', function () {
    // Simple placeholder metrics
    return response("reports_total".": ".\Infra\Report\Models\Report::count()."\n", 200)->header('Content-Type', 'text/plain');
});

Route::prefix('notifications')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexNotificationController::class);
    Route::post('read-all', MarkAllReadNotificationController::class);
    Route::post('{notification}/read', MarkReadNotificationController::class);
});

Route::prefix('units')->middleware(['auth:api'])->group(function () {
    Route::get('', IndexUnitController::class);
    Route::post('', CreateUnitController::class);
    Route::prefix('{unit}')->group(function () {
        Route::get('', DetailUnitController::class);
        Route::put('', UpdateUnitController::class);
        Route::patch('', UpdateUnitController::class);
        Route::delete('', DeleteUnitController::class);
    });
});
