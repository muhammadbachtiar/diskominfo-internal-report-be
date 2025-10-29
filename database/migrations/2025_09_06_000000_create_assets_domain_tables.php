<?php

use Domain\Asset\Enums\AssetStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default(AssetStatus::Available->value);
            $table->string('category')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->timestampTz('purchased_at')->nullable();
            $table->foreignUuid('unit_id')->nullable()->constrained('units');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('asset_loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->unsignedBigInteger('borrower_id');
            $table->decimal('loan_lat', 10, 7);
            $table->decimal('loan_long', 10, 7);
            $table->string('location_name')->nullable();
            $table->timestampTz('borrowed_at');
            $table->timestampTz('returned_at')->nullable();
            $table->timestampsTz();

            $table->foreign('borrower_id')->references('id')->on('users');
        });

        Schema::create('asset_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_loan_id')->constrained('asset_loans')->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name')->nullable();
            $table->timestampsTz();
        });

        Schema::create('asset_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('status_key');
            $table->timestampTz('changed_at');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('note')->nullable();
            $table->timestampsTz();

            $table->foreign('changed_by')->references('id')->on('users');
        });

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->text('description');
            $table->text('note')->nullable();
            $table->text('completion_note')->nullable();
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->string('performed_by_name')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->boolean('return_to_active_location')->default(true);
            $table->timestampsTz();

            $table->foreign('performed_by_id')->references('id')->on('users');
            $table->foreign('completed_by')->references('id')->on('users');
        });

        Schema::create('report_assets', function (Blueprint $table) {
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->timestampsTz();
            $table->primary(['report_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_assets');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_status_histories');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_loans');
        Schema::dropIfExists('assets');
    }
};
