<?php

namespace Domain\Report\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class EvidenceService
{
    public function __construct(
        protected S3Client $s3,
    ) {
    }

    public function presignUpload(string $bucket, string $key, string $mime, int $maxSizeBytes = 20_000_000, int $expires = 900): array
    {
        // Only allow image types
        $allowed = ['image/jpeg','image/png','image/heic','image/heif'];
        if (! in_array(strtolower($mime), $allowed)) {
            throw new \InvalidArgumentException('Only image uploads are allowed');
        }

        // Presign PUT (simple approach)
        $cmd = $this->s3->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key'    => $key,
            'ContentType' => $mime,
        ]);
        $request = $this->s3->createPresignedRequest($cmd, "+{$expires} seconds");
        return [
            'url' => (string) $request->getUri(),
            'headers' => [
                'Content-Type' => $mime,
            ],
            'method' => 'PUT',
            'key' => $key,
        ];
    }

    public function extractExifAndHash(string $tmpFile): array
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($tmpFile) : [];
        $lat = $lng = $acc = null;
        if ($exif) {
            [$lat, $lng] = $this->getGps($exif);
            $acc = $exif['GPSHPositioningError'] ?? ($exif['GPSHPositioningErrorRef'] ?? null);
            if (is_array($acc)) { $acc = null; }
            if ($acc) { $acc = floatval($acc); }
        }
        // pHash (best effort)
        $phash = null;
        try {
            $hasher = new ImageHash(new DifferenceHash());
            $phash = $hasher->hash($tmpFile)->toHex();
        } catch (\Throwable $e) {
            Log::warning('Failed to generate perceptual hash for evidence', [
                'file' => $tmpFile,
                'error' => $e->getMessage(),
            ]);
        }

        // sha256
        $checksum = hash_file('sha256', $tmpFile);

        return [
            'exif' => $exif ?: null,
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $acc,
            'phash' => $phash,
            'checksum' => $checksum,
        ];
    }

    private function getGps($exif): array
    {
        if (! isset($exif['GPSLatitude'], $exif['GPSLongitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitudeRef'])) {
            return [null, null];
        }
        $lat = $this->convertToFloat($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
        $lng = $this->convertToFloat($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
        return [$lat, $lng];
    }

    private function convertToFloat($coord, $hemisphere)
    {
        $degrees = count($coord) > 0 ? $this->gps2Num($coord[0]) : 0;
        $minutes = count($coord) > 1 ? $this->gps2Num($coord[1]) : 0;
        $seconds = count($coord) > 2 ? $this->gps2Num($coord[2]) : 0;

        $flip = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 1) {
            return (float) $parts[0];
        }
        return floatval($parts[0]) / floatval($parts[1]);
    }
}
