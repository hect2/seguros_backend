<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Office;
use App\Models\Position;
use App\Models\PositionType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *<
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // 🔹 Buscar IDs según valores del Excel
        $district = District::where('code', $row['distrito'])->first();
        $office = Office::where('code', $row['oficina'])->first();

        $adminType = PositionType::where('name', $row['cargo_administrativo'])->first();
        $operType = PositionType::where('name', $row['cargo_operativo'])->first();

        $status = EmployeeStatus::where('name', $row['estado'])->first();
        $status_defualt = EmployeeStatus::where('slug', 'inactive')->first();

        // 🔹 Crear empleado
        $employee = Employee::create([
            'full_name' => $row['nombre_completo'],
            'dpi' => $row['dpi'],
            'email' => $row['email'] ?? null,
            'phone' => $row['telefono'] ?? null,
            'status_id' => $status?->id ?? $status_defualt->id,
            'files' => [],
        ]);

        // 🔹 Crear posición administrativa (si existe)
        if ($adminType) {
            Position::create([
                'employee_id' => $employee->id,
                'office_id' => $office?->id,
                'district_id' => $district?->id,
                'initial_salary' => $row['sueldo_inicial'] ?? 0,
                'bonuses' => $row['bonificaciones'] ?? 0,
                'status' => 1,
                'type_id' => $adminType->id,
            ]);
        }

        // 🔹 Crear posición operativa (si existe)
        if ($operType) {
            Position::create([
                'employee_id' => $employee->id,
                'office_id' => $office?->id,
                'district_id' => $district?->id,
                'initial_salary' => $row['sueldo_inicial'] ?? 0,
                'bonuses' => $row['bonificaciones'] ?? 0,
                'status' => 1,
                'type_id' => $operType->id,
            ]);
        }

        return $employee;
    }

    public function rules(): array
    {
        return [
            '*.nombre_completo' => ['required', 'string', 'max:255'],
            '*.dpi' => ['required'],
            '*.email' => ['nullable', 'email'],
            '*.telefono' => ['nullable'],

            '*.distrito' => ['required', 'string'], // code of District
            '*.oficina' => ['required', 'string'], // code of Office

            '*.cargo_administrativo' => ['nullable', 'string'],
            '*.cargo_operativo' => ['nullable', 'string'],

            '*.sueldo_inicial' => ['nullable', 'numeric'],
            '*.bonificaciones' => ['nullable', 'numeric'],

            '*.estado' => ['required', 'string'], // name of EmployeeStatus
        ];
    }
}
