<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;

class MaterialDivisionsCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            $crud->setTable('material_divisions');
            $crud->setSubject('Material Division', 'Material Divisions');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columns (tolerante si no hay updated_at)
            $columns = ['id', 'name'];
            if (Schema::hasColumn('material_divisions', 'created_at')) $columns[] = 'created_at';
            $crud->columns($columns);

            // Fields
            $crud->fields(['name']);

            // Rules
            $crud->requiredFields(['name']);
            $crud->uniqueFields(['name']);

            // Labels
            $crud->displayAs([
                'id'         => 'ID',
                'name'       => 'Material Division',
                'created_at' => 'Created At'
            ]);

            $crud->defaultOrdering('id', 'desc');

            // Trim
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

        // Usa tu blade subido si quieres:
        // return view('admin.material-divisions.division', $viewData);
        return view('admin.material.materialDivision', $viewData);
    }
}
