<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GcFactory;
use GroceryCrud\Core\GroceryCrud;

class GroutBlockCrudController extends Controller
{
    public function index(Request $request)
    {
        try {
            $crud = GcFactory::make();

            $crud->setTable('grout_block');
            $crud->setPrimaryKey('id', 'grout_block');
            $crud->setSubject('Grout Block', 'Grout Blocks');

            // Campos visibles
            $crud->columns(['name', 'grout', 'other_fill']);
            $crud->fields(['name', 'grout', 'other_fill']);

            // Labels
            $crud->displayAs('name', 'Name');
            $crud->displayAs('grout', 'Grout (cy)');
            $crud->displayAs('other_fill', 'Other Fill');

            // Validación
            $crud->requiredFields(['name']);

            // CSRF
            $crud->setCsrfTokenName('_token');
            $crud->setCsrfTokenValue(csrf_token());

            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('charset', 'utf-8');
            }

            return view('admin.grout_block.groutBlock', [
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
