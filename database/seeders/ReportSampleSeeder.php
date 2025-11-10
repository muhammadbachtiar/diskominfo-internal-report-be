<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Report\Models\Report;
use Infra\Shared\Models\Unit;

class ReportSampleSeeder extends Seeder
{
    public function run(): void
    {
        $unit = Unit::first();
        for ($i=1; $i<=5; $i++) {
            Report::firstOrCreate([
                'title' => 'Contoh Laporan '.$i,
            ], [
                'id' => (string) \Ramsey\Uuid\Uuid::uuid7(),
                'number' => 'LAP/'.$unit->code.'/'.date('Y').'/'.str_pad($i,5,'0',STR_PAD_LEFT),
                'description' => 'Deskripsi laporan contoh',
                'category' => 'umum',
                'location' => 'Muara Enim',
                'lat' => -3.7,
                'lng' => 103.8,
                'accuracy' => 10,
                'geohash' => 'qqqqqqqqq',
                'unit_id' => $unit->id,
                'created_by' => 1,
                'status' => ['draft','submitted','review','revision','approved'][($i-1)%5],
            ]);
        }
    }
}

