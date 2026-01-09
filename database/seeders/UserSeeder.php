<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Roles\Models\Roles;
use Infra\User\Models\User;
use Infra\User\Models\UserRoles\UserRoles;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin user
        $superAdminUser = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('sekolahcerdas'),
        ]);

        // Create Kadin user
        $kadinUser = User::firstOrCreate([
            'email' => 'kadin@admin.com',
        ], [
            'name' => 'Kepala Dinas',
            'password' => bcrypt('Kominfo12345'),
        ]);

        $super = Roles::firstOrCreate(['nama' => 'Super Admin']);
        $admin = Roles::firstOrCreate(['nama' => 'admin']);
        $kadin = Roles::firstOrCreate(['nama' => 'kadin']);

        UserRoles::firstOrCreate(['user_id' => $superAdminUser->id, 'roles_id' => $super->id]);
        UserRoles::firstOrCreate(['user_id' => $superAdminUser->id, 'roles_id' => $admin->id]);

        UserRoles::firstOrCreate(['user_id' => $kadinUser->id, 'roles_id' => $kadin->id]);
    }
}
