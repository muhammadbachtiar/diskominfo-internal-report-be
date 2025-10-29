<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_loans', function (Blueprint $table) {
            $table->string('pic_name')->nullable()->after('borrower_id');
            $table->text('note')->nullable()->after('location_name');
        });

        $this->makeCoordinatesNullable();
    }

    public function down(): void
    {
        $this->makeCoordinatesRequired();

        Schema::table('asset_loans', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'note']);
        });
    }

    private function makeCoordinatesNullable(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE asset_loans ALTER COLUMN loan_lat DROP NOT NULL');
            DB::statement('ALTER TABLE asset_loans ALTER COLUMN loan_long DROP NOT NULL');
        } else {
            DB::statement('ALTER TABLE asset_loans MODIFY loan_lat DECIMAL(10, 7) NULL');
            DB::statement('ALTER TABLE asset_loans MODIFY loan_long DECIMAL(10, 7) NULL');
        }
    }

    private function makeCoordinatesRequired(): void
    {
        DB::table('asset_loans')
            ->whereNull('loan_lat')
            ->update(['loan_lat' => 0]);

        DB::table('asset_loans')
            ->whereNull('loan_long')
            ->update(['loan_long' => 0]);

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE asset_loans ALTER COLUMN loan_lat SET NOT NULL');
            DB::statement('ALTER TABLE asset_loans ALTER COLUMN loan_long SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE asset_loans MODIFY loan_lat DECIMAL(10, 7) NOT NULL');
            DB::statement('ALTER TABLE asset_loans MODIFY loan_long DECIMAL(10, 7) NOT NULL');
        }
    }
};

