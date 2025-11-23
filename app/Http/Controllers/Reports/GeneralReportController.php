<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\Services\GeneralReportService;
use App\Reports\Exports\GeneralPdfExport;
use App\Reports\Exports\GeneralCsvExport;
use App\Reports\Exports\GeneralXlsxExport;
use Maatwebsite\Excel\Facades\Excel;

class GeneralReportController extends Controller
{
    public function __construct(
        private GeneralReportService $service
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
        return GeneralPdfExport::generate($data);
    }

    public function csv(Request $request)
    {
        $data = $this->service->getData($request->all());
        return GeneralCsvExport::download($data);
    }

    public function xlsx(Request $request)
    {
        $data = $this->service->getData($request->all());
        return Excel::download(new GeneralXlsxExport($data), 'General_Report.xlsx');
    }
}
