<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GcFactory;
use GroceryCrud\Core\GroceryCrud;

class CompaniesCrudController extends Controller
{
    public function index(Request $request)
    {
        try {

            $crud = GcFactory::make();

            // 👉 usa el nombre de tabla real
            $crud->setTable('companies');

            // 👉 en v3: PK + nombre de tabla (y respeta minúsculas)
            $crud->setPrimaryKey('id', 'companies');

            $crud->setSubject('Companies', 'Companies');

            $crud->setRelation('user_id', 'users', 'name');

            $crud->unsetAdd();
            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // columnas que EXISTEN en esa tabla
            $crud->columns(['user_id','name','estimator','phone','email','address','city','state','country','zip','created_at']);
            $crud->fields(['name','estimator', 'phone', 'email', 'address','city','state', 'country','zip']);
            $crud->displayAs('user_id','User');
            $crud->displayAs('name','Company Name');
            $crud->displayAs('estimator','Estimator Name');
            $crud->displayAs('zip','Zip Code');
            $crud->displayAs('created_at','Created At');
            //$crud->requiredFields(['name','Description']);

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

            return view('admin.company.companies', [
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
