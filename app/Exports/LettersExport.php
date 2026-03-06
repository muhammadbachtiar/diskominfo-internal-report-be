<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LettersExport implements FromQuery, WithHeadings, WithMapping
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
            'Jenis Surat',
            'Nomor Surat',
            'Pengirim/Penerima',
            'Perihal',
            'Tahun',
            'Klasifikasi',
            'Deskripsi',
            'Tanggal',
            'Link Berkas',
        ];
    }

    public function map($letter): array
    {
        $typeLabel = '-';
        if ($letter->type === 'incoming') {
            $typeLabel = 'Surat Masuk';
        } elseif ($letter->type === 'outgoing') {
            $typeLabel = 'Surat Keluar';
        }

        return [
            $typeLabel,
            $letter->letter_number,
            $letter->sender_receiver,
            $letter->subject,
            $letter->year,
            $letter->classification ? $letter->classification->name : '-',
            $letter->description,
            $letter->date_of_letter,
            $letter->file_url ?? $letter->file_path ?? '-',
        ];
    }
}
