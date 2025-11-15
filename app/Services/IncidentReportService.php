<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentStatus;

class IncidentReportService
{
    public function generate($start, $end)
    {
        $in_progress_status = IncidentStatus::select('id')->where("slug", 'in_progress')->first();
        $resolved_status = IncidentStatus::select('id')->where("slug", 'resolved')->first();

        $query = Incident::query();

        if ($start)
            $query->whereDate('created_at', '>=', $start);
        if ($end)
            $query->whereDate('created_at', '<=', $end);

        // Estadistica de Incidentes en progreso por fecha
        $in_progress = $query->clone()
            ->where('status_id', '=', $in_progress_status->id)
            ->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->get();
        $incidents_in_progress = $in_progress->map(fn($incident) => [
            'status' => $incident->status?->name,
            'total' => $incident->total,
        ])->toArray();

        if (!empty($incidents_in_progress)) {
            $incidents_in_progress = $incidents_in_progress[0];
        }

        // Estadistica de Incidentes resueltos por fecha
        $resolved = $query->clone()
            ->where('status_id', '=', $resolved_status->id)
            ->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->get();

        $incidents_resolved = $resolved->map(fn($incident) => [
            'status' => $incident->status?->name,
            'total' => $incident->total,
        ])->toArray();

        if (!empty($incidents_resolved)) {
            $incidents_resolved = $incidents_resolved[0];
        }

        // Stadistica de Incidentes resueltos por fecha
        $by_critical = $query->clone()
                ->selectRaw('criticity_id, COUNT(*) as total')
                ->groupBy('criticity_id')
                ->get();

        $incidents_criticals = $by_critical->map(fn($incident) => [
            'critical' => $incident->criticidad?->name,
            'total' => $incident->total,
        ])->toArray();


        return [
            'period' => [
                'start_date' => $start,
                'end_date' => $end,
            ],

            'total' => $query->count(),
            'in_progress' => $incidents_in_progress,
            'resolved' => $incidents_resolved,
            'critical' => $incidents_criticals,
        ];
    }
}
