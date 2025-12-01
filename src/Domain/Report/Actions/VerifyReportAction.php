<?php

namespace Domain\Report\Actions;

use Infra\Report\Models\Report;
use Infra\Report\Models\Signature;
use Infra\Shared\Foundations\Action;

class VerifyReportAction extends Action
{
    public function execute(?string $number, ?string $hash): array
    {
        $report = Report::where('number', $number)->first();
        if (! $report) {
            throw new \RuntimeException('Not Found');
        }
        $sig = Signature::where('report_id', $report->id)->first();
        return [
            'number' => $report->number,
            'doc_hash' => $sig?->pdf_hash,
            'issuer' => $sig?->cert_subject,
            'serial' => $sig?->cert_serial,
            'status' => $sig ? 'VALID (MOCK)' : 'NOT_SIGNED',
            'coords' => [
                'lat' => $report->lat,
                'lng' => $report->lng,
                'accuracy' => $report->accuracy,
            ],
        ];
    }
}

