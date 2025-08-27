<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', [ApiController::class, 'login']);
Route::post('/sendPasswordResetEmail', [ApiController::class, 'sendPasswordResetEmail']);
Route::middleware('auth.token')->group(function() {
    Route::get('me', [ApiController::class, 'me']);

    Route::post('serialize', [ApiController::class, 'serialize_form']);

    Route::post('create_new_project', [ApiController::class, 'createProject']);
    Route::post('project_measurement_data', [ApiController::class, 'projectMeasurementData']);
    Route::get('project_data/{name}', [ApiController::class, 'getMeasurementData']);
    Route::get('projects', [ApiController::class, 'projects']);
    Route::get('all-projects', [ApiController::class, 'allProjects']);
    Route::get('projects/{id}', [ApiController::class, 'project']);
    Route::get('project_name/{id}', [ApiController::class, 'projectName']);

    Route::get('labors', [ApiController::class, 'labors']);
    Route::get('labors/{id}', [ApiController::class, 'labor']);

    Route::get('equipments', [ApiController::class, 'equipments']);
    Route::get('equipments/{id}', [ApiController::class, 'equipment']);

    Route::get('crews', [ApiController::class, 'crews']);
    Route::get('crews/{id}', [ApiController::class, 'crew']);

    Route::get('materials/divisions', [ApiController::class, 'material_divisions']);
    Route::get('materials/divisions/{id}', [ApiController::class, 'material_from_division']);
    Route::get('materials', [ApiController::class, 'materials']);
    Route::get('materials/{id}', [ApiController::class, 'material']);

    Route::post('plan/uploads', [ApiController::class, 'plan_upload']);
    Route::get('plans/{id}', [ApiController::class, 'project_plans']);

    Route::post('plan/delete', [ApiController::class, 'delete_plans']);
    Route::post('plan/delete/folder', [ApiController::class, 'delete_folder']);
    Route::get('plan/delete/all/{id}', [ApiController::class, 'delete_all_files']);

    Route::post('report/count/{project_id}', [ApiController::class, 'generate_count_report']);
    Route::post('report/perimeter/{project_id}', [ApiController::class, 'generate_perimeter_report']);

    Route::post('report/area/{project_id}', [ApiController::class, 'generate_area_report']);
    Route::post('report/all/{project_id}', [ApiController::class, 'generate_project_report']);
    Route::post('proposal/all/{project_id}', [ApiController::class, 'generate_project_proposal']);
    Route::post('material-qoute/all/{project_id}', [ApiController::class, 'generate_material_qoute']);
    Route::post('sendReport/{project_id}', [ApiController::class, 'sendReport'])->name('sendReport');
    Route::post('current_location', [ApiController::class, "current_location"]);
    Route::post('sync_local_db', [ApiController::class, "sync_local_db"]);
    Route::get('get_local_db', [ApiController::class, "get_local_db"]);
    Route::get('logout', [ApiController::class, "logout"]);

});