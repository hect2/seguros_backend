<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'report_type' => 'required|string|in:summary_by_office,digessp_certifications,totals_by_client',
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
        }

        if ($format === 'json') {
            return response()->json($data);
        }

        return $this->export($data, $format, $reportType);
    }

    /**
     * Reporte resumen por oficina.
     */
    private function getSummaryByOffice(Request $request)
    {
        // Placeholder status IDs. The user will replace these.
        $temporalGuardsStatusId = EmployeeStatus::where('slug', 'temporary_guard')->first()->id; // e.g., 'Guadias Temporales'
        $suspendedStatusId = EmployeeStatus::where('slug', 'suspended')->first()->id;      // e.g., 'Suspendidos'
        $trainingStatusId = EmployeeStatus::where('slug', 'training')->first()->id;       // e.g., 'Capacitacion'
        $activeStatusId = EmployeeStatus::where('slug', 'active')->first()->id;         // e.g., 'Activo' for 'Total Asegurados'

        $query = Office::query();

        if ($request->has('office_id')) {
            if ($request->input('office_id') != null) {
                $query->where('id', $request->input('office_id'));
            }
        }

        $offices = $query->withCount([
            'positions as temporary_guards_count' => function ($q) use ($temporalGuardsStatusId) {
                $q->whereHas('employees', function ($e) use ($temporalGuardsStatusId) {
                    $e->where('status_id', $temporalGuardsStatusId);
                });
            },
            'positions as suspended_count' => function ($q) use ($suspendedStatusId) {
                $q->whereHas('employees', function ($e) use ($suspendedStatusId) {
                    $e->where('status_id', $suspendedStatusId);
                });
            },
            'positions as training_count' => function ($q) use ($trainingStatusId) {
                $q->whereHas('employees', function ($e) use ($trainingStatusId) {
                    $e->where('status_id', $trainingStatusId);
                });
            },
            'positions as total_insured_count' => function ($q) use ($activeStatusId) {
                $q->whereHas('employees', function ($e) use ($activeStatusId) {
                    $e->where('status_id', $activeStatusId);
                });
            },
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
        $query = Office::query();

        if ($request->has('office_id')) {
            if ($request->input('office_id') != null) {
                $query->where('id', $request->input('office_id'));
            }
        }

        $today = Carbon::today();

        $offices = $query->withCount([
            // Total empleados
            'positions as total_count',

            // Certificados vigentes
            'positions as vigentes_count' => function ($q) use ($today) {
                $q->whereHas('employees', function ($e) use ($today) {
                    $e->where('digessp_fecha_vencimiento', '>=', $today);
                });
            },

            // Certificados vencidos
            'positions as vencidos_count' => function ($q) use ($today) {
                $q->whereHas('employees', function ($e) use ($today) {
                    $e->where('digessp_fecha_vencimiento', '<', $today);
                });
            },

            // Sin certificado
            'positions as sin_certificado_count' => function ($q) {
                $q->whereHas('employees', function ($e) {
                    $e->whereNull('digessp_fecha_vencimiento');
                });
            },
        ])->get();

        // Transformamos para añadir porcentajes
        $offices = $offices->map(function ($office) {
            $total = $office->total_count;
            $vigentes = $office->vigentes_count;
            $percentage = $total > 0 ? ($vigentes / $total) * 100 : 0;

            return [
                'code' => $office->code,
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
        // Placeholder status IDs (ajusta o busca por slug si prefieres)
        $availableStatusId = 5; // 'Disponible'
        $reserveStatusId = 6;   // 'Reserva'

        // 1) Encontrar el top client (Business) por cantidad de empleados (JOIN sobre la cadena)
        $topClient = Business::select('business.*', DB::raw('COUNT(employees.id) as employees_count'))
            ->join('districts', 'districts.business_id', '=', 'business.id')
            ->join('offices', 'offices.district_id', '=', 'districts.id')
            ->join('positions', 'positions.office_id', '=', 'offices.id')
            ->join('employees', 'employees.id', '=', 'positions.employee_id')
            ->groupBy('business.id')
            ->orderByDesc('employees_count')
            ->first();

        // 2) Preparar query de oficinas (puedes filtrar por office_id)
        $query = Office::query();

        if ($request->has('office_id')) {
            if ($request->input('office_id') != null) {
                $query->where('id', $request->input('office_id'));
            }
        }

        // 3) Traer oficinas con los contadores por empleado (total, disponible, reserva)
        // Asegúrate que Office tiene: public function employees() { return $this->hasManyThrough(Employee::class, Position::class); }
        $offices = $query
            ->with('district') // necesitamos saber a qué business pertenece la oficina (via district)
            ->withCount([
                'positions as total_count',
                'positions as available_count' => function ($q) use ($availableStatusId) {
                    $q->whereHas('employees', function ($e) use ($availableStatusId) {
                        $e->where('status_id', $availableStatusId);
                    });
                },
                'positions as reserve_count' => function ($q) use ($reserveStatusId) {
                    $q->whereHas('employees', function ($e) use ($reserveStatusId) {
                        $e->where('status_id', $reserveStatusId);
                    });
                },
            ])->get();


        // 4) Mapear y calcular top_client_count / others_count / total por oficina
        $offices = $offices->map(function ($office) use ($topClient) {
            $total = (int) $office->total_count;
            $available = (int) $office->available_count;
            $reserve = (int) $office->reserve_count;

            $belongsToTopClient = false;
            if ($topClient && $office->relationLoaded('district') && $office->district) {
                // Asumiendo que districts tiene business_id
                $belongsToTopClient = ((int) $office->district->business_id === (int) $topClient->id);
            }

            $topClientCount = $belongsToTopClient ? $total : 0;
            $othersCount = $belongsToTopClient ? 0 : $total;

            return [
                'office_id' => $office->id,
                'office_code' => $office->code,
                'top_client_count' => $topClientCount,
                'others_count' => $othersCount,
                'available_count' => $available,
                'reserve_count' => $reserve,
                'total' => $total,
            ];
        });

        // 5) Totales agregados
        $totals = [
            'total_top_client' => $offices->sum('top_client_count'),
            'total_others' => $offices->sum('others_count'),
            'total_available' => $offices->sum('available_count'),
            'total_reserve' => $offices->sum('reserve_count'),
            'grand_total' => $offices->sum('total'),
        ];

        return [
            'top_client_name' => $topClient ? $topClient->name : 'N/A',
            'offices' => $offices,
            'totals' => $totals,
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
