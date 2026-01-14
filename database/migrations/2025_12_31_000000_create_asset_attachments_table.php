<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            
            // File metadata
            $table->string('original_name');
            $table->string('file_category')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            
            // Storage
            $table->string('object_key')->unique();
            $table->string('checksum', 64);
            $table->string('storage_path', 500)->nullable();
            
            // Image metadata
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_compressed')->default(false);
            $table->unsignedBigInteger('original_size')->nullable();
            
            // Security
            $table->boolean('is_scanned')->default(false);
            $table->string('scan_status', 50)->nullable();
            $table->json('scan_result')->nullable();
            
            // Descriptive
            $table->json('tags')->nullable();
            
            $table->timestampsTz();
            $table->softDeletesTz();
            
            // Indexes
            $table->index(['asset_id', 'deleted_at']);
            $table->index('uploaded_by');
            $table->index('file_category');
            $table->index('created_at');
            $table->index('checksum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_attachments');
    }
};