<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Roles\Models\Roles;
use Infra\Shared\Models\Unit;
use Infra\User\Models\User;
use Infra\User\Models\UserRoles\UserRoles;

class RbacDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $unitA = Unit::first();
        $unitB = Unit::skip(1)->first() ?? $unitA;

        // Kadin (atasan tertinggi)
        $kadin = User::firstOrCreate(['email' => 'kadin@example.com'], [
            'name' => 'Kadin',
            'password' => bcrypt('password'),
            'unit_id' => $unitA?->id,
        ]);

        // Kabid
        $kabid1 = User::firstOrCreate(['email' => 'kabid1@example.com'], [
            'name' => 'Kabid 1',
            'password' => bcrypt('password'),
            'unit_id' => $unitA?->id,
        ]);
        $kabid2 = User::firstOrCreate(['email' => 'kabid2@example.com'], [
            'name' => 'Kabid 2',
            'password' => bcrypt('password'),
            'unit_id' => $unitB?->id,
        ]);

        // Pegawai
        $pegawai1 = User::firstOrCreate(['email' => 'pegawai1@example.com'], [
            'name' => 'Pegawai 1',
            'password' => bcrypt('password'),
            'unit_id' => $unitA?->id,
        ]);
        $pegawai2 = User::firstOrCreate(['email' => 'pegawai2@example.com'], [
            'name' => 'Pegawai 2',
            'password' => bcrypt('password'),
            'unit_id' => $unitA?->id,
        ]);
        $pegawai3 = User::firstOrCreate(['email' => 'pegawai3@example.com'], [
            'name' => 'Pegawai 3',
            'password' => bcrypt('password'),
            'unit_id' => $unitB?->id,
        ]);

        $roleKadin = Roles::firstOrCreate(['nama' => 'kadin']);
        $roleKabid = Roles::firstOrCreate(['nama' => 'kabid']);
        $rolePegawai = Roles::firstOrCreate(['nama' => 'pegawai']);

        UserRoles::firstOrCreate(['user_id' => $kadin->id, 'roles_id' => $roleKadin->id]);
        UserRoles::firstOrCreate(['user_id' => $kabid1->id, 'roles_id' => $roleKabid->id]);
        UserRoles::firstOrCreate(['user_id' => $kabid2->id, 'roles_id' => $roleKabid->id]);
        UserRoles::firstOrCreate(['user_id' => $pegawai1->id, 'roles_id' => $rolePegawai->id]);
        UserRoles::firstOrCreate(['user_id' => $pegawai2->id, 'roles_id' => $rolePegawai->id]);
        UserRoles::firstOrCreate(['user_id' => $pegawai3->id, 'roles_id' => $rolePegawai->id]);
    }
}

