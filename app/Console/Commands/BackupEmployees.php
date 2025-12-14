<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\EmployeeBackup;

class BackupEmployees extends Command
{
    protected $signature = 'employees:backup';
    protected $description = 'Genera un backup de todos los empleados y sus relaciones';

    public function handle()
    {
        $this->info("Iniciando respaldo de empleados...");

        $employees = Employee::with([
            'status:id,name',
            'trackings',
            'positions' => function ($q) {
                $q->first();
            },
            'positions.adminPositionType:id,name',
            'positions.operativePositionType:id,name',
            'positions.office:id,code',
            'positions.district:id,code',
        ])->get();

        foreach ($employees as $employee) {
            EmployeeBackup::create([
                'employee_id' => $employee->id,
                'data' => $employee->toArray(),  // snapshot completo
            ]);
        }

        $this->info("Respaldo completado: {$employees->count()} empleados guardados.");
        return Command::SUCCESS;
    }
}
