<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;

class UnitsCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // Table & Subject
            $crud->setTable('units');
            $crud->setSubject('Unit', 'Units');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columns (add timestamps only if they exist)
            $columns = ['id', 'system', 'type', 'unit', 'symbol'];
            if (Schema::hasColumn('units', 'created_at')) $columns[] = 'created_at';
            $crud->columns($columns);

            // Fields
            $crud->fields(['system', 'type', 'unit', 'symbol']);

            // Required
            $crud->requiredFields(['system', 'type', 'unit', 'symbol']);

            // Labels
            $crud->displayAs([
                'id'         => 'ID',
                'system'     => 'System',
                'type'       => 'Type',
                'unit'       => 'Unit',
                'symbol'     => 'Symbol',
                'created_at' => 'Created At'
            ]);

            // Field types
            // system: tinyint(1) -> dropdown
            $crud->fieldType('system', 'dropdown', [
                0 => 'Imperial/US',
                1 => 'Metric (SI)',
            ]);

            // Optional relation for "type" if a lookup exists (unit_types or unit_type)
            if (Schema::hasTable('unit_types')) {
                $display = Schema::hasColumn('unit_types', 'name') ? 'name'
                         : (Schema::hasColumn('unit_types', 'title') ? 'title' : null);
                if ($display) $crud->setRelation('type', 'unit_types', $display);
            } elseif (Schema::hasTable('unit_type')) {
                $display = Schema::hasColumn('unit_type', 'name') ? 'name'
                         : (Schema::hasColumn('unit_type', 'title') ? 'title' : null);
                if ($display) $crud->setRelation('type', 'unit_type', $display);
            }
            // Si no existe tabla de tipos, "type" queda como int sin relación.

            // Ordering
            $crud->defaultOrdering('id', 'desc');

            // (Opcional) Uniqueness “lógico”: evita duplicar mismo unit+symbol
            // (Grocery valida a nivel aplicación; si tienes unique en DB, mejor)
            // $crud->uniqueFields(['unit', 'symbol']);

            // Normalize/trim
            $crud->callbackBeforeInsert(function ($state) {
                foreach (['unit', 'symbol'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });
            $crud->callbackBeforeUpdate(function ($state) {
                foreach (['unit', 'symbol'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // Usa tu blade subido si quieres: 'admin.units.unit'
        // return view('admin.units.unit', $viewData);
        return view('admin.material.materialUnits', $viewData);
    }
}
