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
        
        // Check if report is approved
        if ($report->status !== 'approved') {
            throw new \RuntimeException('PDF is only available for approved reports. Current status: ' . $report->status);
        }
        
        $sig = Signature::where('report_id', $report->id)->first();
        if (! $sig || ! $sig->signed_pdf_key) {
            throw new \RuntimeException('PDF not available. The report may not have been signed yet.');
        }
        
        $disk = config('filesystems.default');
        $content = Storage::disk($disk)->get($sig->signed_pdf_key);
        
        // Generate filename based on report number
        $filename = $report->number
            ? str_replace('/', '-', $report->number) . '.pdf'
            : 'report-' . $report->id . '.pdf';
        
        return [
            'content' => $content,
            'filename' => $filename,
            'report' => $report
        ];
    }
}
