<?php

namespace App\Imports;

use App\Models\District;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DistrictsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new District([
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'status' => $row['status'] ?? 1,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.code' => ['required', 'string', 'max:20'],
            '*.name' => ['required', 'string', 'max:255'],
            '*.status' => ['nullable', 'integer'],
        ];
    }
}
