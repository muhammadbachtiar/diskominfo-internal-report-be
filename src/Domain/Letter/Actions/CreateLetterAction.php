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
        // Using 'public' visibility or default based on config. 
        // Report evidence uses: 'evidences/{id}/{uuid}.ext'.
        // We'll use 'letters/{uuid}/{uuid}.ext'.
        
        $uuid = (string) Str::uuid();
        $directory = "letters/{$uuid}";
        
        // Upload file
        // We use putFileAs to control the name, or just putFile.
        // Assuming 's3' disk is the default or configured one.
        $disk = Storage::disk('s3');
        $path = $disk->putFileAs($directory, $file, $uuid . '.' . $file->getClientOriginalExtension());
        $fileUrl = $disk->url($path); // Or just store path if preferred, user asked for 'file_url'.

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
            // Log error but don't fail the whole request
            \Illuminate\Support\Facades\Log::warning("Thumbnail generation failed for letter: " . $e->getMessage());
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
            'file_url' => $fileUrl, // Store full URL from MinIO/S3
            'thumbnail_url' => $thumbnailUrl, // Store full URL from MinIO/S3 (already using url() method)
            'metadata_ai' => $data['metadata_ai'] ?? null,
            'created_by' => $data['created_by'],
        ]);
    }

    private function generateThumbnail(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        
        // If Image
        if (str_starts_with($mime, 'image/')) {
            // Using Intervention Image v3
            $manager = extension_loaded('imagick') ? ImageManager::imagick() : ImageManager::gd();
            return $manager->read($file->getPathname())->scale(width: 300)->toJpeg()->toString();
        }

        // If PDF
        if ($mime === 'application/pdf') {
            if (class_exists(Pdf::class)) {
                // Spatie PDF to Image
                $pdf = new Pdf($file->getPathname());
                $pdf->setPage(1);
                // Return content. Spatie saves to file. 
                // We might need to save to temp file then read.
                $tempObj = tempnam(sys_get_temp_dir(), 'thumb');
                $pdf->saveImage($tempObj);
                $content = file_get_contents($tempObj);
                unlink($tempObj);
                return $content;
            }
        }

        return null; // Not supported
    }
}
