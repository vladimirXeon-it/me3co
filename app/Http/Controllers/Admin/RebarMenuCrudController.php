<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GcFactory;
use GroceryCrud\Core\GroceryCrud;

class RebarMenuCrudController extends Controller
{
    public function index(Request $request)
    {
        try {
            $crud = GcFactory::make();

            $crud->setTable('rebar_menu');
            $crud->setPrimaryKey('id', 'rebar_menu');
            $crud->setSubject('Rebar Menu', 'Rebar Menu');

            $crud->columns(['imperial', 'metric', 'peso_lf', 'short_ton', 'long_ton']);
            $crud->fields(['imperial', 'metric', 'peso_lf', 'short_ton', 'long_ton']);

            // ========== Opciones de interfaz ==========
            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            $crud->displayAs('imperial', 'Imperial');
            $crud->displayAs('metric', 'Metric');
            $crud->displayAs('peso_lf', 'Peso / LF');
            $crud->displayAs('short_ton', 'Short Ton');
            $crud->displayAs('long_ton', 'Long Ton');

            $crud->requiredFields(['imperial', 'metric', 'peso_lf']);

            $crud->setCsrfTokenName('_token');
            $crud->setCsrfTokenValue(csrf_token());

            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('charset', 'utf-8');
            }

            return view('admin.rebar_menu.rebarMenu', [
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
