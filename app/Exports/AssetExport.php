<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Nomor Seri',
            'Status',
            'Harga Perolehan',
            'Tanggal Pengadaan',
            'Lokasi',
            'Unit',
            'Dibuat Pada',
        ];
    }

    public function map($asset): array
    {
        $statusLabel = match ($asset->status) {
            'available'   => 'Tersedia',
            'borrowed'    => 'Dipinjam',
            'maintenance' => 'Perawatan',
            'retired'     => 'Pensiun',
            'attached'    => 'Terlampir',
            'completed'   => 'Selesai',
            default       => $asset->status,
        };

        return [
            $asset->code,
            $asset->name,
            $asset->category ? $asset->category->name : ($asset->category ?? '-'),
            $asset->serial_number ?? '-',
            $statusLabel,
            $asset->purchase_price !== null ? number_format((float) $asset->purchase_price, 2, ',', '.') : '-',
            $asset->purchased_at ? $asset->purchased_at->format('d/m/Y') : '-',
            optional($asset->location)->name ?? '-',
            optional($asset->unit)->name ?? '-',
            $asset->created_at ? $asset->created_at->format('d/m/Y H:i') : '-',
        ];
    }
}
