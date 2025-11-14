<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Services\GcFactory;

trait BuildsCrud
{
    /**
     * Crea el CRUD con tu GcFactory y resuelve auto JSON/HTML.
     * $builder recibe el $crud para configurarlo (tabla, campos, etc.).
     */
    protected function renderCrud(Request $request, callable $builder)
    {
        try {
            // Usa tu factory (igual que en OpeningsCrudController)
            /** @var \App\Services\GcFactory $factory */
            $factory = app(GcFactory::class);
            /** @var GroceryCrud $crud */
            $crud = $factory->make();

            // Deja que el caller configure el CRUD
            $builder($crud);

            // Render
            $output = $crud->render();

            if (!empty($output->isJSONResponse)) {
                return response($output->output, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('charset', 'utf-8');
            }

            return [
                'css_files' => $output->css_files,
                'js_files'  => $output->js_files,
                'output'    => $output->output,
            ];
        } catch (\Throwable $e) {
            // Si el error ocurre en llamada XHR (?action=...), regresa texto plano
            if ($request->query('action')) {
                return response($e->getMessage() . "\n\n" . $e->getTraceAsString(), 500)
                    ->header('Content-Type', 'text/plain; charset=utf-8');
            }
            throw $e;
        }
    }
}
