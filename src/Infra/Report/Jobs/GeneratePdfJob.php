<?php

namespace Infra\Report\Jobs;

use Domain\Report\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Infra\Report\Models\Report;
use Infra\Report\Models\Signature;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $reportId) {}

    public function handle(PdfService $pdfService): void
    {
        $report = Report::with('evidences')->findOrFail($this->reportId);
        $verificationUrl = url('/verify?number='.$report->number.'&hash='.($report->ver_hash ?? ''));
        $pdfKey = $pdfService->generateSummaryPdf($report, $report->evidences->all(), $verificationUrl);
        // Save placeholder signature row
        Signature::updateOrCreate(['report_id' => $report->id], [
            'provider' => config('tte.provider', 'mock'),
        ]);
        SignReportJob::dispatch($report->id, $pdfKey);
    }
}

