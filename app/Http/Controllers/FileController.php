<?php
namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\MessageIncident;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public $notFoundMessage = 'Registro no encontrado.';

    public function show(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'id' => 'required|integer',
            'filename' => 'required|string',
        ]);

        Log::error('Validated: ' . json_encode($validated));

        // ---- Selección del módulo ----
        $record = null;

        if ($validated['module'] === 'incidents') {
            $record = Incident::find($validated['id']);
        } else if ($validated['module'] === 'messages') {
            $record = MessageIncident::find($validated['id']);
        }

        if (!$record) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // ---- Buscar archivo dentro del JSON ----
        $file = null;
        if ($validated['module'] === 'incidents') {
            $file = collect($record->files)
                ->firstWhere('filename', $validated['filename']);
        } else if ($validated['module'] === 'messages') {
            $file = collect($record->attachments)
                ->firstWhere('filename', $validated['filename']);
        }


        if (!$file) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => 'File not found.',
            ], 404);
        }

        // Path REAL en storage
        $storagePath = urldecode($file['path']);
        Log::error("Path real: $storagePath");

        if (!Storage::disk('public')->exists($storagePath)) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => 'File not found in storage.',
            ], 404);
        }

        Log::error("Download: $storagePath");
        return Storage::disk('public')->download($storagePath);

    }
}
