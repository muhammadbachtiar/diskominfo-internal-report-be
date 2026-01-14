<?php

namespace Infra\Asset\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AssetAttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'original_name' => $this->original_name,
            'file_category' => $this->file_category,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'human_file_size' => $this->human_file_size,
            'object_key' => $this->object_key,
            'checksum' => $this->checksum,
            
            // Image metadata
            'width' => $this->width,
            'height' => $this->height,
            'is_compressed' => $this->is_compressed,
            'original_size' => $this->original_size,
            
            // Security
            'is_scanned' => $this->is_scanned,
            'scan_status' => $this->scan_status,
            'is_clean' => $this->isScanClean(),
            'has_threats' => $this->hasScanThreats(),
            
            // Tags
            'tags' => $this->tags ?? [],
            
            // File type helpers
            'is_image' => $this->isImage(),
            'is_pdf' => $this->isPdf(),
            'is_document' => $this->isDocument(),
            
            // Uploader
            'uploaded_by' => [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
                'email' => $this->uploader->email,
            ],
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Temporary download URL (automatically generated via accessor)
            'url' => $this->url,
        ];
    }
}