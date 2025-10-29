<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_actions')) {
            Schema::create('report_actions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('report_id');
                $table->string('title');
                $table->text('note')->nullable();
                $table->integer('sequence');
                $table->timestamps();
                $table->foreign('report_id')
                    ->references('id')
                    ->on('reports')
                    ->onDelete('cascade');
            });
        }

        Schema::table('report_evidences', function (Blueprint $table) {
            if (!Schema::hasColumn('report_evidences', 'action_id')) {
                $table->uuid('action_id')->nullable()->after('report_id');
                $table->foreign('action_id')
                    ->references('id')
                    ->on('report_actions')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_evidences', function (Blueprint $table) {
            $table->dropForeign(['action_id']);
            $table->dropColumn('action_id');
        });

        Schema::dropIfExists('report_actions');
    }
};
