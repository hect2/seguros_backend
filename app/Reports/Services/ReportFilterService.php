<?php

namespace App\Reports\Services;

class ReportFilterService
{
    public static function apply($query, $filters)
    {
        if (!empty($filters['office_id'])) {
            $query->where('office_id', $filters['office_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }
}
