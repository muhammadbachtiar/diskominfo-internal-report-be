<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Add unit_id and employee fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('unit_id')->nullable()->after('id');
            $table->string('employee_id')->nullable()->after('email');
            $table->json('roles_cache')->nullable()->after('remember_token');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id','employee_id','roles_cache']);
        });
        Schema::dropIfExists('units');
    }
};

