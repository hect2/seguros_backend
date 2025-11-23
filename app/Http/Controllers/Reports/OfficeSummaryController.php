<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\Services\OfficeSummaryService;
use App\Reports\Exports\OfficeSummaryXlsx;
use Maatwebsite\Excel\Facades\Excel;

class OfficeSummaryController extends Controller
{
    public function __construct(
        private OfficeSummaryService $service
    ) {}

    public function index()
    {
        $data = $this->service->getSummary();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function xlsx()
    {
        $data = $this->service->getSummary();
        return Excel::download(new OfficeSummaryXlsx($data), 'Office_Summary.xlsx');
    }
}
