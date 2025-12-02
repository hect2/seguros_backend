<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MessageIncidentController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportIncidentController;
use App\Http\Controllers\Reports\ClientTotalsController;
use App\Http\Controllers\Reports\DigesspReportController;
use App\Http\Controllers\Reports\GeneralReportController;
use App\Http\Controllers\Reports\OfficeSummaryController;
use App\Http\Controllers\Reports\PNCReportController;
use App\Http\Controllers\Reports\ReportsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'check.token.expiration'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('list')
        ->name('list.')
        ->group(function () {

            // GET /api/list/roles → getRoles()
            Route::get('/roles', [UserController::class, 'getRoles'])
                ->name('getRoles');

            // GET /api/list/districts → getDistricts()
            Route::get('/districts', [UserController::class, 'getDistricts'])
                ->name('getDistricts');

            // GET /api/list/offices → getOffices()
            Route::get('/offices', [IncidentController::class, 'getOffices'])
                ->name('getOffices');

            // GET /api/list/types → getTypes()
            Route::get('/types', [IncidentController::class, 'getTypes'])
                ->name('getTypes');

            // GET /api/list/criticals → getCriticals()
            Route::get('/criticals', [IncidentController::class, 'getCriticals'])
                ->name('getCriticals');

            // GET /api/list/status-employees → getStatusEmployees()
            Route::get('/status-employees', [EmployeeController::class, 'getStatusEmployees'])
                ->name('getStatusEmployees');

            // GET /api/list/positiontypes → getPositionTypes()
            Route::get('/positiontypes', [EmployeeController::class, 'getPositionTypes'])
                ->name('getPositionTypes');

            // GET /api/list/business → getBusinesses()
            Route::get('/business', [BusinessController::class, 'getBusinesses'])
                ->name('getBusinesses');
        });

    Route::prefix('counts')
        ->name('counts.')
        ->group(function () {

            // GET /api/counts/districts/ → getCount()
            Route::get('/districts', [DistrictController::class, 'getCount'])
                ->name('districts.getCount');

            // GET /api/counts/offices/ → getCount()
            Route::get('/offices', [OfficeController::class, 'getCount'])
                ->name('offices.all');

            // GET /api/counts/business/ → getCount()
            Route::get('/business', [BusinessController::class, 'getCount'])
                ->name('business.all');
        });



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
        });

    Route::prefix('incidents')
        ->name('incidents.')
        ->middleware('permission:incidents_view')
        ->group(function () {

            // GET /api/incidents → index()
            Route::get('/', [IncidentController::class, 'index'])
                ->name('index');

            // GET /api/incidents/{id} → show()
            Route::get('/show/{id}', [IncidentController::class, 'show'])
                ->name('show');

            // POST /api/incidents → store()
            Route::post('/', [IncidentController::class, 'store'])
                ->name('store')
                ->middleware('permission:incidents_create');

            // PUT /api/incidents/{id} → update()
            Route::put('/update/{id}', [IncidentController::class, 'update'])
                ->name('update')
                ->middleware('permission:incidents_edit');

            // GET /api/incidents/reports → index()
            Route::get('/reports', [ReportIncidentController::class, 'index'])
                ->name('reports');


            Route::prefix('messages')
                ->name('messages.')
                ->group(function () {

                    // GET /api/incidents/messages/list/{$id} → index()
                    Route::get('/list/{incident_id}', [MessageIncidentController::class, 'index']);

                    // POST /api/incidents/messages/ → store()
                    Route::post('/', [MessageIncidentController::class, 'store']);

                    // PUT /api/incidents/messages/{$id} → update()
                    Route::put('/{id}', [MessageIncidentController::class, 'update']);

                    // DELETE /api/incidents/messages/{$id} → destroy()
                    Route::delete('/{id}', [MessageIncidentController::class, 'destroy']);
                });
        });

    Route::prefix('employees')
        ->name('employees.')
        ->middleware('permission:employees_view')
        ->group(function () {

            // GET /api/employees → index()
            Route::get('/', [EmployeeController::class, 'index'])
                ->name('index');

            // GET /api/employees/show/{id} → show()
            Route::get('/show/{id}', [EmployeeController::class, 'show'])
                ->name('show');

            // POST /api/employees → store()
            Route::post('/', [EmployeeController::class, 'store'])
                ->name('store')
                ->middleware('permission:employees_create_or_import');

            // PUT /api/employees/update/{id} → update()
            Route::put('/update/{id}', [EmployeeController::class, 'update'])
                ->name('update')
                ->middleware('permission:employees_edit');

            // POST /api/employees/import → import()
            Route::post('/import', [EmployeeController::class, 'import'])
                ->name('import')
                ->middleware('permission:employees_create_or_import');
        });


    Route::prefix('reports')->group(function () {
        Route::get('/pnc', [PNCReportController::class, 'index']);
        Route::get('/general', [GeneralReportController::class, 'index']);
        Route::get('/office-summary', [OfficeSummaryController::class, 'index']);
        Route::get('/digessp', [DigesspReportController::class, 'index']);
        Route::get('/client-totals', [ClientTotalsController::class, 'index']);

        // Downloads
        Route::get('/pnc/pdf', [PNCReportController::class, 'pdf']);
        Route::get('/pnc/xlsx', [PNCReportController::class, 'xlsx']);

        Route::get('/general/pdf', [GeneralReportController::class, 'pdf']);
        Route::get('/general/csv', [GeneralReportController::class, 'csv']);
        Route::get('/general/xlsx', [GeneralReportController::class, 'xlsx']);

        Route::get('/office-summary/xlsx', [OfficeSummaryController::class, 'xlsx']);
        Route::get('/digessp/xlsx', [DigesspReportController::class, 'xlsx']);
        Route::get('/client-totals/xlsx', [ClientTotalsController::class, 'xlsx']);
    });

    Route::prefix('business')
        ->name('business.')
        ->middleware('permission:business_view')
        ->group(function () {

            // GET /api/business → index()
            Route::get('/', [BusinessController::class, 'index'])
                ->name('index');

            // GET /api/business/{id} → show()
            Route::get('/show/{id}', [BusinessController::class, 'show'])
                ->name('show');

            // POST /api/business → store()
            Route::post('/', [BusinessController::class, 'store'])
                ->name('store')
                ->middleware('permission:business_create');

            // PUT /api/business/{id} → update()
            Route::put('/{id}', [BusinessController::class, 'update'])
                ->name('update')
                ->middleware('permission:business_edit');
        });

    Route::prefix('reports')
        ->name('reports.')
        ->middleware('permission:reports_view')
        ->group(function () {

            // GET /api/reports → index()
            Route::post('/', [ReportsController::class, 'index'])
                ->name('index');

            // GET /api/global-distribution-by-region → getGlobalDistributionByRegion()
            Route::get('/global-distribution-by-region', [ReportsController::class, 'getGlobalDistributionByRegion'])
                ->name('getGlobalDistributionByRegion');

            // GET /api/distribution-by-region → getDistributionByRegion()
            Route::get('/distribution-by-region', [ReportsController::class, 'getDistributionByRegion'])
                ->name('getDistributionByRegion');
        });
});
