<?php

namespace Domain\Report\Services;

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Infra\Report\Models\Report;

class PdfService
{
    public function generateSummaryPdf(Report $report, array $evidences, string $verificationUrl): string
    {
        $qr = QrCode::create($verificationUrl)->setSize(120);
        $writer = new PngWriter();
        $qrData = $writer->write($qr)->getString();
        $qrBase64 = 'data:image/png;base64,'.base64_encode($qrData);

        $html = view('pdf.report-summary', [
            'report' => $report,
            'evidences' => $evidences,
            'qr' => $qrBase64,
        ])->render();

        $dompdf = new Dompdf([ 'isRemoteEnabled' => true ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $hash = hash('sha256', $output);
        $path = 'reports/'.date('Y/m').'/'.$report->id.'/summary.pdf';
        Storage::disk(config('filesystems.default'))->put($path, $output);

        return $path;
    }
}

