<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;

class LaborClassesCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // Tabla y sujeto
            $crud->setTable('labor_classes');
            $crud->setSubject('Labor Class', 'Labor Class');

            // Columnas visibles y campos editables
            $crud->columns(['id', 'name', 'created_at']);
            $crud->fields(['name']);

            // Validación mínima
            $crud->requiredFields(['name']);
            $crud->uniqueFields(['name']);

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Labels bonitos
            $crud->displayAs([
                'id'         => 'ID',
                'name'       => 'Name',
                'created_at' => 'Created_at'
            ]);

            // Orden por defecto
            $crud->defaultOrdering('id', 'desc');

            // Opcional: descomenta si NO quieres permitir borrar
            // $crud->unsetDelete();
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // Vista Blade
        return view('admin.labors.laborClass', $viewData);
    }
}
