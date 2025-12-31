<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MaterialsCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        try {

            $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

                $crud->setTable('materials');
                $crud->setSubject('Material', 'Materials');

                $crud->unsetExport();
                $crud->unsetPrint();
                $crud->unsetColumnsButton();
                $crud->unsetSettings();
                $crud->unsetFilters();
                $crud->unsetAdd();
                $crud->unsetEdit();

                // ✅ OJO: si esto te da 500 por “undefined method”, quítalo del CRUD
                // y pon el botón en la vista blade mejor.
                // $crud->setActionButton('New Material', route('admin.material.form.create'), false, 'fa fa-plus');
            });

            // 1) Si renderCrud devuelve una respuesta (redirect/response), respétala
            if ($viewData instanceof \Symfony\Component\HttpFoundation\Response) {
                return $viewData;
            }

            // 2) Si viene action=*, Grocery CRUD NECESITA JSON
            if ($request->query('action')) {
                return response()->json($viewData);
            }

            // 3) Render normal (HTML)
            // Asegura que lo que mandas a la vista sea array
            $data = is_array($viewData) ? $viewData : [
                'css_files' => $viewData->css_files ?? [],
                'js_files'  => $viewData->js_files ?? [],
                'output'    => $viewData->output ?? '',
            ];

            return view('admin.material.material', $data);

        } catch (Throwable $e) {

            Log::error('Materials CRUD failed', [
                'url' => $request->fullUrl(),
                'action' => $request->query('action'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Si era action=initial (ajax), responde JSON sí o sí
            if ($request->query('action')) {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'details' => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
    }

    public function form(Request $request, $id = null)
    {
        $material = $id ? DB::table('materials')->where('id', $id)->first() : null;

        $divisions = Schema::hasTable('material_divisions')
            ? DB::table('material_divisions')->orderBy('name')->pluck('name', 'id')->toArray()
            : [];

        $classes = Schema::hasTable('material_classes')
            ? DB::table('material_classes')->orderBy('name')->pluck('name', 'id')->toArray()
            : [];

        $types = Schema::hasTable('material_types')
            ? DB::table('material_types')->orderBy('name')->pluck('name', 'id')->toArray()
            : [];

        $units = Schema::hasTable('units')
            ? DB::table('units')->orderBy('unit')->pluck('unit', 'unit')->toArray()
            : [];

        // ✅ OJO: si "products" no existe en esta DB, NO revientes el modal
        $products = [];

        dd([
            'material'  => $material,
            'divisions' => $divisions,
            'classes'   => $classes,
            'types'     => $types,
            'units'     => $units,
            'products'  => $products,
            'isModal'   => true,
        ]);

        return view('admin.material.add', [
            'material'  => $material,
            'divisions' => $divisions,
            'classes'   => $classes,
            'types'     => $types,
            'units'     => $units,
            'products'  => $products,
            'isModal'   => true,
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
