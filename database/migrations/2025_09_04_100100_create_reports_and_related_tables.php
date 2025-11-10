<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->float('accuracy')->nullable();
            $table->string('geohash', 16)->nullable();
            $table->timestamp('event_at')->nullable();
            $table->uuid('unit_id');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['draft','submitted','review','revision','approved','rejected'])->default('draft');
            $table->string('ver_hash', 64)->nullable(); // SHA-256 of content
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });

        Schema::create('report_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->enum('type', ['photo']); // image only per addendum
            $table->string('original_name');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->string('object_key')->unique();
            $table->string('checksum', 64); // SHA-256
            $table->json('exif')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->float('accuracy');
            $table->string('geohash', 16);
            $table->string('phash', 64)->nullable();
            $table->boolean('is_scanned')->default(false);
            $table->string('scan_status')->nullable(); // clean/infected/failed
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->foreignId('approver_id')->constrained('users');
            $table->enum('status', ['pending','approved','rejected','revision'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
        });

        Schema::create('signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('provider');
            $table->string('signed_pdf_key')->nullable();
            $table->string('signature_type')->default('PAdES');
            $table->string('cert_subject')->nullable();
            $table->string('cert_serial')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->json('ocsp_status')->nullable();
            $table->json('tsa_timestamp')->nullable();
            $table->string('pdf_hash', 64)->nullable();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->foreignId('user_id')->constrained('users');
            $table->text('body');
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('channel', ['email','wa','app']);
            $table->json('payload');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity');
            $table->string('entity_id');
            $table->json('diff_json')->nullable();
            $table->string('ip')->nullable();
            $table->string('ua')->nullable();
            $table->timestamps();
        });

        // unique to avoid duplicate evidences
        Schema::table('report_evidences', function (Blueprint $table) {
            $table->unique(['report_id', 'checksum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('signatures');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('report_evidences');
        Schema::dropIfExists('reports');
    }
};

