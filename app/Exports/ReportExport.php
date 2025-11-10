<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    public function __construct(protected Collection $rows) {}

    public function collection()
    {
        return $this->rows->map(fn($r) => [
            $r->number, $r->title, $r->status, $r->created_at,
        ]);
    }

    public function headings(): array
    {
        return ['number','title','status','created_at'];
    }
}

