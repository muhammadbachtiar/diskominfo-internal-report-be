<?php

namespace Infra\Report\Signing;

use Domain\Report\Signing\SignResult;
use Domain\Report\Signing\TteSignerInterface;
use Illuminate\Support\Facades\Storage;

class MockTteSigner implements TteSignerInterface
{
    public function requestSignature(string $pdfKey, string $signerNIK, string $reason): SignResult
    {
        // Mock: copy pdf to signed path and return fake cert data
        $disk = config('filesystems.default');
        $signedKey = preg_replace('/\.pdf$/', '.signed.pdf', $pdfKey);
        $content = Storage::disk($disk)->get($pdfKey);
        // We could embed a visible mark, but for mock just copy
        Storage::disk($disk)->put($signedKey, $content);
        $hash = hash('sha256', $content);
        return new SignResult(
            signedPdfKey: $signedKey,
            certSubject: 'CN=MOCK SIGNER, O=Org, C=ID',
            certSerial: 'MOCKSERIAL123',
            pdfHash: $hash,
            ocsp: ['status' => 'GOOD', 'source' => 'MOCK'],
            tsa: ['status' => 'N/A'],
            provider: 'mock'
        );
    }
}

