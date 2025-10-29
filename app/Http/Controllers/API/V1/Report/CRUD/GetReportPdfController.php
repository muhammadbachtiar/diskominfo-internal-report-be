<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Infra\Shared\Controllers\BaseController;
use Illuminate\Support\Facades\Storage;
use Infra\Report\Models\Report;
use Infra\Report\Models\Signature;
use Infra\Shared\Enums\HttpStatus;

class GetReportPdfController extends BaseController
{
    public function __invoke(Report $report)
    {
        $sig = Signature::where('report_id', $report->id)->first();
        $disk = config('filesystems.default');
        $key = $sig?->signed_pdf_key;
        if (! $key) {
            return $this->resolveForFailedResponseWith(message:'PDF not available',status:HttpStatus::NotFound);
        }
        $content = Storage::disk($disk)->get($key);
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

