<?php

namespace Domain\Report\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;
use Aws\S3\S3Client;
use Carbon\CarbonImmutable;

class PresignEvidenceAction extends Action
{
    public function __construct(private S3Client $s3)
    {
    }

    public function execute(Report $report, ?string $originalName = null, ?string $mime = null, ?int $size = null): array
    {
        CheckRolesAction::resolve()->execute('presign-evidence');

        if (! in_array($report->status, ['draft','revision'])) {
            throw new \RuntimeException('Evidence can only be uploaded for draft or revision reports');
        }

        $bucket = config('report.evidence.bucket')
            ?? config('filesystems.disks.s3.bucket')
            ?? env('AWS_BUCKET');
        if (! $bucket) {
            throw new \RuntimeException('Evidence bucket is not configured');
        }

        $prefix = trim((string) config('report.evidence.prefix', ''), '/');
        $basePath = implode('/', array_filter([$prefix, 'evidences', $report->id]));
        $objectId = (string) Uuid::uuid7();
        $extension = $this->resolveExtension($originalName, $mime);
        $objectKey = $basePath.'/'.$objectId;
        if ($extension !== '') {
            $objectKey .= '.'.$extension;
        }

        $expiresAt = CarbonImmutable::now()->addMinutes(5);

        $params = [
            'Bucket' => $bucket,
            'Key' => $objectKey,
        ];

        if ($mime) {
            $params['ContentType'] = $mime;
        }

        $command = $this->s3->getCommand('PutObject', $params);
        $request = $this->s3->createPresignedRequest($command, '+5 minutes');

        return [
            'url' => (string) $request->getUri(),
            'object_key' => $objectKey,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    private function resolveExtension(?string $originalName, ?string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
        ];

        if ($originalName) {
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($ext !== '') {
                $sanitized = preg_replace('/[^a-z0-9]/', '', $ext);
                if ($sanitized !== '') {
                    return $sanitized;
                }
            }
        }

        if ($mime) {
            $lower = strtolower($mime);
            if (isset($map[$lower])) {
                return $map[$lower];
            }
        }

        return 'bin';
    }
}
