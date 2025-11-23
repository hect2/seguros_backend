<?php

namespace App\Reports\Services;

use App\Models\Employee;

class PNCReportService
{
    public function getData($filters)
    {
        $query = Employee::query()
                    ->leftjoin("positions","positions.employee_id","=","employees.id")
                    ->leftjoin("offices","offices.id","=","positions.office_id")
                    ->leftjoin("employee_statuses","employee_statuses.id","=","employees.status_id")
                    ;
        $query = ReportFilterService::apply($query, $filters);

        return $query->select('employees.full_name', 'employees.dpi', 'employee_statuses.name', 'offices.code', 'employees.created_at')->get();
    }
}
