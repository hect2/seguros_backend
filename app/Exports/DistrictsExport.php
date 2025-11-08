<?php

namespace App\Exports;

use App\Models\District;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DistrictsExport implements FromCollection, WithHeadings
{
    public function __construct(public ?string $status = null, public ?string $search = null) {}
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = District::query();

        if ($this->status !== null) {
            $query->where('status', $this->status);
        }
        if ($this->search !== null) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%");
        }

        return $query->select('code', 'name', 'description', 'status')->get();
    }

    /**
     * Define los encabezados del archivo.
     */
    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Description',
            'Status',
        ];
    }
}
