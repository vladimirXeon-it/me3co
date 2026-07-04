<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Wall;
use Illuminate\Support\Facades\Storage;

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
Route::get('/s3-test', function () {

        Storage::disk('s3')->put(
            'test/test.txt',
            'Hola Paco desde Laravel'
        );

        return Storage::disk('s3')->exists('test/test.txt')
            ? 'OK'
            : 'FAIL';
    });
Route::get('/s3-env-test', function () {
    return [
        'bucket' => env('AWS_BUCKET2'),
        'region' => env('AWS_DEFAULT_REGION'),
        'storage_on_s3' => env('STORAGE_ON_S3'),
        'config_bucket' => config('filesystems.disks.s3.bucket'),
    ];
});
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
    Route::get('materials/{id}', [ApiController::class, 'materials']);
    Route::get('material/{id}', [ApiController::class, 'material']);

    //Route::post('plan/uploads', [ApiController::class, 'plan_upload']);
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
    Route::post('sync_local_db', [ApiController::class, "sync_local_db"]);
    Route::post('sync_project_local_db', [ApiController::class, "sync_project_local_db"]);
    Route::get('get_project_local_db/{data}', [ApiController::class, "get_project_local_db"]);
    Route::post('current_location', [ApiController::class, "current_location"]);
    Route::get('get_local_db', [ApiController::class, "get_local_db"]);
    Route::post('save-line-template', [ApiController::class, "saveLineTemplate"]);
    Route::post('CalculateData', [ApiController::class, "CalculateData"]);
    Route::post('ObtenData', [ApiController::class, "ObtenData"]);
    Route::get('get-line-template', [ApiController::class, "getLineTemplate"]);
    Route::get('logout', [ApiController::class, "logout"]);
    Route::post('sync/{table}', [ApiController::class, "syncTable"]);
    Route::post('pullUpdates/{table}', [ApiController::class, 'pullUpdates']);

    Route::get('/course-bands',  [ApiController::class, 'api_course_bands_index']);
    Route::post('/course-bands',        [ApiController::class, 'api_course_bands_upsert']);   // crea/actualiza por (id_user,id_local)
    Route::put ('/course-bands/{id}',   [ApiController::class, 'api_course_bands_update']);
    Route::get   ('/course-bands/{id}', [ApiController::class, 'api_course_bands_show']);
    Route::delete('/course-bands/{id}', [ApiController::class, 'api_course_bands_destroy']);

    Route::get('/pitch', [ApiController::class, 'searchPitch']);

    Route::post('/save-plan-image', [ApiController::class, 'savePlanImage']);
    Route::post('/pdf-to-png', [ApiController::class, 'convert']);
    Route::get('/groutBlock', [ApiController::class, 'searchGroutBlock']);
    Route::get('/list-plan-images', [ApiController::class, 'listPlanImages']);

    Route::post('/pdf/analyze', [ApiController::class, 'analyzePdf']); 
    Route::post('plan/uploads',      [ApiController::class, 'plan_upload']); 
    Route::post('/plans/upload-png-chunk', [ApiController::class, 'uploadPngChunk']);
    Route::post('/plan/upload-full-page', [ApiController::class, 'uploadFullPage']);
    Route::get('/plan/fullExists', [ApiController::class, 'fullExists']);

    Route::post('/sql/query', [ApiController::class, 'runQuery']);
    Route::post('/takeoffs/reports/bulk', [ApiController::class, 'bulk']);
    Route::post('/takeoffs/reports/bulk-excel', [ApiController::class, 'bulkExcel']);
    Route::post('/takeoffs/reports/bulk-excel-html', [ApiController::class, 'bulkExcelFromHtml']);

    Route::post('/delete-template', [ApiController::class, 'deleteTemplate']);
    Route::post('/upload-annotation-photo', [ApiController::class, 'uploadAnnotationPhoto']);

    Route::get('/user', [ApiController::class, 'currentUser']);
    Route::post('/update-line-template', [ApiController::class, 'updateLineTemplate']);

    Route::post('/save-drawing-template', [ApiController::class, 'saveDrawingTemplate']);
    Route::get('/drawing-template/{template_id}', [ApiController::class, 'getDrawingTemplate']);

    Route::get('/uploads/{path}', function ($path) {
        $cleanPath = StorageService::cleanPath($path);

        if (StorageService::useS3()) {
            if (!StorageService::exists($cleanPath)) {
                abort(404);
            }

            return redirect(StorageService::url($cleanPath));
        }

        abort(404);
    })->where('path', '.*');

    Route::get('/get-line-template-by-id/{id}', [ApiController::class, 'getLineTemplateById']);
    Route::get('/get-area-template-by-id/{id}', [ApiController::class, 'getAreaTemplateById']);
    Route::get('/get-perimeter-template-by-id/{id}', [ApiController::class, 'getPerimeterTemplateById']);
    Route::get('/templates-lite', [ApiController::class, 'templatesLite']);

});