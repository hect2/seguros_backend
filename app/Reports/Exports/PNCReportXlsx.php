<?php

namespace App\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PNCReportXlsx implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        // Expect a Collection or an array of arrays/objects
        $this->rows = collect($rows)->map(function ($r) {
            // Ensure associative array
            if (is_object($r)) return (array) $r;
            return $r;
        });
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        // Derive headings from the first row keys
        $first = $this->rows->first();
        if (!$first) return [];
        return array_keys((array) $first);
    }
}
