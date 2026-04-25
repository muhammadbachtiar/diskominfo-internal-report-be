<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use App\Exports\AssetExport;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\Asset;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ExportAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('view-asset');

            $request->validate([
                'format'          => ['nullable', 'string', 'in:xlsx,csv'],
                'status'          => ['nullable', 'string', 'in:available,borrowed,maintenance,retired,attached,completed'],
                'category_id'     => ['nullable', 'string', 'exists:asset_categories,id'],
                'unit_id'         => ['nullable', 'string', 'exists:units,id'],
                'search'          => ['nullable', 'string', 'max:255'],
                'from'            => ['nullable', 'date'],
                'to'              => ['nullable', 'date', 'after_or_equal:from'],
            ]);

            // ── Build query (same filters as IndexAssetAction) ─────────────────
            $query = Asset::with(['category', 'unit', 'location', 'currentLoan']);

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            if ($categoryId = $request->query('category_id')) {
                $query->where('category_id', $categoryId);
            }

            if ($unitId = $request->query('unit_id')) {
                $query->where('unit_id', $unitId);
            }


            // Date range filter — berdasarkan purchased_at
            if ($from = $request->query('from')) {
                $query->whereDate('purchased_at', '>=', $from);
            }

            if ($to = $request->query('to')) {
                $query->whereDate('purchased_at', '<=', $to);
            }

            if ($search = $request->query('search')) {
                $pattern = '%' . $search . '%';
                $query->where(function ($builder) use ($pattern) {
                    $builder->where('name', 'like', $pattern)
                        ->orWhere('code', 'like', $pattern)
                        ->orWhere('serial_number', 'like', $pattern)
                        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', $pattern));
                });
            }

            $query->orderBy('created_at', 'desc');

            // ── Determine format & filename ────────────────────────────────────
            $format   = strtolower($request->query('format', 'xlsx'));
            $fileName = 'assets_export_' . Carbon::now()->format('Ymd_His');

            if ($format === 'csv') {
                return Excel::download(
                    new AssetExport($query),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );
            }

            return Excel::download(
                new AssetExport($query),
                $fileName . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );

        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith(
                'Validation Error',
                $e->errors(),
                HttpStatus::UnprocessableEntity
            );
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
