<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\Services\DigesspService;
use App\Reports\Exports\DigesspXlsx;
use Maatwebsite\Excel\Facades\Excel;

class DigesspReportController extends Controller
{
    public function __construct(
        private DigesspService $service
    ) {}

    public function index()
    {
        $data = $this->service->getReport();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function xlsx()
    {
        $data = $this->service->getReport();
        return Excel::download(new DigesspXlsx($data), 'Digessp_Report.xlsx');
    }
}
