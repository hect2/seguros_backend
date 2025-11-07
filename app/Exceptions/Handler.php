<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Captura todas las excepciones y las convierte en JSON
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Maneja las excepciones y devuelve una respuesta JSON uniforme.
     */
    protected function handleApiException(Throwable $e)
    {
        // Error por validaciones
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        // Error si no existe la ruta
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
            ], 404);
        }

        // Error si el método no está permitido
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'HTTP method not allowed.',
            ], 405);
        }

        // Error HTTP genérico
        if ($e instanceof HttpResponseException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getResponse()->getStatusCode());
        }

        // Cualquier otro error
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Internal Server Error.',
            'type'    => class_basename($e),
        ], $status);
    }
}
