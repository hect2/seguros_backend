<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Office;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OfficesImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $district = District::where('code', $row['district_code'])->first();
        $user = User::where('email', $row['user_email'])->first();

        $district_id = $district ? $district->id : null;
        $user_id = $user ? $user->id : null;

        return new Office([
            'district_id' => $district_id ?? null,
            'user_id' => $user_id ?? null,
            'code' => $row['code'],
            'name' => $row['name'],
            'direction' => $row['direction'] ?? null,
            'phone' => $row['phone'] ?? null,
            'observations' => $row['observations'] ?? null,
            'status' => $row['status'] ?? 1,
        ]);
    }
    public function rules(): array
    {
        return [
            '*.district_code'=> ['nullable', 'string', 'exists:districts,code'],
            '*.user_email'   => ['nullable', 'sometimes','string', 'exists:users,email'],
            '*.code'         => ['required', 'string', 'max:20', 'unique:offices,code'],
            '*.name'         => ['required', 'string', 'max:255'],
            '*.direction'    => ['nullable', 'string', 'max:255'],
            '*.phone'        => ['nullable', 'max:20'],
            '*.observations' => ['nullable', 'string', 'max:500'],
            '*.status'       => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
