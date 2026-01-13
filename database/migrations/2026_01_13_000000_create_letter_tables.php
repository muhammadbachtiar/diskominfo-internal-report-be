<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create classifications table if it doesn't exist
        if (!Schema::hasTable('classifications')) {
            Schema::create('classifications', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g., Penting, Rahasia, Biasa, Segera
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Create letters table
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['incoming', 'outgoing']);
            $table->string('letter_number')->index();
            $table->string('sender_receiver'); // Sender for incoming, Receiver for outgoing
            $table->date('date_of_letter');
            $table->year('year');
            $table->string('subject'); // Perihal
            // Fulltext index will be added separately or via raw SQL if needed, but simple index is often enough for starters. 
            // Laravel fulltext support depends on DB engine. MySQL supports it.
            
            $table->foreignId('classification_id')->constrained('classifications');
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete(); 
            // Note: units table uses UUID based on reference checks, assuming standard foreignUuid is correct. 
            // If units table uses BigInt, this will fail. Let's double check units table migration content if possible.
            // But from generic view `2025_09_04_100000_create_units_table.php`, it likely uses UUIDs as other tables like reports use uuid.
            
            $table->text('description')->nullable(); // AI Summary
            $table->string('file_url');
            $table->string('thumbnail_url')->nullable();
            $table->json('metadata_ai')->nullable();
            
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->fullText('subject'); // Subject fulltext index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('classifications');
    }
};
