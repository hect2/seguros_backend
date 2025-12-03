<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\MessageIncident;
use App\Services\Base64FileService;
use Illuminate\Http\Request;

class MessageIncidentController extends Controller
{
    public $notFoundMessage = 'Mensaje no encontrado.';
    public $storeErrorMessage = 'Fallo al crear el mensaje.';
    public $storeSuccessMessage = 'Mensaje creado correctamente.';
    public $updateSuccessMessage = 'Mensaje actualizado correctamente.';
    public $deleteSuccessMessage = 'Mensaje eliminado correctamente.';



    /**
     * Display a listing of the resource.
     */
    public function index($incident_id)
    {
        $messages = MessageIncident::with('replies')
            ->where('id_incident', $incident_id)
            ->where('id_message_reply', null)
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json([
            'error' => false,
            'data' => $messages,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Base64FileService $service)
    {
        $validated = $request->validate([
            'id_message_reply' => 'nullable|integer|exists:message_incidents,id',
            'id_incident' => 'required|integer|exists:incidents,id',
            'id_user' => 'required|integer|exists:users,id',
            'message' => 'required|strin g',
            'attachments' => 'nullable|array',
            'status_id' => 'nullable|integer|exists:incident_statuses,id',
        ]);

        if (isset($validated['status_id'])) {
            $incident = Incident::find($validated['id_incident']);
            $incident->update([
                'status_id' => $validated['status_id'],
            ]);
        }

        $message = MessageIncident::create([
            'id_message_reply' => $validated['id_message_reply'] ?? null,
            'id_incident' => $validated['id_incident'],
            'id_user' => $validated['id_user'],
            'message' => $validated['message'],
        ]);

        $files_saved = $service->process_files($validated['attachments'], 'incidents/messages', $message->id);

        $message->update([
            'attachments' => $files_saved,
        ]);

        if (!$message || !$message->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $message,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, Base64FileService $service)
    {
        $message = MessageIncident::find($id);

        if (!$message) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'message' => 'sometimes|string',
            'attachments' => 'sometimes|array',
        ]);

        $files = $message->attachments;
        if (!$validated['attachments']) {
            $files_saved = $service->process_files($validated['attachments'], 'incidents/messages', $message->id);
            if (!empty($files_saved)) {
                $files = array_merge($files_saved, $files);
            }
        }

        $message->update([
            'message' => $validated['message'],
            'attachments' => $files,
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $message,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $message = MessageIncident::find($id);

        if (!$message) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $message->delete();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $message,
            'message' => $this->deleteSuccessMessage,
        ], 200);
    }
}
