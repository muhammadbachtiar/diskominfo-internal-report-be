<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->toggleNullable(true);
    }

    public function down(): void
    {
        $this->toggleNullable(false);
    }

    private function toggleNullable(bool $nullable): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            if (! $nullable) {
                DB::statement("UPDATE report_evidences SET checksum = '' WHERE checksum IS NULL");
                DB::statement('UPDATE report_evidences SET lat = 0 WHERE lat IS NULL');
                DB::statement('UPDATE report_evidences SET lng = 0 WHERE lng IS NULL');
                DB::statement('UPDATE report_evidences SET accuracy = 0 WHERE accuracy IS NULL');
                DB::statement("UPDATE report_evidences SET geohash = '' WHERE geohash IS NULL");
            }

            $action = $nullable ? 'DROP' : 'SET';
            DB::statement("ALTER TABLE report_evidences ALTER COLUMN checksum {$action} NOT NULL");
            DB::statement("ALTER TABLE report_evidences ALTER COLUMN lat {$action} NOT NULL");
            DB::statement("ALTER TABLE report_evidences ALTER COLUMN lng {$action} NOT NULL");
            DB::statement("ALTER TABLE report_evidences ALTER COLUMN accuracy {$action} NOT NULL");
            DB::statement("ALTER TABLE report_evidences ALTER COLUMN geohash {$action} NOT NULL");

            return;
        }

        if ($driver === 'mysql') {
            if (! $nullable) {
                DB::statement("UPDATE report_evidences SET checksum = '' WHERE checksum IS NULL");
                DB::statement('UPDATE report_evidences SET lat = 0 WHERE lat IS NULL');
                DB::statement('UPDATE report_evidences SET lng = 0 WHERE lng IS NULL');
                DB::statement('UPDATE report_evidences SET accuracy = 0 WHERE accuracy IS NULL');
                DB::statement("UPDATE report_evidences SET geohash = '' WHERE geohash IS NULL");
            }

            $null = $nullable ? 'NULL' : 'NOT NULL';
            DB::statement("ALTER TABLE report_evidences MODIFY checksum VARCHAR(64) {$null}");
            DB::statement("ALTER TABLE report_evidences MODIFY lat DECIMAL(10,7) {$null}");
            DB::statement("ALTER TABLE report_evidences MODIFY lng DECIMAL(10,7) {$null}");
            DB::statement("ALTER TABLE report_evidences MODIFY accuracy DOUBLE {$null}");
            DB::statement("ALTER TABLE report_evidences MODIFY geohash VARCHAR(16) {$null}");

            return;
        }

        Schema::table('report_evidences', function (Blueprint $table) use ($nullable) {
            $table->string('checksum', 64)->nullable($nullable)->change();
            $table->decimal('lat', 10, 7)->nullable($nullable)->change();
            $table->decimal('lng', 10, 7)->nullable($nullable)->change();
            $table->float('accuracy')->nullable($nullable)->change();
            $table->string('geohash', 16)->nullable($nullable)->change();
        });
    }
};
