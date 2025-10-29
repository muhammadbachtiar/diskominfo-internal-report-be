<?php

namespace Domain\Report\Signing;

class SignResult
{
    public function __construct(
        public readonly string $signedPdfKey,
        public readonly ?string $certSubject,
        public readonly ?string $certSerial,
        public readonly ?string $pdfHash,
        public readonly ?array $ocsp,
        public readonly ?array $tsa,
        public readonly ?string $provider = 'mock'
    ) { }
}

interface TteSignerInterface
{
    public function requestSignature(string $pdfKey, string $signerNIK, string $reason): SignResult;
}

