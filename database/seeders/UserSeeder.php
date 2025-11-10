<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Roles\Models\Roles;
use Infra\User\Models\User;
use Infra\User\Models\UserRoles\UserRoles;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('sekolahcerdas'),
        ]);

        // Ensure roles exist
        $super = Roles::firstOrCreate(['nama' => 'Super Admin']);
        $admin = Roles::firstOrCreate(['nama' => 'admin']);
        $kadin = Roles::firstOrCreate(['nama' => 'kadin']);
        $kabid = Roles::firstOrCreate(['nama' => 'kabid']);
        $pegawai = Roles::firstOrCreate(['nama' => 'pegawai']);

        // Attach admin roles to super admin user
        UserRoles::firstOrCreate(['user_id' => $user->id, 'roles_id' => $super->id]);
        UserRoles::firstOrCreate(['user_id' => $user->id, 'roles_id' => $admin->id]);
    }
}
