<?php

namespace App\Http\Controllers;

use App\Models\Critical;
use App\Models\Incident;
use App\Models\Office;
use App\Models\Type;
use App\Services\Base64FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    public $notFoundMessage = 'Incidente no encontrado.';
    public $storeErrorMessage = 'Fallo al crear el incidente.';
    public $storeSuccessMessage = 'Incidente creado correctamente.';
    public $updateSuccessMessage = 'Incidente actualizado correctamente.';


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Incident::query()
            ->join('offices', 'offices.id', '=', 'incidents.office_id')
            ->leftJoin('users', 'users.id', '=', 'incidents.user_assigned')
            ->leftJoin('types', 'types.id', '=', 'incidents.type_id')
            ->leftJoin('criticals', 'criticals.id', '=', 'incidents.criticity_id')
            ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
            ;

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('users.name', 'like', "%{$search}%");
        }

        if ($type = $request->query('type_id')) {
            $query->where('incidents.type_id', $type);
        }

        if ($office_id = $request->query('office_id')) {
            $query->where('incidents.office_id', $office_id);
        }

        if ($criticidad = $request->query('criticidad')) {
            $query->whereIn('criticals.slug', $criticidad);
        }

        if ($startDate = $request->query('fecha_inicio')) {
            $endDate = $request->query('fecha_fin', now()); // si no hay fecha_fin, toma hoy

            $query->whereBetween('incidents.created_at', [
                date('Y-m-d 00:00:00', strtotime($startDate)),
                date('Y-m-d 23:59:59', strtotime($endDate)),
            ]);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'incidents.id');
        $sortDir = $request->query('', 'asc');

        $incidents = $query->orderBy($sortBy, $sortDir)
            ->select('incidents.id', 'incidents.created_at as date', 'types.name as type', 'incidents.description as description', 'offices.code as office', 'users.name as user_reported', 'criticals.name as criticity', 'criticals.slug as criticity_slug', 'incident_statuses.name as status', 'incident_statuses.slug as status_slug')
            ->paginate($perPage);

        return response()->json($incidents, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Base64FileService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            // Llaves foráneas
            'type_id' => 'required|integer|exists:types,id',
            'office_id' => 'required|integer|exists:offices,id',
            'criticity_id' => 'required|integer|exists:criticals,id',

            // Usuarios
            'user_reported' => 'required|integer|exists:users,id',
            'user_assigned' => 'nullable|integer|exists:users,id',

            // Archivos JSON opcionales (colección de objetos)
            'files' => 'nullable|array',

            // Estado
            'status' => 'required|integer|exists:incident_statuses,id',
        ]);

        $incident = Incident::create([
            'title'=> $validated['title'],
            'description'=> $validated['description'],
            'type_id'=> $validated['type_id'],
            'office_id'=> $validated['office_id'],
            'criticity_id'=> $validated['criticity_id'],
            'status_id'=> $validated['status'],
            'user_reported'=> $validated['user_reported'],
            'user_assigned'=> $validated['user_assigned'],
        ]);

        $files_saved = $service->process_files($validated['files'],'incidents', $incident->id);

        $incident->update([
            'files'=> $files_saved,
        ]);

        if (!$incident || !$incident->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'incident' => $incident->id,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $incident = Incident::find($id);

        $data = [
            'id' => $incident->id,
            'title' => $incident->title,
            'type' => $incident->type?->name,
            'office' => $incident->oficina?->code,
            'criticity' => $incident->criticidad?->name,
            'criticity_slug' => $incident->criticidad?->slug,
            'description' => $incident->description,
            'files' => $incident->files,
            'status' => $incident->status?->name,
            'status_slug' => $incident->status?->slug,
            'user_reported' => $incident->userReported?->name,
            'user_assigned' => $incident->userAssigned?->name,
            'created_at' => $incident->created_at,
        ];

        if (!$incident) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, Base64FileService $service)
    {
        $incident = Incident::find($id);

        if (!$incident) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // Validación de los campos permitidos
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            // Llaves foráneas
            'type_id' => 'required|integer|exists:types,id',
            'office_id' => 'required|integer|exists:offices,id',
            'criticity_id' => 'required|integer|exists:criticals,id',

            // Usuarios
            'user_reported' => 'required|integer|exists:users,id',
            'user_assigned' => 'nullable|integer|exists:users,id',

            // Archivos JSON opcionales (colección de objetos)
            'files' => 'nullable|array',

            // Estado
            'status' => 'required|integer|exists:incident_statuses,id',
        ]);

        $files = $incident->files;
        $files_saved= $service->process_files($validated['files'],'incidents', $incident->id);
        if (!empty($files_saved)) {
            $files = array_merge($files_saved, $files);
        }

        $incident->update([
            'title'=> $validated['title'],
            'description'=> $validated['description'],
            'type_id'=> $validated['type_id'],
            'office_id'=> $validated['office_id'],
            'criticity_id'=> $validated['criticity_id'],
            'status_id'=> $validated['status'],
            'user_assigned'=> $validated['user_assigned'],
            'files'=> $files,
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $incident,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function getOffices(){
        $offices = Office::select('id', 'code')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $offices,
        ], 200);
    }

    public function getTypes(){
        $types = Type::select('id', 'name')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $types,
        ], 200);
    }

    public function getCriticals(){
        $criticals = Critical::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $criticals,
        ], 200);
    }
}
