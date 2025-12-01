<?php

namespace Domain\Report\Actions;

use Illuminate\Support\Facades\Storage;
use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\Signature;
use Infra\Shared\Foundations\Action;

class GetReportPdfAction extends Action
{
    public function execute(Report $report): array
    {
        CheckRolesAction::resolve()->execute('view-report');
        $sig = Signature::where('report_id', $report->id)->first();
        if (! $sig || ! $sig->signed_pdf_key) {
            throw new \RuntimeException('PDF not available');
        }
        $disk = config('filesystems.default');
        $content = Storage::disk($disk)->get($sig->signed_pdf_key);
        return ['content' => $content, 'filename' => basename($sig->signed_pdf_key)];
    }
}
