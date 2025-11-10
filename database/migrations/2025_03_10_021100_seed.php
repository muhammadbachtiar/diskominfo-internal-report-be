<?php

use Database\Seeders\PermissionSeeder;
use Database\Seeders\ReportSampleSeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RbacDemoUsersSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed base permissions and units first
        (new PermissionSeeder)->run();

        // Map role → permissions
        (new RolePermissionSeeder)->run();

        // Create super admin, then demo RBAC users
        (new UserSeeder)->run();   }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
