<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;

class PlansCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // Table & subject
            $crud->setTable('plans');
            $crud->setSubject('Plan', 'Plans');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columns (muestra timestamps solo si existen)
            $cols = ['id', 'name', 'price'];
            if (Schema::hasColumn('plans', 'created_at')) $cols[] = 'created_at';
            $crud->columns($cols);

            // Fields
            $crud->fields([
                'name',
                'type',
                'price',
                'time_unit',
                'project_allowed',
                'description',
            ]);

            // Rules
            $crud->requiredFields(['name', 'type', 'price', 'time_unit', 'project_allowed']);
            $crud->uniqueFields(['name']);

            // Labels
            $crud->displayAs([
                'id'              => 'ID',
                'name'            => 'Name',
                'type'            => 'Subscription Type',
                'price'           => 'Price',
                'time_unit'       => 'Time Unit',
                'project_allowed' => 'Number Of Project Allowed',
                'description'     => 'Details',
                'created_at'      => 'Created At',
            ]);

            // Field types
            // Ajusta las opciones si manejas otros valores en DB
            $crud->fieldType('type', 'dropdown', [
                0 => 'Free',
                1 => 'Pro',
                2 => 'Business',
                3 => 'Enterprise',
            ]);

            $crud->fieldType('time_unit', 'dropdown', [
                0 => 'Day',
                1 => 'Month',
                2 => 'Year',
            ]);

            // Numéricos
            $crud->fieldType('price', 'integer');
            $crud->fieldType('project_allowed', 'integer');

            // Orden
            $crud->defaultOrdering('id', 'desc');

            // Trims
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

        // Usa tus blades subidos (index/edit) bajo admin/plans/
        return view('admin.subscriptions.plans', $viewData);
    }
}
