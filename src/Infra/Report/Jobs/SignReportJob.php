<?php

namespace Infra\Report\Jobs;

use Domain\Report\Signing\TteSignerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Infra\Report\Models\Report;
use Infra\Report\Models\Signature;

class SignReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $reportId, public string $pdfKey) {}

    public function handle(TteSignerInterface $signer): void
    {
        $report = Report::findOrFail($this->reportId);
        $signerNik = config('tte.signer_nik');
        $result = $signer->requestSignature($this->pdfKey, $signerNik, 'Approval by Kabid');
        Signature::updateOrCreate(['report_id' => $report->id], [
            'provider' => $result->provider,
            'signed_pdf_key' => $result->signedPdfKey,
            'cert_subject' => $result->certSubject,
            'cert_serial' => $result->certSerial,
            'signed_at' => now(),
            'ocsp_status' => $result->ocsp,
            'tsa_timestamp' => $result->tsa,
            'pdf_hash' => $result->pdfHash,
        ]);
    }
}

