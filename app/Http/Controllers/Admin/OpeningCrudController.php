<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GcFactory;
use GroceryCrud\Core\GroceryCrud;

class OpeningsCrudController extends Controller
{
    public function index(Request $request)
    {
        try {

            $crud = GcFactory::make();

            // 👉 usa el nombre de tabla real
            $crud->setTable('openings');

            // 👉 en v3: PK + nombre de tabla (y respeta minúsculas)
            $crud->setPrimaryKey('id', 'openings');

            $crud->setSubject('Openings', 'Openings');

            // columnas que EXISTEN en esa tabla
            /*$crud->columns(['name','description','created_at']);
            $crud->fields(['name','description']);
            $crud->displayAs('name','Crew');
            $crud->displayAs('description','Description');
            $crud->displayAs('cost_per_day','Cost Per day');
            $crud->displayAs('created_at','Created At');
            $crud->requiredFields(['name','Description']);*/

            // 6) CSRF (no excluimos el middleware)
            $crud->setCsrfTokenName('_token');
            $crud->setCsrfTokenValue(csrf_token());

            // 7) (Opcional) callback para user_id
            $crud->callbackBeforeInsert(function ($state) {
                $data = $state->data ?? [];
                $data['user_id'] = $data['user_id'] ?? 0;
                $state->data = $data;
                return $state;
            });

            // 8) Render
            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('charset', 'utf-8');
            }

            return view('admin.openings.openings', [
                'css_files' => $output->css_files,
                'js_files'  => $output->js_files,
                'output'    => $output->output,
            ]);
        } catch (\Throwable $e) {
            // Si algo falla en el XHR (?action=...), te regreso el detalle en texto
            if ($request->query('action')) {
                return response($e->getMessage()."\n\n".$e->getTraceAsString(), 500)
                    ->header('Content-Type', 'text/plain; charset=utf-8');
            }
            throw $e;
        }
    }
}
