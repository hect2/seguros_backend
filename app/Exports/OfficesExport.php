<?php

namespace App\Exports;

use App\Models\Office;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OfficesExport implements FromCollection, WithHeadings
{
    public function __construct(public ?string $status = null, public ?string $search = null, public ?string $district_id = null) {}
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Office::query()
            ->leftJoin('districts', 'offices.district_id', '=', 'districts.id')
            ->leftJoin('users', 'offices.user_id', '=', 'users.id');

        if ($this->search !== null) {
            $query->where('offices.name', 'like', "%{$this->search}%")
                ->orWhere('offices.code', 'like', "%{$this->search}%");
        }

        if ($this->status !== null) {
            $query->where('offices.status', $this->status);
        }

        if ($this->district_id !== null) {
            $query->where('offices.district_id', $this->district_id);
        }

        return $query->select(
            'offices.code',
            'offices.name',
            'offices.direction',
            'offices.phone',
            'offices.observations',
            'offices.status',
            'districts.code as district_code',
            'users.email as user_email'
        )->get();
    }

    /**
     * Define los encabezados del archivo.
     */
    public function headings(): array
    {
        return [
            'District',
            'User',
            'Code',
            'Name',
            'Direction',
            'Phone',
            'Observations',
            'Status',
        ];
    }
}
