<?php

namespace Domain\Letter\Actions;

use Infra\Letter\Models\Letter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Spatie\PdfToImage\Pdf;
use Infra\Shared\Foundations\Action;

class CreateLetterAction extends Action
{
    public function execute(array $data, UploadedFile $file): Letter
    {
        // 1. Upload Main File
        $uuid = (string) Str::uuid();
        $directory = "letters/{$uuid}";
        
        // Use default disk or s3 explicitly if needed. 
        // Recommending using the configured default or 'public' for easier access if S3 not set.
        // If the environment is set up with MinIO/S3 as 's3' disk, we should use it.
        // We'll check if 's3' is configured, otherwise fallback to 'public'.
        $diskName = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        $disk = Storage::disk($diskName);
        
        $path = $disk->putFileAs($directory, $file, $uuid . '.' . $file->getClientOriginalExtension());
        $fileUrl = $disk->url($path);

        // 2. Generate and Upload Thumbnail
        $thumbnailUrl = null;
        try {
            $thumbnailContent = $this->generateThumbnail($file);
            if ($thumbnailContent) {
                $thumbName = $uuid . '_thumb.jpg';
                $thumbPath = $directory . '/' . $thumbName;
                $disk->put($thumbPath, $thumbnailContent);
                $thumbnailUrl = $disk->url($thumbPath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Thumbnail generation failed for letter {$uuid}: " . $e->getMessage());
        }

        // 3. Create Record
        return Letter::create([
            'type' => $data['type'],
            'letter_number' => $data['letter_number'],
            'sender_receiver' => $data['sender_receiver'],
            'date_of_letter' => $data['date_of_letter'],
            'year' => $data['year'],
            'subject' => $data['subject'],
            'classification_id' => $data['classification_id'],
            'unit_id' => $data['unit_id'] ?? null,
            'description' => $data['description'] ?? null,
            'file_url' => $fileUrl,
            'thumbnail_url' => $thumbnailUrl,
            'metadata_ai' => $data['metadata_ai'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }

    private function generateThumbnail(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        $tempPath = $file->getPathname();
        
        // If Image
        if (str_starts_with($mime, 'image/')) {
            // Using Intervention Image v3
            // Ensure we use the correct driver available in the container (Imagick is installed)
            $manager = ImageManager::imagick(); 
            return $manager->read($tempPath)->scale(width: 300)->toJpeg()->toString();
        }

        // If PDF
        if ($mime === 'application/pdf') {
            if (class_exists(Pdf::class)) {
                try {
                    // Spatie PDF to Image
                    $pdf = new Pdf($tempPath);
                    
                    // Render first page as image
                    // We need to save to a temp file because v2 saveImage writes to disk
                    $tempOutput = tempnam(sys_get_temp_dir(), 'thumb_') . '.jpg';
                    
                    $pdf->setPage(1)
                        ->saveImage($tempOutput);
                    
                    if (file_exists($tempOutput)) {
                        $content = file_get_contents($tempOutput);
                        unlink($tempOutput);
                        
                        // Resize the generated image using Intervention
                        $manager = ImageManager::imagick();
                        return $manager->read($content)->scale(width: 300)->toJpeg()->toString();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("PDF thumbnail generation error: " . $e->getMessage());
                    throw $e;
                }
            } else {
                 \Illuminate\Support\Facades\Log::warning("Spatie\PdfToImage\Pdf class not found.");
            }
        }

        return null;
    }
}
