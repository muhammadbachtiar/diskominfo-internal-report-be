<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Shared\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::firstOrCreate(['code' => 'BIDA'], ['name' => 'Bidang A']);
        Unit::firstOrCreate(['code' => 'BIDB'], ['name' => 'Bidang B']);
    }
}

