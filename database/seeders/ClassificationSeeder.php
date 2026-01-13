<?php

namespace Database\Seeders;

use Infra\Letter\Models\Classification;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            ['name' => 'Biasa', 'description' => 'Surat rutin yang tidak memerlukan penanganan khusus.'],
            ['name' => 'Penting', 'description' => 'Surat yang memerlukan perhatian dan penanganan prioritas.'],
            ['name' => 'Rahasia', 'description' => 'Surat yang isinya hanya boleh diketahui oleh pejabat yang berwenang.'],
            ['name' => 'Segera', 'description' => 'Surat yang harus diselesaikan dalam waktu sesingkat-singkatnya.'],
            ['name' => 'Sangat Segera', 'description' => 'Surat yang harus diselesaikan / dikirimkan pada kesempatan pertama.'],
        ];

        foreach ($classifications as $classification) {
            Classification::firstOrCreate(
                ['name' => $classification['name']],
                ['description' => $classification['description']]
            );
        }
    }
}
