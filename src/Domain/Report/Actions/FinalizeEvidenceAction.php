<?php

namespace Domain\Report\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Auth;
use Infra\Report\Jobs\GenerateThumbnailJob;
use Infra\Report\Jobs\ScanEvidenceJob;
use Infra\Report\Models\Evidence\Evidence;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;

class FinalizeEvidenceAction extends Action
{
    public function execute(Report $report, string $objectKey, ?string $originalName, ?string $mime, ?int $size): Evidence
    {
        CheckRolesAction::resolve()->execute('finalize-evidence');
        if (! in_array($report->status, ['draft','revision'])) {
            throw new \RuntimeException('Evidence can only be uploaded for draft or revision reports');
        }

        $name = $originalName ?: basename($objectKey) ?: 'evidence';
        $mimeType = $mime ?: 'application/octet-stream';
        $fileSize = $size ?? 0;

        $evidence = Evidence::create([
            'id' => (string) Uuid::uuid7(),
            'report_id' => $report->id,
            'type' => 'photo',
            'original_name' => $name,
            'mime' => $mimeType,
            'size' => $fileSize,
            'object_key' => $objectKey,
            'checksum' => null,
            'exif' => null,
            'lat' => null,
            'lng' => null,
            'accuracy' => null,
            'geohash' => null,
            'phash' => null,
            'uploaded_by' => Auth::id(),
        ]);

        ScanEvidenceJob::dispatch($evidence->id);
        GenerateThumbnailJob::dispatch($evidence->id);

        return $evidence;
    }
}
