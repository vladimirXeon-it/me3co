<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;

class MaterialClassesCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // Tabla y sujeto
            $crud->setTable('material_classes');
            $crud->setSubject('Material Class', 'Material Classes');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columnas base
            $columns = ['id', 'material_division_id', 'name'];
            if (Schema::hasColumn('material_classes', 'created_at')) $columns[] = 'created_at';
            $crud->columns($columns);

            // Campos editables
            $crud->fields(['material_division_id', 'name']);

            // Reglas
            $crud->requiredFields(['material_division_id', 'name']);
            // $crud->uniqueFields(['name']); // habilítalo si 'name' debe ser único

            // Labels
            $crud->displayAs([
                'id'                   => 'ID',
                'material_division_id' => 'Material Division',
                'name'                 => 'Material Class',
                'created_at'           => 'Created At'
            ]);

            // Relación (intenta con plural y singular, y con distintos campos de display)
            if (Schema::hasTable('material_divisions')) {
                $display = Schema::hasColumn('material_divisions','name') ? 'name'
                         : (Schema::hasColumn('material_divisions','title') ? 'title' : null);
                if ($display) $crud->setRelation('material_division_id', 'material_divisions', $display);
            } elseif (Schema::hasTable('material_division')) {
                $display = Schema::hasColumn('material_division','name') ? 'name'
                         : (Schema::hasColumn('material_division','title') ? 'title' : null);
                if ($display) $crud->setRelation('material_division_id', 'material_division', $display);
            }

            // Orden
            $crud->defaultOrdering('id', 'desc');

            // Normalización ligera
            $crud->callbackBeforeInsert(function ($state) {
                if (isset($state->data['name'])) $state->data['name'] = trim((string)$state->data['name']);
                return $state;
            });
            $crud->callbackBeforeUpdate(function ($state) {
                if (isset($state->data['name'])) $state->data['name'] = trim((string)$state->data['name']);
                return $state;
            });
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // Usa tu blade. Si prefieres el tuyo (class.blade.php), cámbialo aquí.
        return view('admin.material.materialClass', $viewData);
        // return view('admin.material-classes.class', $viewData); // <- si quieres mapear al que subiste
    }
}
