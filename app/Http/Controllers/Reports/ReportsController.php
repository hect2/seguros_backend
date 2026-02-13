<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\District;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    /**
     * Generate a report based on the given criteria.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string|in:summary_by_office,digessp_certifications,totals_by_client,global_distribution_by_region,distribution_by_region,day_summary,week_summary,fifteen_summary,all_summary',
            'format' => 'nullable|string|in:json,pdf,xlsx,csv',
            'office_id' => 'nullable|exists:offices,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $reportType = $request->input('report_type');
        $format = $request->input('format', 'json');

        $data = [];

        switch ($reportType) {
            case 'summary_by_office':
                $data = $this->getSummaryByOffice($request);
                break;
            case 'digessp_certifications':
                $data = $this->getCertificationDigessp($request);
                break;
            case 'totals_by_client':
                $data = $this->getTotalsByClient($request);
                break;
            case 'day_summary':
                $data = $this->getDailySummary($request);
                break;
            case 'week_summary':
                $data = $this->getDailySummary($request, '7');
                break;
            case 'fifteen_summary':
                $data = $this->getDailySummary($request, '15');
                break;
            case 'all_summary':
                $data = $this->getDailySummary($request, 'all');
                break;
        }

        if ($format === 'json') {
            return response()->json($data);
        }

        return $this->export($data, $format, $reportType);
    }

    private function getDailySummary(Request $request, string $days = '1')
    {
        $query = Employee::query();

        $user = Auth::user();

        if (!$user->hasRole('Super Administrador')) {
            if (!empty($user->office) && is_array($user->office)) {
                $officeId = $user->office[0];

                $query->whereHas('positions', function ($q) use ($officeId) {
                    $q->where('office_id', $officeId)
                        ->orderBy('created_at')
                        ->limit(1);
                });
            }
        }
        // $employees = $query
        //     ->with([
        //         'status:id',
        //         'lastHistory'
        //     ])
        //     ->get();
        // return response()->json($employees);

        $counters = $this->countDailyStatusChanges($query, $days);

        return [
            'daily_active_employees' => $counters['active'],
            'daily_inactive_employees' => $counters['inactive'],
            'daily_suspended_employees' => $counters['suspended'],
            'daily_insured_employees' => $counters['insured'],
            'daily_accredited_employees' => $counters['accredited'],
        ];
    }

    private function countDailyStatusChanges($employeeQuery, string $days): array
    {
        $statusId_active = EmployeeStatus::where('slug', 'active')->first()->id;
        $statusId_inactive = EmployeeStatus::where('slug', 'inactive')->first()->id;
        $statusId_suspended = EmployeeStatus::where('slug', 'suspended')->first()->id;
        $statusId_insured = EmployeeStatus::where('slug', 'insured')->first()->id;
        $statusId_accredited = EmployeeStatus::where('slug', 'accredited')->first()->id;

        $count_active = 0;
        $count_inactive = 0;
        $count_suspended = 0;
        $count_insured = 0;
        $count_accredited = 0;

        $employees = $employeeQuery
            ->with([
                'backups' => function ($q) use ($days) {
                    if ($days !== 'all') {
                        $q->where('created_at', '>=', now()->subDays((int) $days));
                    }
                    $q->latest();
                }
            ])
            ->get();

        foreach ($employees as $employee) {
            // 🔥 ahora sí usamos lo que cargamos
            $backup = $employee->backups->first();

            if (!$backup) {
                continue;
            }

            $previousStatusId = $backup->data['status_id'] ?? 0;
            $currentStatusId = $employee->status_id;
            Log::error("EmployeeID : " . $employee->id . " yesterdayStatusId: " . $previousStatusId . " todayStatusId: " . $currentStatusId);

            if ($previousStatusId !== $currentStatusId) {

                if ($currentStatusId === $statusId_active) {
                    $count_active++;
                } elseif ($currentStatusId === $statusId_suspended) {
                    $count_suspended++;
                } elseif ($currentStatusId === $statusId_insured) {
                    $count_insured++;
                } elseif ($currentStatusId === $statusId_accredited) {
                    $count_accredited++;
                } elseif ($currentStatusId === $statusId_inactive) {
                    $count_inactive++;
                }
            }
        }

        return [
            'active' => $count_active,
            'inactive' => $count_inactive,
            'suspended' => $count_suspended,
            'insured' => $count_insured,
            'accredited' => $count_accredited,
        ];
    }

    /**
     * Reporte resumen por oficina.
     */
    private function getSummaryByOffice(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Resolver rango de fechas
        |--------------------------------------------------------------------------
        */
        if ($request->filled('start_date') || $request->filled('end_date')) {

            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::parse($request->end_date)->startOfDay();

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::parse($request->start_date)->endOfDay();

        } else {
            // Default: hoy
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        // Placeholder status IDs. The user will replace these.
        $temporalGuardsStatusId = EmployeeStatus::where('slug', 'temporary_guard')->first()->id; // e.g., 'Guadias Temporales'
        $suspendedStatusId = EmployeeStatus::where('slug', 'suspended')->first()->id;      // e.g., 'Suspendidos'
        $trainingStatusId = EmployeeStatus::where('slug', 'training')->first()->id;       // e.g., 'Capacitacion'
        $activeStatusId = EmployeeStatus::where('slug', 'active')->first()->id;         // e.g., 'Activo' for 'Total Asegurados'

        $today = Carbon::today()->toDateString();

        $query = Office::query();

        $user = Auth::user();
        if (!$user->hasRole('Super Administrador')) {
            if (isset($user->office) || is_array($user->office) || count($user->office) > 0) {
                $officeId = $user->office[0];
                $query = Office::query()->where('id', $officeId);
            }
        }

        if ($request->has('office_id')) {
            if ($request->input('office_id') != null) {
                $query->where('id', $request->input('office_id'));
            }
        }

        $offices = $query->withCount([
            'positions as temporary_guards_count' => fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas(
                        'employees',
                        fn($e) =>
                        $e->where('status_id', $temporalGuardsStatusId)
                    ),
            'positions as suspended_count' => fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas(
                        'employees',
                        fn($e) =>
                        $e->where('status_id', $suspendedStatusId)
                    ),
            'positions as training_count' => fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas(
                        'employees',
                        fn($e) =>
                        $e->where('status_id', $trainingStatusId)
                    ),
            'positions as total_insured_count' => fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas(
                        'employees',
                        fn($e) =>
                        $e->where('status_id', $activeStatusId)
                    ),
        ])->get();




        $totals = [
            'total_temporary_guards' => $offices->sum(callback: 'temporary_guards_count'),
            'total_suspended' => $offices->sum('suspended_count'),
            'total_training' => $offices->sum('training_count'),
            'total_insured' => $offices->sum('total_insured_count'),
        ];

        return ['offices' => $offices, 'totals' => $totals];
    }

    /**
     * Reporte certificados digessp.
     */
    private function getCertificationDigessp(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Resolver rango de fechas
        |--------------------------------------------------------------------------
        */
        if ($request->filled('start_date') || $request->filled('end_date')) {

            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::parse($request->end_date)->startOfDay();

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::parse($request->start_date)->endOfDay();

        } else {
            // Por defecto: hoy
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $query = Office::query();

        $user = Auth::user();
        if (!$user->hasRole('Super Administrador')) {
            if (isset($user->office) || is_array($user->office) || count($user->office) > 0) {
                $officeId = $user->office[0];
                $query = Office::query()->where('id', $officeId);
            }
        }

        if ($request->has('office_id')) {
            if ($request->input('office_id') != null) {
                $query->where('id', $request->input('office_id'));
            }
        }

        $today = Carbon::today()->toDateString();

        $offices = $query->withCount([
            // Total empleados
            'positions as total_count' => fn($q) =>
                $q->whereBetween('created_at', [$startDate, $endDate]),

            // Certificados vigentes
            'positions as vigentes_count' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('employees', function ($e) use ($startDate, $endDate) {
                    $e->where('digessp_fecha_vencimiento', '>=', $startDate);
                });
                $q->whereBetween('created_at', [$startDate, $endDate]);
            },

            // Certificados vencidos
            'positions as vencidos_count' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('employees', function ($e) use ($startDate, $endDate) {
                    $e->where('digessp_fecha_vencimiento', '<', $startDate);
                });
                $q->whereBetween('created_at', [$startDate, $endDate]);
            },

            // Sin certificado
            'positions as sin_certificado_count' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('employees', function ($e) use ($startDate, $endDate) {
                    $e->whereNull('digessp_fecha_vencimiento');
                });
                $q->whereBetween('created_at', [$startDate, $endDate]);
            },
        ])->get();

        // Transformamos para añadir porcentajes
        $offices = $offices->map(function ($office) {
            $total = $office->total_count;
            $vigentes = $office->vigentes_count;
            $percentage = $total > 0 ? ($vigentes / $total) * 100 : 0;

            return [
                'code' => $office->name,
                'total' => $total,
                'vigentes' => $vigentes,
                'percentage' => round($percentage, 2),
                'vencidos' => $office->vencidos_count,
                'sin_certificado' => $office->sin_certificado_count,
            ];
        });

        // Totales generales
        $totals = [
            'total' => $offices->sum('total'),
            'total_vigentes' => $offices->sum('vigentes'),
            'total_vencidos' => $offices->sum('vencidos'),
            'total_sin_certificado' => $offices->sum('sin_certificado'),
        ];

        return ['offices' => $offices, 'totals' => $totals];
    }


    /**
     * Reporte por cliente.
     */
    private function getTotalsByClient(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $response = [
            'today' => $this->calculateTotalsByDate($request, $today),
            'yesterday' => $this->calculateTotalsByDate($request, $yesterday),
        ];

        if ($request->filled(['start_date', 'end_date'])) {
            $response['range'] = $this->calculateTotalsByDate(
                $request,
            );
        }

        return $response;
    }


    private function calculateTotalsByDate(Request $request, string $date = null)
    {
        /*
        |--------------------------------------------------------------------------
        | Resolver rango de fechas
        |--------------------------------------------------------------------------
        */
        if ($request->filled('start_date') || $request->filled('end_date')) {

            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::parse($request->end_date)->startOfDay();

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::parse($request->start_date)->endOfDay();

        } elseif ($date) {

            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

        } else {
            // Fallback de seguridad (hoy)
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Status IDs
        |--------------------------------------------------------------------------
        */
        $availableStatusId = EmployeeStatus::where('slug', 'active')->value('id');
        $reserveStatusId = EmployeeStatus::where('slug', 'temporary_guard')->value('id');

        /*
        |--------------------------------------------------------------------------
        | TOP CLIENT por rango
        |--------------------------------------------------------------------------
        */
        $topClient = District::select(
            'districts.id',
            'districts.name',
            DB::raw('COUNT(employees.id) as employees_count')
        )
            ->join('offices', 'offices.district_id', '=', 'districts.id')
            ->join('positions', 'positions.office_id', '=', 'offices.id')
            ->join('employees', 'employees.id', '=', 'positions.employee_id')
            ->whereBetween('employees.created_at', [$startDate, $endDate])
            ->groupBy('districts.id', 'districts.name')
            ->orderByDesc('employees_count')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Oficinas filtradas por usuario
        |--------------------------------------------------------------------------
        */
        $query = Office::query();
        $user = Auth::user();

        if (!$user->hasRole('Super Administrador') && !empty($user->office)) {
            $query->where('id', $user->office[0]);
        }

        if ($request->filled('office_id')) {
            $query->where('id', $request->office_id);
        }

        $offices = $query
            ->with('district')
            ->withCount([
                'positions as total_count' => fn($q) =>
                    $q->whereBetween('created_at', [$startDate, $endDate]),

                'positions as available_count' => fn($q) =>
                    $q->whereBetween('created_at', [$startDate, $endDate])
                        ->whereHas(
                            'employees',
                            fn($e) =>
                            $e->where('status_id', $availableStatusId)
                        ),

                'positions as reserve_count' => fn($q) =>
                    $q->whereBetween('created_at', [$startDate, $endDate])
                        ->whereHas(
                            'employees',
                            fn($e) =>
                            $e->where('status_id', $reserveStatusId)
                        ),
            ])
            ->get()
            ->map(function ($office) use ($topClient) {
                $total = (int) $office->total_count;

                $belongsToTopClient = false;
                if ($topClient && $office->district) {
                    $belongsToTopClient = $office->district->id === $topClient->id;
                }

                return [
                    'office_id' => $office->id,
                    'office_code' => $office->name,
                    'top_client_count' => $belongsToTopClient ? $total : 0,
                    'others_count' => $belongsToTopClient ? 0 : $total,
                    'available_count' => (int) $office->available_count,
                    'reserve_count' => (int) $office->reserve_count,
                    'total' => $total,
                ];
            });

        return [
            'top_client_name' => $topClient?->name ?? 'N/A',
            'date_range' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'offices' => $offices,
            'totals' => [
                'total_top_client' => $offices->sum('top_client_count'),
                'total_others' => $offices->sum('others_count'),
                'total_available' => $offices->sum('available_count'),
                'total_reserve' => $offices->sum('reserve_count'),
                'grand_total' => $offices->sum('total'),
            ],
        ];
    }


    public function getGlobalDistributionByRegion(Request $request)
    {
        $districts = District::where('status', 1)
            ->withCount([
                'offices' => function ($query) {
                    $query->where('status', 1);
                }
            ])
            ->get();

        $districtsData = $districts->map(function ($district) {
            return [
                'code' => $district->code,
                'total' => $district->offices_count,
            ];
        });

        $totalOffices = $districts->sum('offices_count');

        return [
            'totals' => $totalOffices,
            'districts' => $districtsData,
        ];
    }

    public function getDistributionByRegion(Request $request)
    {
        $districts = District::with([
            'offices' => function ($query) {
                $query->where('status', 1)->withCount('positions');
            }
        ])->get();

        $districtsData = $districts->map(function ($district) {
            $officesData = $district->offices->map(function ($office) {
                return [
                    'code' => $office->name,
                    'total' => $office->positions_count,
                ];
            });

            return [
                'code' => $district->code,
                'total' => $officesData->sum('total'),
                'offices' => $officesData,
            ];
        });

        return [
            'districts' => $districtsData,
        ];
    }

    /**
     * Handle the export of the report.
     */
    private function export($data, $format, $reportType)
    {
        // This is a placeholder for the export logic.
        // In a real application, you would use a library like Maatwebsite/Excel
        // and have dedicated Export classes.

        // Example:
        // $exportClass = $this->getExportClass($reportType, $format);
        // return Excel::download(new $exportClass($data), 'report.'.$format);

        return response()->json([
            'message' => "Export functionality for '{$format}' is not yet implemented.",
            'data' => $data
        ], 501); // 501 Not Implemented
    }
}
