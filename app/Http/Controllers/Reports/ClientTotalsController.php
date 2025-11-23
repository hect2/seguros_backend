<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\Services\ClientTotalsService;
use App\Reports\Exports\ClientTotalsXlsx;
use Maatwebsite\Excel\Facades\Excel;

class ClientTotalsController extends Controller
{
    public function __construct(
        private ClientTotalsService $service
    ) {}

    public function index()
    {
        $data = $this->service->getData();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function xlsx()
    {
        $data = $this->service->getData();
        return Excel::download(new ClientTotalsXlsx($data), 'Client_Totals.xlsx');
    }
}
