<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentReportRequest;
use App\Services\IncidentReportService;
use Illuminate\Http\Request;

class ReportIncidentController extends Controller
{
    protected $service;

    public function __construct(IncidentReportService $service)
    {
        $this->service = $service;
    }

    public function index(IncidentReportRequest $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;

        $report = $this->service->generate($start, $end);

        return response()->json([
            'message' => 'Incident report generated successfully',
            'data' => $report
        ]);
    }
}
