<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GcFactory;
use GroceryCrud\Core\GroceryCrud;

class PitchCrudController extends Controller
{
    public function index(Request $request)
    {
        try {

            $crud = GcFactory::make();

            // tabla pitch
            $crud->setTable('pitch');

            // PK
            $crud->setPrimaryKey('id', 'pitch');

            // nombres
            $crud->setSubject('Pitch', 'Pitch');

            // ========== Opciones de interfaz ==========
            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // ========== Columnas ==========
            $crud->columns(['id', 'name', 'value']);
            $crud->fields(['name', 'value']);

            // ========== Labels ==========
            $crud->displayAs('id', 'ID');
            $crud->displayAs('name', 'Name');
            $crud->displayAs('value', 'Value');

            // ========== Validaciones ==========
            $crud->requiredFields(['name', 'value']);

            // CSRF
            $crud->setCsrfTokenName('_token');
            $crud->setCsrfTokenValue(csrf_token());

            // ========== Render ==========
            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('charset', 'utf-8');
            }

            return view('admin.pitch.pitch', [
                'css_files' => $output->css_files,
                'js_files'  => $output->js_files,
                'output'    => $output->output,
            ]);

        } catch (\Throwable $e) {

            if ($request->query('action')) {
                return response(
                    $e->getMessage() . "\n\n" . $e->getTraceAsString(),
                    500
                )->header('Content-Type', 'text/plain; charset=utf-8');
            }

            throw $e;
        }
    }
}
