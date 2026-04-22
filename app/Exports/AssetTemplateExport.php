<?php

namespace App\Exports;

use Infra\Asset\Models\AssetCategory;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AssetTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Nama Aset',
            'Kategori',
            'Tahun Pengadaan',
            'Kode',
            'Nomor Seri',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Formatting lebar kolom
                $event->sheet->getColumnDimension('A')->setWidth(30);
                $event->sheet->getColumnDimension('B')->setWidth(25);
                $event->sheet->getColumnDimension('C')->setWidth(20);
                $event->sheet->getColumnDimension('D')->setWidth(20);
                $event->sheet->getColumnDimension('E')->setWidth(25);

                // Mengambil daftar Kategori untuk ditaruh di Dropdown Excel
                $categories = AssetCategory::pluck('name')->toArray();
                
                // Jika terlalu panjang, Excel data validation formula1 ada batas maksimal 255 karakter
                // Karena itu pastikan tidak lebih dari 255 karakter, atau gunakan sheet terpisah (advanced).
                // Di sini kita gunakan string sederhana disatukan koma.
                $categoryList = '"' . implode(',', array_slice($categories, 0, 15)) . '"';

                // Aplikasikan Data Validation (Dropdown) dari baris 2 s.d 500 pada Kolom B
                for ($i = 2; $i <= 500; $i++) {
                    $validation = $event->sheet->getCell('B' . $i)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input Error');
                    $validation->setError('Kategori harus dipilih dari list.');
                    $validation->setFormula1($categoryList);
                }
            },
        ];
    }
}
