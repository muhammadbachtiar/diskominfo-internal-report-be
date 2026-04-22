<?php

namespace App\Http\Controllers\API\V1\Asset;

use App\Imports\AssetImport;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Maatwebsite\Excel\Facades\Excel;

class ImportAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // Max 5MB
            ]);

            $import = new AssetImport();
            Excel::import($import, $request->file('file'));

            return $this->resolveForSuccessResponseWith('Proses Import Selesai.', [
                'imported_count' => count($import->importedRows),
                'failures_count' => count($import->failures),
                'imported_data' => $import->importedRows,
                'failures_data' => $import->failures,
            ], HttpStatus::Ok);

        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('File tidak valid.', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Exception $e) {
            return $this->resolveForFailedResponseWith('Terjadi kesalahan saat import: ' . $e->getMessage(), [], HttpStatus::InternalError);
        }
    }
}
