<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('id');
            $table->text('note')->nullable()->after('status');
            $table->foreign('category_id')
                ->references('id')
                ->on('asset_categories')
                ->onDelete('set null');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('id');
            $table->foreign('category_id')
                ->references('id')
                ->on('report_categories')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'note']);
        });
    }
};
