<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Base64FileService
{
    public function save(string $base64, string $path, string $filename): ?array
    {
        if (!str_contains($base64, 'base64,')) {
            Log::error('No hay archivo');
            return null;
        }

        // Carpeta final
        $path = 'uploads/' . $path;

        // Crear carpeta si no existe
        Storage::disk('local')->makeDirectory($path, 0755, true);

        // Separar metadata y contenido
        [$meta, $content] = explode(',', $base64);

        // MIME Type
        preg_match('/data:(.*?);base64/', $meta, $match);
        $mime = $match[1] ?? null;

        if (!$mime) {
            return null;
        }

        // Extensión
        $ext = explode('/', $mime)[1] ?? 'bin';

        // Asegurar que filename tiene extensión
        if (!str_ends_with($filename, ".$ext")) {
            $filename .= ".$ext";
        }

        // Decodificar base64
        $bytes = base64_decode($content);

        // Guardar archivo
        Storage::disk('local')->put("$path/$filename", $bytes);
        $url = Storage::disk('local')->url("$path/$filename");
        Log::error("URL file: " . $url);

        return [
            'uuid'         => str()->uuid(),
            'filename'     => $filename,
            'path'         => "$path/$filename",
            'url'          => $url,
            'mime_type'    => $mime,
            'size_bytes'   => strlen($bytes),
            'uploaded_at'  => now()->toDateTimeString(),
        ];
    }


    public function process_files(array $files, string $directory, int $id, $module = ''): array{
        $files_saved = [];
        Log::error(message: 'Save file');
        foreach ($files as $file) {
            $path= $directory . '/' . $id ;
            Log::error('Inicio de save file: '. $id .' - '. $path);
            $file_saved = $this->save(
                base64: $file['file'],
                path: $path,
                filename: $file['name']
            );

            if ($module == 'employee') {
                $file_saved['date_emission'] = $file['date_emission'];
                $file_saved['type'] = $file['type'];
            }

            $files_saved[] = $file_saved;
        }

        return $files_saved;
    }
}
