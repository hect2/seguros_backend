<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
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

    Route::prefix('offices')
        ->name('offices.')
        ->middleware('permission:offices_view')
        ->group(function () {

            // GET /api/offices → index()
            Route::get('/', [OfficeController::class, 'index'])
                ->name('index');

            // GET /api/offices/{id} → show()
            Route::get('/{id}', [OfficeController::class, 'show'])
                ->name('show');

            // POST /api/offices → store()
            Route::post('/', [OfficeController::class, 'store'])
                ->name('store')
                ->middleware('permission:offices_create_or_import');

            // PUT /api/offices/{id} → update()
            Route::put('/{id}', [OfficeController::class, 'update'])
                ->name('update')
                ->middleware('permission:offices_edit');

            // DELETE /api/offices/{id} → destroy()
            Route::delete('/{id}', [OfficeController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:offices_delete');

            // POST /api/offices/import → import()
            Route::post('/import', [OfficeController::class, 'import'])
                ->name('import')
                ->middleware('permission:offices_create_or_import');

            // GET /api/offices/download → export()
            Route::post('/download', [OfficeController::class, 'export'])
                ->name('download')
                ->middleware('permission:offices_download');
        });

    Route::prefix('users')
        ->name('users.')
        ->middleware('permission:users_view')
        ->group(function () {

            // GET /api/users → index()
            Route::get('/', [UserController::class, 'index'])
                ->name('index');
            
            // GET /api/users/{id} → show()
            Route::get('/{id}', [UserController::class, 'show'])
                ->name('show');

            // POST /api/users → store()
            Route::post('/', [UserController::class, 'store'])
                ->name('store')
                ->middleware('permission:users_create');

            // PUT /api/users/{id} → update()
            Route::put('/{id}', [UserController::class, 'update'])
                ->name('update')
                ->middleware('permission:users_edit');

            // GET /api/users/roles → getRoles()
            Route::post('/roles', [UserController::class, 'getRoles'])
                ->name('getRoles');
            
            // GET /api/users/districts → getDistricts()
            Route::post('/roles', [UserController::class, 'getDistricts'])
                ->name('getDistricts');
        });
});
