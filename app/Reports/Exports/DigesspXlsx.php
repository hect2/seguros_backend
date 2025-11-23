<?php

namespace App\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DigesspXlsx implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = collect($rows)->map(fn($r) => is_object($r) ? (array)$r : $r);
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['office','total','valid','valid_percentage','expired','missing_certificate'];
    }
}
