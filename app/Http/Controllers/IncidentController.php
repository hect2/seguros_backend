<?php

namespace App\Http\Controllers;

use App\Models\Critical;
use App\Models\Incident;
use App\Models\IncidentStatus;
use App\Models\Office;
use App\Models\Type;
use App\Models\User;
use App\Notifications\NewIncidentNotification;
use App\Services\Base64FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->join('districts', 'districts.id', '=', 'incidents.district_id')
            ->leftJoin('users', 'users.id', '=', 'incidents.user_assigned')
            ->leftJoin('types', 'types.id', '=', 'incidents.type_id')
            ->leftJoin('criticals', 'criticals.id', '=', 'incidents.criticity_id')
            ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
        ;

        // Apply role-based filtering
        $user = auth()->user();
        if ($user) {
            $query->where(function ($q) use ($user) {

                // Incidentes que el usuario reportó directamente
                // $q->where('incidents.user_reported', $user->id);

                // Si el usuario tiene distritos asignados
                if (!empty($user->district) && is_array($user->district)) {
                    $q->orWhereIn('incidents.district_id', $user->district);
                }

            });
        }
        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('users.name', 'like', "%{$search}%");
        }

        if ($type = $request->query('type_id')) {
            $query->where('incidents.type_id', $type);
        }

        if ($district_id = $request->query('district_id')) {
            $query->where('incidents.district_id', $district_id);
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
            ->select('incidents.id', 'incidents.created_at as date', 'types.name as type', 'incidents.description as description', 'districts.code as district', 'users.name as user_reported', 'criticals.name as criticity', 'criticals.slug as criticity_slug', 'incident_statuses.name as status', 'incident_statuses.slug as status_slug')
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
            'district_id' => 'required|integer|exists:districts,id',
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
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type_id' => $validated['type_id'],
            'district_id' => $validated['district_id'],
            'criticity_id' => $validated['criticity_id'],
            'status_id' => $validated['status'],
            'user_reported' => $validated['user_reported'],
            'user_assigned' => $validated['user_assigned'],
        ]);

        $files_saved = $service->process_files($validated['files'], 'incidents', $incident->id);

        $incident->update([
            'files' => $files_saved,
        ]);

        if (!$incident || !$incident->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        // Obtener usuarios del distrito
        $districtUsers = User::whereJsonContains('district', (int) $incident->district_id)->get();
        foreach ($districtUsers as $user) {
            $user->notify(new NewIncidentNotification($incident));
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
            'district' => $incident->oficina?->code,
            'criticity' => $incident->criticidad?->name,
            'criticity_slug' => $incident->criticidad?->slug,
            'description' => $incident->description,
            'files' => $incident->files,
            'status' => $incident->status?->name,
            'status_slug' => $incident->status?->slug,
            'status_id' => $incident->status?->id,
            'user_reported' => $incident->userReported?->name,
            'user_assigned' => $incident->userAssigned?->name,
            'created_at' => $incident->created_at,
            'follow_date' => $incident->follow_date,
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
            'district_id' => 'required|integer|exists:districts,id',
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
        $files_saved = $service->process_files($validated['files'], 'incidents', $incident->id);
        if (!empty($files_saved)) {
            $files = array_merge($files_saved, $files);
        }

        $incident->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type_id' => $validated['type_id'],
            'district_id' => $validated['district_id'],
            'criticity_id' => $validated['criticity_id'],
            'status_id' => $validated['status'],
            'user_assigned' => $validated['user_assigned'],
            'files' => $files,
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $incident,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function getOffices(Request $request)
    {
        $district_id = (int) $request->input('district_id', 0);
        $user_id = (int) $request->input('user_id', 0);

        if ($district_id === 0) {
            if ($user_id === 0) {
                // Filtrar por distrito únicamente
                $offices = Office::select('id', 'code')->get();
            } else {
                // Filtrar por la oficina del usuario autenticado
                $user = Auth::user();

                // Validación defensiva por si user->office está vacío
                if (!$user->hasRole('Super Administrador')) {
                    if (!isset($user->office) || !is_array($user->office) || count($user->office) === 0) {
                        return response()->json([
                            'error' => true,
                            'message' => 'El usuario no tiene oficinas asignadas.',
                        ], 400);
                    }

                    $officeId = $user->office[0];

                    $offices = Office::where('id', $officeId)
                        ->select('id', 'code')
                        ->get();
                } else {
                    $offices = Office::select('id', 'code')->get();
                }
            }
        } else {
            if ($user_id === 0) {
                // Filtrar por distrito únicamente
                $offices = Office::where('district_id', $district_id)
                    ->select('id', 'code')
                    ->get();
            } else {
                // Filtrar por la oficina del usuario autenticado
                $user = Auth::user();

                if (!$user->hasRole('Super Administrador')) {
                    // Validación defensiva por si user->office está vacío
                    if (!isset($user->office) || !is_array($user->office) || count($user->office) === 0) {
                        return response()->json([
                            'error' => true,
                            'message' => 'El usuario no tiene oficinas asignadas.',
                        ], 400);
                    }

                    $officeId = $user->office[0];

                    $offices = Office::where('id', $officeId)
                        ->select('id', 'code')
                        ->get();
                } else {
                    $offices = Office::select('id', 'code')->get();
                }
            }
        }


        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $offices,
        ], 200);
    }

    public function getTypes()
    {
        $types = Type::select('id', 'name')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $types,
        ], 200);
    }

    public function getCriticals()
    {
        $criticals = Critical::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $criticals,
        ], 200);
    }

    public function getIncidentStatus()
    {
        $statuses = IncidentStatus::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $statuses,
        ], 200);
    }

    public function assign(Request $request, $id)
    {
        $user = Incident::find($id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // Validación del campo user_assigned
        $validated = $request->validate([
            'user_assigned' => 'required|integer|exists:users,id',
        ], [
            'user_assigned.required' => 'Debe asignar un usuario.',
            'user_assigned.exists' => 'El usuario asignado no existe.',
        ]);

        // Actualizar
        $user->update([
            'user_assigned' => $validated['user_assigned']
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => [
                'id' => $user->id,
                'user_assigned' => $user->user_assigned,
            ],
            'message' => 'Usuario asignado correctamente.',
        ], 200);
    }

    public function follow(Request $request, $id)
    {
        $incident = Incident::find($id);

        if (!$incident) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // Validación del campo follow_date
        $validated = $request->validate([
            'follow_date' => 'required|date',
        ], [
            'follow_date.required' => 'Debe asignar una fecha de seguimiento.',
            'follow_date.date' => 'La fecha de seguimiento no es válida.',
        ]);

        // Actualizar
        $incident->update([
            'follow_date' => $validated['follow_date']
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => [
                'id' => $incident->id,
                'follow_date' => $incident->follow_date,
            ],
            'message' => 'Fecha de seguimiento actualizada correctamente.',
        ], 200);
    }
}
