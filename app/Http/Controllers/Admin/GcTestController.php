<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GroceryCrud\Core\GroceryCrud;
use GroceryCrud\Core\Database\LaravelDatabase;
use Illuminate\Http\Request;

class GcTestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $crud = new GroceryCrud(config('grocerycrud'), new LaravelDatabase());

            // users es una tabla estándar en Laravel: id, name, email...
            $crud->setTable('users');
            $crud->setPrimaryKey('id');
            $crud->setSubject('Usuario', 'Usuarios');

            // columnas “seguras” que siempre existen
            $crud->columns(['id', 'name', 'email']);
            $crud->fields(['name', 'email']);

            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)->header('Content-Type', 'application/json');
            }

            return view('gcrud.test', [
                'css_files' => $output->css_files,
                'js_files'  => $output->js_files,
                'output'    => $output->output,
            ]);
        } catch (\Throwable $e) {
            if ($request->query('action')) {
                // DEVOLVER el error tal cual en el XHR
                return response($e->getMessage()."\n\n".$e->getTraceAsString(), 500)
                    ->header('Content-Type', 'text/plain; charset=utf-8');
            }
            throw $e;
        }
    }
}
