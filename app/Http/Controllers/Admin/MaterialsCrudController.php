<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

                // ✅ Re-enabled Add & Edit
                // $crud->unsetAdd();
                // $crud->unsetEdit();

                $crud->displayAs([
                    'material_type_id' => 'Type',
                    'material_division_id' => 'Division',
                    'material_class_id' => 'Class',
                    'name' => 'Material Name',
                    'description' => 'Description',
                    'prices' => 'Price',
                    'default_unit' => 'Default Unit Count',
                    'length' => 'Length (mm)',
                    'width' => 'Width (mm)',
                    'height' => 'Height (mm)',
                    'weight_lf' => 'Weight Per Lf',
                    'sq_ft_per_cy' => 'Sq ft per Cy',
                    'production_rate' => 'Production Rate',
                    'production_subed_out_cost' => 'Subed Out cost',
                    'subbed_out_rate' => 'Subed Out rate(day)',
                    'cleaning_cost' => 'Cleaning Cost Inhouse',
                    'cleaning_subed_out' => 'Cleaning Subbed out',
                    'associated_products' => 'Other Material Associated'
                ]);

                $crud->setRelation('material_type_id', 'material_types', 'name');
                $crud->setRelation('material_division_id', 'material_divisions', 'name');
                $crud->setRelation('material_class_id', 'material_classes', 'name');

                $crud->requiredFields(['name', 'material_division_id', 'material_class_id', 'default_unit']);

                // ✅ Custom Field Callbacks (Dimensions & Associated)
                $crud->callbackAddField('length', function () {
                        return $this->getDimensionHtml('length', 0);
                    }
                    );
                    $crud->callbackEditField('length', function ($value) {
                        return $this->getDimensionHtml('length', $value);
                    }
                    );

                    $crud->callbackAddField('width', function () {
                        return $this->getDimensionHtml('width', 0);
                    }
                    );
                    $crud->callbackEditField('width', function ($value) {
                        return $this->getDimensionHtml('width', $value);
                    }
                    );

                    $crud->callbackAddField('height', function () {
                        return $this->getDimensionHtml('height', 0);
                    }
                    );
                    $crud->callbackEditField('height', function ($value) {
                        return $this->getDimensionHtml('height', $value);
                    }
                    );

                    $crud->callbackAddField('associated_products', function () {
                        return $this->getAssociatedProductsHtml([]);
                    }
                    );
                    $crud->callbackEditField('associated_products', function ($value) {
                        return $this->getAssociatedProductsHtml(json_decode($value) ?: []);
                    }
                    );

                    // ✅ Trigger for general form init (using name field)
                    $crud->callbackAddField('name', function () {
                        return '<input name="name" type="text" class="form-control material_name" required>
                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="if(window.MaterialsManager) window.MaterialsManager.initForm(this.parentElement)">';
                    }
                    );
                    $crud->callbackEditField('name', function ($value) {
                        return '<input name="name" type="text" class="form-control material_name" value="' . htmlspecialchars($value) . '" required>
                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="if(window.MaterialsManager) window.MaterialsManager.initForm(this.parentElement)">';
                    }
                    );

                    $crud->callbackAddField('production_rate', function () {
                        return '<div class="production-rate-group">
                                    <div class="input-group">
                                        <span class="input-group-text display-unit">Piece</span>
                                        <input type="number" step="any" class="form-control optional_field" name="production_rate">
                                        <span class="input-group-text">Piece Per</span>
                                        <select class="form-select" style="max-width: 100px;">
                                            <option>Day</option>
                                            <option>Hour</option>
                                        </select>
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );
                    $crud->callbackEditField('production_rate', function ($value) {
                        return '<div class="production-rate-group">
                                    <div class="input-group">
                                        <span class="input-group-text display-unit">Piece</span>
                                        <input type="number" step="any" class="form-control optional_field" name="production_rate" value="' . htmlspecialchars($value) . '">
                                        <span class="input-group-text">Piece Per</span>
                                        <select class="form-select" style="max-width: 100px;">
                                            <option>Day</option>
                                            <option>Hour</option>
                                        </select>
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );

                    $crud->callbackAddField('production_subed_out_cost', function () {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control optional_field" name="production_subed_out_cost">
                                </div>';
                    }
                    );
                    $crud->callbackEditField('production_subed_out_cost', function ($value) {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control optional_field" name="production_subed_out_cost" value="' . htmlspecialchars($value) . '">
                                </div>';
                    }
                    );

                    // Wrap Subed Out Rate to include 'OR' and specific label
                    $crud->callbackAddField('subbed_out_rate', function () {
                        return '<div class="subbed-rate-group">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="any" class="form-control" name="subbed_out_rate">
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );
                    $crud->callbackEditField('subbed_out_rate', function ($value) {
                        return '<div class="subbed-rate-group">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="any" class="form-control" name="subbed_out_rate" value="' . htmlspecialchars($value) . '">
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );

                    $crud->callbackAddField('cleaning_cost', function () {
                        return '<div class="cleaning-inhouse-group">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="any" class="form-control optional_field" name="cleaning_cost">
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );
                    $crud->callbackEditField('cleaning_cost', function ($value) {
                        return '<div class="cleaning-inhouse-group">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="any" class="form-control optional_field" name="cleaning_cost" value="' . htmlspecialchars($value) . '">
                                    </div>
                                    <div class="or-separator">OR</div>
                                </div>';
                    }
                    );

                    $crud->callbackAddField('cleaning_subed_out', function () {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control optional_field" name="cleaning_subed_out">
                                </div>';
                    }
                    );
                    $crud->callbackEditField('cleaning_subed_out', function ($value) {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control optional_field" name="cleaning_subed_out" value="' . htmlspecialchars($value) . '">';
                    }
                    );

                    $crud->callbackAddField('prices', function () {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control" name="prices">
                                    <span class="input-group-text display-unit-suffix">$ / Piece</span>
                                </div>';
                    }
                    );
                    $crud->callbackEditField('prices', function ($value) {
                        return '<div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="any" class="form-control" name="prices" value="' . htmlspecialchars($value) . '">
                                    <span class="input-group-text display-unit-suffix">$ / Piece</span>
                                </div>';
                    }
                    );

                    // Set layout (2 columns for most fields)
                    $fields = [
                        'material_type_id', 'material_division_id', 'material_class_id',
                        'name', 'description', 'default_unit',
                        'length', 'width', 'height',
                        'weight_lf', 'sq_ft_per_cy',
                        'prices', 'waste',
                        'production_rate',
                        'production_subed_out_cost', 'subbed_out_rate',
                        'cleaning_cost', 'cleaning_subed_out',
                        'associated_products'
                    ];
                    $crud->addFields($fields);
                    $crud->editFields($fields);
                    $crud->columns(['name', 'material_type_id', 'material_division_id', 'material_class_id', 'prices', 'default_unit']);
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
                'js_files' => $viewData->js_files ?? [],
                'output' => $viewData->output ?? '',
            ];

            return view('admin.material.material', $data);

        }
        catch (Throwable $e) {

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

    private function getDimensionHtml($field, $value)
    {
        $units = get_length_units();
        $valStr = (string)($value ?: '0.0');
        $parts = explode('.', $valStr);
        $whole = $parts[0];
        $decimal = isset($parts[1]) ? '0.' . $parts[1] : 0;

        // Basic fraction conversion (simplified for the UI)
        $sup = 0;
        $sub = 0;
        if ($decimal > 0) {
        // This is just a placeholder, the JS will handle the actual display if needed
        // or we could use a helper to convert decimal to fraction
        }

        return '
            <div class="dimension-container" data-field="' . $field . '">
                <input type="hidden" name="' . $field . '" id="gc-' . $field . '" value="' . $value . '">
                <div class="form-control fraction-input" data-target="gc-' . $field . '" tabindex="-1">
                    <div class="whole" contenteditable="true">' . $whole . '</div>
                    <div class="fraction">
                        <div class="sup" contenteditable="true">' . $sup . '</div>
                        <hr>
                        <div class="sub" contenteditable="true">' . $sub . '</div>
                    </div>
                </div>
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="if(window.MaterialsManager) window.MaterialsManager.initDimensions(this.parentElement)">
            </div>';
    }

    private function getAssociatedProductsHtml($data)
    {
        $materials = get_materials();
        $options = '<option value="">Choose</option>';
        foreach ($materials as $m) {
            $options .= '<option value="' . $m->id . '" data-unit="' . $m->default_unit . '">' . htmlspecialchars($m->name) . '</option>';
        }

        $html = '<div class="associated-products-manager">
                    <h5 class="text-center mb-3">Other Material Associated</h5>
                    <div class="more-material">';

        foreach ($data as $key => $item) {
            $m_id = $item->material_id ?? '';
            $req = $item->required ?? 0;
            $unit = $item->unit ?? '';
            $for = $item->for ?? 1;

            $currentOptions = '<option value="">Choose</option>';
            foreach ($materials as $m) {
                $sel = ($m->id == $m_id) ? 'selected' : '';
                $currentOptions .= '<option value="' . $m->id . '" data-unit="' . $m->default_unit . '" ' . $sel . '>' . htmlspecialchars($m->name) . '</option>';
            }

            $html .= '
                <div class="row mat-field mb-2 g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold">Material</label>
                        <select class="form-control form-control-sm other_material" name="associated_products[' . $key . '][material_id]">
                            ' . $currentOptions . '
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="small fw-bold">Count</label>
                        <input type="number" step="any" class="form-control form-control-sm" name="associated_products[' . $key . '][required]" value="' . $req . '">
                    </div>
                    <div class="col-md-1">
                        <label class="small fw-bold">Unit</label>
                        <input class="form-control form-control-sm other_material_unit" name="associated_products[' . $key . '][unit]" value="' . $unit . '" readonly>
                    </div>
                    <div class="col-md-1 text-center small fw-bold pb-2">For every</div>
                    <div class="col-md-1">
                        <label class="small fw-bold">Count</label>
                        <input type="number" step="any" class="form-control form-control-sm" name="associated_products[' . $key . '][for]" value="' . $for . '">
                    </div>
                    <div class="col-md-1">
                        <label class="small fw-bold">Unit</label>
                        <input class="form-control form-control-sm display-unit" value="Unit" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Material</label>
                        <input class="form-control form-control-sm main-material-name" value="Material Name" readonly>
                    </div>
                    <div class="col-md-1 text-end pb-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove"><i class="fa fa-trash"></i></button>
                    </div>
                </div>';
        }

        $html .= '</div>
                  <button type="button" class="btn btn-sm btn-info add-more mt-2">Add More</button>
                  <template class="matfield-template">
                      <div class="row mat-field mb-2 g-2 align-items-end">
                          <div class="col-md-3">
                              <label class="small fw-bold">Material</label>
                              <select class="form-control form-control-sm other_material" name="associated_products[__INDEX__][material_id]">
                                  ' . $options . '
                              </select>
                          </div>
                          <div class="col-md-1">
                              <label class="small fw-bold">Count</label>
                              <input type="number" step="any" class="form-control form-control-sm" name="associated_products[__INDEX__][required]">
                          </div>
                          <div class="col-md-1">
                              <label class="small fw-bold">Unit</label>
                              <input class="form-control form-control-sm other_material_unit" name="associated_products[__INDEX__][unit]" readonly>
                          </div>
                          <div class="col-md-1 text-center small fw-bold pb-2">For every</div>
                          <div class="col-md-1">
                              <label class="small fw-bold">Count</label>
                              <input type="number" step="any" class="form-control form-control-sm" name="associated_products[__INDEX__][for]" value="1">
                          </div>
                          <div class="col-md-1">
                              <label class="small fw-bold">Unit</label>
                              <input class="form-control form-control-sm display-unit" value="Unit" readonly>
                          </div>
                          <div class="col-md-3">
                              <label class="small fw-bold">Material</label>
                              <input class="form-control form-control-sm main-material-name" value="Material Name" readonly>
                          </div>
                          <div class="col-md-1 text-end pb-1">
                              <button type="button" class="btn btn-sm btn-outline-danger remove"><i class="fa fa-trash"></i></button>
                          </div>
                      </div>
                  </template>
                  <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" onload="if(window.MaterialsManager) window.MaterialsManager.initAssociated(this.parentElement)">
                </div>';

        return $html;
    }

}
