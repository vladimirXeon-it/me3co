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

            // Tabla y sujeto
            $crud->setTable('materials');
            $crud->setSubject('Material', 'Materials');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();

            // Columnas visibles en la grilla (resume lo importante)
            $columns = [
                'id', 'unique_id', 'name', 'material_division_id', 'material_class_id',
                'default_unit', 'measurement_unit', 'waste', 'production_rate',
                'created_at'
            ];
            // Oculta timestamps que no existan
            if (!Schema::hasColumn('materials', 'created_at')) {
                $columns = array_diff($columns, ['created_at']);
            }

            $crud->columns(array_values($columns));

            // Campos del formulario (edita lo que realmente capturas)
            $crud->fields([
                'material_division_id', 'material_class_id',
                'name', 'description',
                'unit_measure_value', 'default_unit',           // Default Unit Count + Unit
                'length', 'width', 'height',                    // Dimensiones (mm)
                'prices',                                       // $ /
                'waste',                                        // Waste %
                'production_rate',                              // Rate (units per day)
                'production_subed_out_cost', 'subbed_out_rate',// Subed out
                'cleaning_cost', 'cleaning_subed_out',          // Cleaning costs
                'associated_products'                           // Other Material Associated (repeater)
            ]);

            // Reglas
            $crud->requiredFields(['name', 'material_division_id', 'material_class_id']);
            $crud->uniqueFields(['unique_id']); // quítalo si no debe ser único

            // Labels
            $crud->displayAs([
                'material_division_id'      => 'Division',
                'material_class_id'         => 'Class',
                'name'                      => 'Name',
                'description'               => 'Description',
                'unit_measure_value'        => 'Default Unit Count',
                'default_unit'              => 'Unit',
                'length'                    => 'Length (mm)',
                'width'                     => 'Width (mm)',
                'height'                    => 'Height (mm)',
                'prices'                    => 'Price',
                'waste'                     => 'Waste (%)',
                'production_rate'           => 'Production Rate',
                'production_subed_out_cost' => 'Subed Out cost',
                'subbed_out_rate'           => 'OR (Subbed Out Rate)',
                'cleaning_cost'             => 'Cleaning Cost In-house',
                'cleaning_subed_out'        => 'Cleaning Subbed-out',
                'associated_products'       => 'Other Material Associated',
            ]);

            // Relaciones seguras
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
                $crud->setRelation('user_id', 'users', 'name');
            } elseif (Schema::hasTable('user') && Schema::hasColumn('user', 'name')) {
                $crud->setRelation('user_id', 'user', 'name');
            }

            if (Schema::hasTable('projects')) {
                $show = Schema::hasColumn('projects','title') ? 'title' :
                        (Schema::hasColumn('projects','name') ? 'name' : null);
                if ($show) $crud->setRelation('project_id', 'projects', $show);
            } elseif (Schema::hasTable('project')) {
                $show = Schema::hasColumn('project','title') ? 'title' :
                        (Schema::hasColumn('project','name') ? 'name' : null);
                if ($show) $crud->setRelation('project_id', 'project', $show);
            }

            if (Schema::hasTable('material_divisions') && Schema::hasColumn('material_divisions', 'name')) {
                $crud->setRelation('material_division_id', 'material_divisions', 'name');
            }

            if (Schema::hasTable('material_classes') && Schema::hasColumn('material_classes', 'name')) {
                $crud->setRelation('material_class_id', 'material_classes', 'name');
            }

            if (Schema::hasTable('material_types')) {
                $show = Schema::hasColumn('material_types','name') ? 'name' :
                        (Schema::hasColumn('material_types','title') ? 'title' : null);
                if ($show) $crud->setRelation('material_type_id', 'material_types', $show);
            }

            $crud->callbackAddField('prices', function () {
                // Render 2 inputs visibles y 1 hidden para enviar JSON a 'prices'
                return '
                <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" class="form-control" id="price_amount" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-1 text-center align-self-center">/</div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="price_per" placeholder="Unit (e.g. piece)">
                </div>
                <input type="hidden" name="prices" id="prices_json">
                </div>
                <small class="form-text text-muted">Stored as JSON: {"amount":x,"per":"unit"}</small>
                ';
            });

            // (Opcional) al editar, parsea JSON a los 2 inputs
            $crud->callbackEditField('prices', function ($value) {
                $amount = ''; $per = '';
                if (!empty($value)) {
                    $j = json_decode($value, true);
                    $amount = $j['amount'] ?? '';
                    $per    = $j['per'] ?? '';
                }
                return '
                <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0" class="form-control" id="price_amount" value="'.e($amount).'">
                    </div>
                </div>
                <div class="col-md-1 text-center align-self-center">/</div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="price_per" value="'.e($per).'" placeholder="Unit (e.g. piece)">
                </div>
                <input type="hidden" name="prices" id="prices_json">
                </div>
                <small class="form-text text-muted">Stored as JSON: {"amount":x,"per":"unit"}</small>
                ';
            });

            // ===== CAMPO associated_products: repeater como en “Other Material Associated” =====
            $crud->callbackAddField('associated_products', function () {
                return '
                <div id="assoc_wrap">
                <div class="row g-2 assoc_row">
                    <div class="col-md-4">
                    <input type="text" class="form-control assoc_material" placeholder="Material">
                    </div>
                    <div class="col-md-2">
                    <input type="number" step="0.001" min="0" class="form-control assoc_count" placeholder="Count">
                    </div>
                    <div class="col-md-2">
                    <input type="text" class="form-control assoc_unit" placeholder="Unit">
                    </div>
                    <div class="col-md-2 text-center align-self-center">For every</div>
                    <div class="col-md-2">
                    <input type="number" step="0.001" min="0" class="form-control assoc_for_count" placeholder="Count">
                    </div>
                </div>
                </div>
                <button type="button" class="btn btn-sm btn-warning mt-2" id="assoc_add">Add More</button>
                <input type="hidden" name="associated_products" id="assoc_json">
                <small class="form-text text-muted">JSON array of rows.</small>
                ';
            });

            $crud->callbackEditField('associated_products', function ($value) {
                $rows = [];
                if (!empty($value)) {
                    $rows = json_decode($value, true);
                    if (!is_array($rows)) $rows = [];
                }
                $html = '<div id="assoc_wrap">';
                if (empty($rows)) $rows = [['material'=>'','count'=>'','unit'=>'','for_count'=>'']];
                foreach ($rows as $r) {
                    $html .= '
                    <div class="row g-2 assoc_row">
                    <div class="col-md-4"><input type="text" class="form-control assoc_material" value="'.e($r['material'] ?? '').'" placeholder="Material"></div>
                    <div class="col-md-2"><input type="number" step="0.001" min="0" class="form-control assoc_count" value="'.e($r['count'] ?? '').'" placeholder="Count"></div>
                    <div class="col-md-2"><input type="text" class="form-control assoc_unit" value="'.e($r['unit'] ?? '').'" placeholder="Unit"></div>
                    <div class="col-md-2 text-center align-self-center">For every</div>
                    <div class="col-md-2"><input type="number" step="0.001" min="0" class="form-control assoc_for_count" value="'.e($r['for_count'] ?? '').'" placeholder="Count"></div>
                    </div>';
                }
                $html .= '</div>
                <button type="button" class="btn btn-sm btn-warning mt-2" id="assoc_add">Add More</button>
                <input type="hidden" name="associated_products" id="assoc_json">
                <small class="form-text text-muted">JSON array of rows.</small>';
                return $html;
            });

            // ===== JS: serializa los campos compuestos ANTES de enviar =====
            $crud->callbackAddField('name', function () { // “enganche” para inyectar el JS una sola vez
                return '
                <input name="name" type="text" class="form-control" required>
                <script>
                    (function () {
                    const gcForm = document.querySelector("form#gcrud-form");
                    if (!gcForm || gcForm.dataset.enhanced) return;
                    gcForm.dataset.enhanced = "1";

                    function serializePrice() {
                        const amount = document.getElementById("price_amount")?.value || "";
                        const per    = document.getElementById("price_per")?.value || "";
                        document.getElementById("prices_json")?.setAttribute("value", JSON.stringify({amount: amount || null, per: per || null}));
                    }

                    function serializeAssoc() {
                        const wrap = document.getElementById("assoc_wrap");
                        if (!wrap) return;
                        const rows = [...wrap.querySelectorAll(".assoc_row")].map(r => ({
                        material:   r.querySelector(".assoc_material")?.value || "",
                        count:      r.querySelector(".assoc_count")?.value || "",
                        unit:       r.querySelector(".assoc_unit")?.value || "",
                        for_count:  r.querySelector(".assoc_for_count")?.value || ""
                        })).filter(x => x.material || x.count || x.unit || x.for_count);
                        document.getElementById("assoc_json")?.setAttribute("value", JSON.stringify(rows));
                    }

                    // botón Add More
                    document.getElementById("assoc_add")?.addEventListener("click", function(){
                        const wrap = document.getElementById("assoc_wrap");
                        if (!wrap) return;
                        const div = document.createElement("div");
                        div.className = "row g-2 assoc_row mt-2";
                        div.innerHTML = `
                        <div class="col-md-4"><input type="text" class="form-control assoc_material" placeholder="Material"></div>
                        <div class="col-md-2"><input type="number" step="0.001" min="0" class="form-control assoc_count" placeholder="Count"></div>
                        <div class="col-md-2"><input type="text" class="form-control assoc_unit" placeholder="Unit"></div>
                        <div class="col-md-2 text-center align-self-center">For every</div>
                        <div class="col-md-2"><input type="number" step="0.001" min="0" class="form-control assoc_for_count" placeholder="Count"></div>
                        `;
                        wrap.appendChild(div);
                    });

                    // hook submit
                    gcForm.addEventListener("submit", function(){
                        serializePrice();
                        serializeAssoc();
                    });
                    })();
                </script>
                ';
            });

            // Dropdowns para campos varchar basados en units
            if (Schema::hasTable('units')) {
                // prioriza 'symbol' para mostrar; guarda el mismo valor en el campo varchar
                $unitDisplay = Schema::hasColumn('units','symbol') ? 'symbol' :
                               (Schema::hasColumn('units','unit') ? 'unit' : null);

                if ($unitDisplay) {
                    $units = DB::table('units')
                        ->orderBy($unitDisplay)
                        ->pluck($unitDisplay, $unitDisplay) // ['kg' => 'kg']
                        ->toArray();

                    $crud->fieldType('default_unit', 'dropdown', $units);
                    $crud->fieldType('measurement_unit', 'dropdown', $units);
                }
            }

            // Tipos numéricos (opcional, ayuda visual)
            foreach ([
                'height','width','length','unit_measure_value','weight_lf','sq_ft_per_cy','shortton_wlf',
                'waste','production_rate','production_subed_out_cost','cleaning_cost','cleaning_subed_out','subbed_out_rate'
            ] as $numericField) {
                if (Schema::hasColumn('materials', $numericField)) {
                    $crud->fieldType($numericField, 'decimal');
                }
            }

            // Orden por defecto
            $crud->defaultOrdering('id', 'desc');

            // Normalizaciones
            $crud->callbackBeforeInsert(function ($state) {
                foreach (['unique_id','name','default_unit','measurement_unit'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });
            $crud->callbackBeforeUpdate(function ($state) {
                foreach (['unique_id','name','default_unit','measurement_unit'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                return $state;
            });
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // Usa tus blades de /resources/views/admin/material/
        return view('admin.material.material', $viewData);
    }

    public function form(Request $request, $id = null)
    {
        $material = null;
        if ($id) {
            $material = DB::table('materials')->where('id', $id)->first(); // o Material::findOrFail($id);
        }

        // Catálogos (ajusta a tus tablas reales)
        $divisions = DB::table('material_divisions')->orderBy('name')->pluck('name', 'id')->toArray();
        $classes   = DB::table('material_classes')->orderBy('name')->pluck('name', 'id')->toArray();
        $types     = DB::table('material_types')->orderBy('name')->pluck('name', 'id')->toArray();
        $units     = DB::table('units')->orderBy('unit')->pluck('unit', 'unit')->toArray();
        $products  = DB::table('products')->orderBy('name')->pluck('name', 'id')->toArray();

        // ⚡ MUY IMPORTANTE:
        // Enviamos $isModal=true para que add.blade.php rinda SOLO el <form> (sin layout)
        return view('admin.material.add', [
            'material'  => $material,
            'divisions' => $divisions,
            'classes'   => $classes,
            'types'     => $types,
            'units'     => $units,
            'products'  => $products,
            'isModal'   => true
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

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'message' => '✓ Record Created', 'id' => $id]);
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

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'message' => '✓ Record Updated', 'id' => $id]);
        }

        return redirect()->to('admin/material')->with('message', '✓ Record Updated!');
    }

}
