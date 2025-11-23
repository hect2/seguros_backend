<?php

namespace App\Reports\Services;

use App\Models\Office;

class DigesspService
{
    public function getReport()
    {
        return Office::all()->map(function ($office) {
            $total = $office->certificates()->count();
            $valid = $office->certificates()->where('expiry_date', '>', now())->count();
            $expired = $office->certificates()->where('expiry_date', '<=', now())->count();
            $no_cert = $office->employees()->doesntHave('certificate')->count();

            return [
                'office' => $office->name,
                'total' => $total,
                'valid' => $valid,
                'valid_percentage' => $total ? round($valid / $total * 100, 2) : 0,
                'expired' => $expired,
                'missing_certificate' => $no_cert,
            ];
        });
    }
}
