<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MaterialsCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // ===== CONFIGURACIÓN DEL CRUD =====

            $crud->setTable('materials');
            $crud->setSubject('Material', 'Materials');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();
            $crud->unsetAdd();
            $crud->unsetEdit();

            $columns = [
                'id', 'unique_id', 'name', 'material_division_id', 'material_class_id',
                'default_unit', 'measurement_unit', 'waste', 'production_rate', 'created_at'
            ];

            if (!Schema::hasColumn('materials', 'created_at')) {
                $columns = array_diff($columns, ['created_at']);
            }

            $crud->columns(array_values($columns));

            $crud->setActionButton('New Material', route('admin.material.form.create'), false, 'fa fa-plus');

            // TODO: tus callbacks (precios, repeaters, etc.)
            // Aquí no modifico nada, funcionan igual.

        });

        // ====== CORRECCIÓN CRÍTICA ======
        // Grocery CRUD SÓLO ES FELIZ SI REGRESA JSON en AJAX

        if (isset($viewData->isJSONResponse) && $viewData->isJSONResponse === true) {
            return response()->json($viewData->data);
        }

        // Para redirecciones internas de GC
        if ($viewData instanceof \Illuminate\Http\RedirectResponse) {
            return $viewData;
        }

        // ====== SALIDA NORMAL (HTML) ======
        return view('admin.material.material', [
            'css_files' => $viewData->css_files ?? [],
            'js_files'  => $viewData->js_files ?? [],
            'output'    => $viewData->output ?? '',
        ]);
    }

    public function form(Request $request, $id = null)
    {
        $material = $id ? DB::table('materials')->where('id', $id)->first() : null;

        $divisions = DB::table('material_divisions')->orderBy('name')->pluck('name', 'id')->toArray();
        $classes   = DB::table('material_classes')->orderBy('name')->pluck('name', 'id')->toArray();
        $types     = DB::table('material_types')->orderBy('name')->pluck('name', 'id')->toArray();
        $units     = DB::table('units')->orderBy('unit')->pluck('unit', 'unit')->toArray();
        $products  = DB::table('products')->orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.material.add', [
            'material'  => $material,
            'divisions' => $divisions,
            'classes'   => $classes,
            'types'     => $types,
            'units'     => $units,
            'products'  => $products,
            'isModal'   => true, // fuerza modo modal SIEMPRE
        ]);
    }

    public function create_material(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'material_class_id'    => 'required|integer',
            'material_division_id' => 'required|integer',
            'material_type_id'     => 'nullable|integer',
            'measurement_unit'     => 'required|string',
            'unit_measure_value'   => 'nullable|numeric',
            'default_unit'         => 'required|string',
            'prices'               => 'nullable|string',
            'production_rate'      => 'nullable|numeric',
            'production_subed_out_cost' => 'nullable|numeric',
            'cleaning_cost'        => 'nullable|numeric',
            'cleaning_subed_out'   => 'nullable|numeric',
            'height'               => 'nullable|numeric',
            'width'                => 'nullable|numeric',
            'length'               => 'nullable|numeric',
            'waste'                => 'nullable|numeric',
            'associated_products'  => 'array'
        ]);

        $data = [
            'user_id'                   => 0,
            'name'                      => $request->post('name'),
            'material_class_id'         => $request->post('material_class_id'),
            'material_type_id'          => $request->post('material_type_id'),
            'material_division_id'      => $request->post('material_division_id'),
            'description'               => $request->post('description'),
            'measurement_unit'          => $request->post('measurement_unit'),
            'unit_measure_value'        => $request->post('unit_measure_value'),
            'default_unit'              => $request->post('default_unit'),
            'unique_id'                 => function_exists('generate_material_id')
                                            ? \generate_material_id($request->post('material_class_id'))
                                            : \Str::upper(\Str::random(6)),
            'height'                    => $request->post('height'),
            'width'                     => $request->post('width'),
            'length'                    => $request->post('length'),
            'waste'                     => $request->post('waste'),
            'prices'                    => $request->post('prices'),
            'production_rate'           => $request->post('production_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost'             => $request->post('cleaning_cost'),
            'cleaning_subed_out'        => $request->post('cleaning_subed_out'),
            'associated_products'       => json_encode($request->post('associated_products', [])),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];

        $id = DB::table('materials')->insertGetId($data);

        if ($request->ajax() || $request->query('modal') == 1) {
            return response("
                <script>
                    parent.jQuery.fancybox.close();
                    parent.location.reload();
                </script>
            ");
        }

        return redirect()->to('admin/material')->with('message', '✓ Record Created!');
    }

    public function update_material(Request $request, $id)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'material_class_id'    => 'required|integer',
            'material_division_id' => 'required|integer',
            'material_type_id'     => 'nullable|integer',
            'measurement_unit'     => 'required|string',
            'unit_measure_value'   => 'nullable|numeric',
            'default_unit'         => 'required|string',
            'prices'               => 'nullable|string',
            'production_rate'      => 'nullable|numeric',
            'production_subed_out_cost' => 'nullable|numeric',
            'cleaning_cost'        => 'nullable|numeric',
            'cleaning_subed_out'   => 'nullable|numeric',
            'height'               => 'nullable|numeric',
            'width'                => 'nullable|numeric',
            'length'               => 'nullable|numeric',
            'waste'                => 'nullable|numeric',
            'associated_products'  => 'array'
        ]);

        $data = [
            'name'                      => $request->post('name'),
            'material_class_id'         => $request->post('material_class_id'),
            'material_type_id'          => $request->post('material_type_id'),
            'material_division_id'      => $request->post('material_division_id'),
            'description'               => $request->post('description'),
            'measurement_unit'          => $request->post('measurement_unit'),
            'unit_measure_value'        => $request->post('unit_measure_value'),
            'default_unit'              => $request->post('default_unit'),
            'height'                    => $request->post('height'),
            'width'                     => $request->post('width'),
            'length'                    => $request->post('length'),
            'waste'                     => $request->post('waste'),
            'prices'                    => $request->post('prices'),
            'production_rate'           => $request->post('production_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost'             => $request->post('cleaning_cost'),
            'cleaning_subed_out'        => $request->post('cleaning_subed_out'),
            'associated_products'       => json_encode($request->post('associated_products', [])),
            'updated_at'                => now(),
        ];

        DB::table('materials')->where('id', $id)->update($data);

        if ($request->ajax() || $request->query('modal') == 1) {
            return response("
                <script>
                    parent.jQuery.fancybox.close();
                    parent.location.reload();
                </script>
            ");
        }

        return redirect()->to('admin/material')->with('message', '✓ Record Updated!');
    }

}
