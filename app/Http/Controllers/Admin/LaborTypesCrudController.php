<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;   // para validar tablas/columnas antes de setRelation

class LaborTypesCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // === MUESTRA LA TABLA "labors" EN ESTA VISTA ===
            $crud->setTable('labors');
            $crud->setSubject('Labor', 'Labors');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columnas / campos
            $crud->columns([
                'id',
                'unique_id',
                'user_id',
                'project_id',
                'labor_class_id',
                'labor_type',
                'cost_per_hour',
                'burdens',
                'total_cost',
                'created_at',
                'updated_at'
            ]);

            $crud->fields([
                'unique_id',
                'user_id',
                'project_id',
                'labor_class_id',
                'labor_type',
                'cost_per_hour',
                'burdens',
                'total_cost'
            ]);

            $crud->requiredFields(['user_id','project_id','labor_class_id','labor_type','cost_per_hour']);
            $crud->uniqueFields(['unique_id']);

            $crud->displayAs([
                'id'             => 'ID',
                'unique_id'      => 'Unique ID',
                'user_id'        => 'User',
                'project_id'     => 'Project',
                'labor_class_id' => 'Class',
                'labor_type'     => 'Type',
                'cost_per_hour'  => 'Cost per Hour',
                'burdens'        => 'Burdens / Notes',
                'total_cost'     => 'Total Cost',
                'created_at'     => 'Created At',
                'updated_at'     => 'Updated At',
            ]);

            // ===== Relaciones seguras (no revienta si cambian nombres) =====
            // users/user -> name
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
                $crud->setRelation('user_id', 'users', 'name');
            } elseif (Schema::hasTable('user') && Schema::hasColumn('user', 'name')) {
                $crud->setRelation('user_id', 'user', 'name');
            }

            // projects/project -> title|name
            if (Schema::hasTable('projects')) {
                $display = Schema::hasColumn('projects','title') ? 'title' :
                           (Schema::hasColumn('projects','name')  ? 'name'  : null);
                if ($display) $crud->setRelation('project_id', 'projects', $display);
            } elseif (Schema::hasTable('project')) {
                $display = Schema::hasColumn('project','title') ? 'title' :
                           (Schema::hasColumn('project','name')  ? 'name'  : null);
                if ($display) $crud->setRelation('project_id', 'project', $display);
            }

            // labor_classes -> name
            if (Schema::hasTable('labor_classes') && Schema::hasColumn('labor_classes','name')) {
                $crud->setRelation('labor_class_id', 'labor_classes', 'name');
            }

            $crud->defaultOrdering('id', 'desc');

            // Normalizaciones ligeras
            $crud->callbackBeforeInsert(function ($state) {
                foreach (['unique_id','labor_type'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });

            $crud->callbackBeforeUpdate(function ($state) {
                foreach (['unique_id','labor_type'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // IMPORTANTE: mantenemos la vista de "labor-types" pero renderizamos la tabla LABORS
        return view('admin.labors.labors', $viewData);
    }
}
