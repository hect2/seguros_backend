<?php

namespace App\Reports\Services;

use App\Models\Office;

class ClientTotalsService
{
    public function getData()
    {
        return Office::all()->map(function ($office) {
            return [
                'office' => $office->name,
                'banrural' => $office->employees()->where('client_id', 1)->count(),
                'others' => $office->employees()->where('client_id', '!=', 1)->count(),
                'available' => $office->employees()->where('status_id', 1)->count(),
                'reserve' => $office->employees()->where('status_id', 4)->count(),
                'total' => $office->employees()->count(),
            ];
        });
    }
}
