<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistrictController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::prefix('districts')
        ->name('districts.')
        ->middleware('permission:districts_view')
        ->group(function () {

            // GET /api/districts → index()
            Route::get('/', [DistrictController::class, 'index'])
                ->name('index');

            // GET /api/districts/{id} → show()
            Route::get('/{id}', [DistrictController::class, 'show'])
                ->name('show');

            // POST /api/districts → store()
            Route::post('/', [DistrictController::class, 'store'])
                ->name('store')
                ->middleware('permission:districts_create_or_import');

            // PUT /api/districts/{id} → update()
            Route::put('/{id}', [DistrictController::class, 'update'])
                ->name('update')
                ->middleware('permission:districts_edit');

            // DELETE /api/districts/{id} → destroy()
            Route::delete('/{id}', [DistrictController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:districts_delete');

            // POST /api/districts/import → import()
            Route::post('/import', [DistrictController::class, 'import'])
                ->name('import')
                ->middleware('permission:districts_create_or_import');

            // GET /api/districts/download → export()
            Route::post('/download', [DistrictController::class, 'export'])
                ->name('download')
                ->middleware('permission:districts_download');
        });
});
