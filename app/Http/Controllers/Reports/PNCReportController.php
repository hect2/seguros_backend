<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\Services\PNCReportService;
use App\Reports\Exports\PNCReportPdf;
use App\Reports\Exports\PNCReportXlsx;
use Maatwebsite\Excel\Facades\Excel;

class PNCReportController extends Controller
{
    public function __construct(
        private PNCReportService $service
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->getData($request->all());

        return response()->json([
            'success' => true,
            'filters' => $request->all(),
            'data' => $data,
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->service->getData($request->all());
        $responsible = $request->responsible ?? null;

        return PNCReportPdf::generate($data, $responsible);
    }

    public function xlsx(Request $request)
    {
        $data = $this->service->getData($request->all());

        return Excel::download(new PNCReportXlsx($data), 'PNC_Report.xlsx');
    }
}
