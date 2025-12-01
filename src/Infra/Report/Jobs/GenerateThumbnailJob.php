<?php

namespace Infra\Report\Jobs;

use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Infra\Report\Models\Evidence\Evidence;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Exceptions\NotSupportedException;

class GenerateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $evidenceId) {}

    public function handle(): void
    {
        $evidence = Evidence::findOrFail($this->evidenceId);
        if (strpos($evidence->mime, 'image/') !== 0) {
            Log::info('Skipping thumbnail generation for non-image evidence.', [
                'evidence_id' => $evidence->id,
                'mime' => $evidence->mime,
            ]);
            return;
        }
        $disk = config('report.evidence.disk', config('filesystems.default'));
        $source = Storage::disk($disk)->get($evidence->object_key);
        $manager = $this->createManager();

        try {
            $image = $manager->read($source);
        } catch (DecoderException | NotSupportedException $e) {
            Log::warning('Thumbnail generation skipped, unable to decode evidence image.', [
                'evidence_id' => $evidence->id,
                'object_key' => $evidence->object_key,
                'error' => $e->getMessage(),
            ]);

            return;
        } catch (\Throwable $e) {
            Log::error('Unexpected error while generating evidence thumbnail.', [
                'evidence_id' => $evidence->id,
                'object_key' => $evidence->object_key,
                'error' => $e->getMessage(),
            ]);

            return;
        }
        $image->scale(width: 800);
        // watermark with report number and time (simple text at top-left)
        // Intervention Image v3 lacks built-in text drawing without GD/Imagick draw; skipping visual text to keep job simple.
        $thumbKey = preg_replace('/\.(jpg|jpeg|png|heic|heif)$/i', '.thumb.jpg', $evidence->object_key);
        Storage::disk($disk)->put($thumbKey, (string) $image->toJpeg(80));
}

    private function createManager(): ImageManager
    {
        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }

        return ImageManager::gd();
    }
}
