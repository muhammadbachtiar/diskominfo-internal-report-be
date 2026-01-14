<?php

namespace Domain\Asset\Actions\Attachment;

use Aws\S3\S3Client;
use Carbon\CarbonImmutable;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;

class PresignAttachmentAction extends Action
{
    public function __construct(private S3Client $s3)
    {
    }

    public function execute(Asset $asset, ?string $originalName = null, ?string $mime = null, ?int $size = null): array
    {
        CheckRolesAction::resolve()->execute('view-asset');

        // Check if asset can be modified
        if ($asset->status === 'retired') {
            throw new \RuntimeException('Cannot upload attachments to retired assets');
        }

        $bucket = config('filesystems.disks.s3.bucket') ?? env('AWS_BUCKET');
        if (! $bucket) {
            throw new \RuntimeException('S3 bucket is not configured');
        }

        $basePath = 'assets/' . $asset->id . '/attachments';
        $objectId = (string) Uuid::uuid7();
        $extension = $this->resolveExtension($originalName, $mime);
        $objectKey = $basePath . '/' . $objectId;
        if ($extension !== '') {
            $objectKey .= '.' . $extension;
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
            'image/webp' => 'webp',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
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