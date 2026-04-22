<?php

namespace App\Imports;

use Domain\Asset\Actions\CRUD\CreateAssetAction;
use Infra\Asset\Models\AssetCategory;
use Infra\Asset\Models\Asset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AssetImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $importedRows = [];
    public array $failures = [];

    public function collection(Collection $rows)
    {
        $categories = AssetCategory::all()->keyBy('name');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowArray = $row->toArray();
            
            $namaAset = $rowArray['nama_aset'] ?? null;
            $kategoriName = $rowArray['kategori'] ?? null;
            $tahunPengadaan = $rowArray['tahun_pengadaan'] ?? null;
            $kode = $rowArray['kode'] ?? null;
            $nomorSeri = $rowArray['nomor_seri'] ?? null;

            if (empty($namaAset)) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'errors' => ['Nama Aset wajib diisi.']
                ];
                continue;
            }

            $categoryId = null;
            $categoryNameForDB = null;
            $categorySlug = null;
            
            if (!empty($kategoriName) && $categories->has($kategoriName)) {
                $category = $categories->get($kategoriName);
                $categoryId = $category->id;
                $categorySlug = $category->slug;
                $categoryNameForDB = $category->name;
            }

            if (empty($kode)) {
                $year = $tahunPengadaan ?: date('Y');
                
                $prefix = $categorySlug ? strtoupper(substr(str_replace('-', '', $categorySlug), 0, 3)) : 'AST';
                $codePrefix = $prefix . '-' . $year . '-';
                
                $counter = 1;
                $kode = $codePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);

                while (Asset::withTrashed()->where('code', $kode)->exists()) {
                    $counter++;
                    $kode = $codePrefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
                }
            } else {
                if (Asset::withTrashed()->where('code', $kode)->exists()) {
                   $this->failures[] = [
                        'row' => $rowNumber,
                        'errors' => ['Kode Aset "' . $kode . '" sudah dipakai database.']
                    ];
                    continue; 
                }
            }

            if (! empty($nomorSeri)) {
                if (Asset::withTrashed()->where('serial_number', $nomorSeri)->exists()) {
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'errors' => ['Nomor seri "' . $nomorSeri . '" sudah terdaftar di database.']
                    ];
                    continue;
                }
            }

            $purchasedAt = null;
            if ($tahunPengadaan) {
                $purchasedAt = $tahunPengadaan . '-01-01 00:00:00';
            }

            try {
                CreateAssetAction::resolve()->execute([
                    'name' => $namaAset,
                    'code' => $kode,
                    'category' => $categoryNameForDB,
                    'category_id' => $categoryId,
                    'serial_number' => $nomorSeri,
                    'purchased_at' => $purchasedAt,
                ]);

                $this->importedRows[] = [
                    'row' => $rowNumber,
                    'nama' => $namaAset,
                    'kode' => $kode
                ];
            } catch (\Exception $e) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'errors' => ['Gagal menyimpan: ' . $e->getMessage()]
                ];
            }
        }
    }
}
