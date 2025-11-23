<?php

namespace App\Reports\Services;

use App\Models\Office;

class OfficeSummaryService
{
    public function getSummary()
    {
        $offices = Office::all();

        return $offices->map(function ($office) {
            return [
                'office' => $office->name,
                'temporary_guards' => $office->employees()->where('status_id', 2)->count(),
                'suspended' => $office->employees()->where('status_id', 3)->count(),
                'insured_total' => $office->employees()->whereNotNull('dpi')->count(),
                'training' => $office->employees()->where('has_training', true)->count(),
            ];
        });
    }
}
