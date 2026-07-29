@extends('user.layouts.app')

@section('title', 'Create material')

@section('content')

    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!--end row-->
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="my-project">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="me3co-form-title mb-1">My Materials</h3>
                                        <p class="me3co-form-subtitle mb-0">Quick Start Material Templates</p>
                                    </div>

                                    <a href="javascript:void(0);" id="create-new" class="btn me3co-primary-btn">
                                        <i class="fa fa-plus me-1"></i>
                                        Create New
                                    </a>
                                </div>

                                <hr class="my-3">

                                @php
                                    if (request('project_id')) {
                                        session()->put(
                                            'idProject',
                                            request('project_id')
                                        );
                                    }

                                    $master_materials = get_master_materials();
                                @endphp

                                <button type="button" class="btn me3co-outline-btn import_button mb-3">
                                    <i class="fa fa-file-import me-1"></i>
                                    Import Items
                                </button>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check m-0">
                                        <input type="checkbox" class="form-check-input m-0" id="selectAllMaterial">

                                        <label class="form-check-label fw-semibold" for="selectAllMaterial">
                                            Select All
                                        </label>
                                    </div>

                                    <small class="text-muted">
                                        {{ count($master_materials) }} templates
                                    </small>
                                </div>

                                <div class="labor-template-list">
                                    @foreach ($master_materials as $master_material)
                                        <div class="labor-template-item import-item">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="check-box form-check-input material-checkbox m-0">

                                                <div class="labor-icon">
                                                    <i class="fa fa-cube"></i>
                                                </div>

                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $master_material->name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Quick start material template
                                                    </small>
                                                </div>
                                            </div>

                                            <a href="{{ route('material.import', ['id' => $master_material->id]) }}"
                                                class="btn me3co-small-primary">
                                                Use
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="new-project" style="display:none;">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">Create New Material</h3>
                                    <p class="me3co-form-subtitle mb-0">
                                        Fill in the details below to create a new material.
                                    </p>
                                </div>
                                <form method="post" action="{{ route('material.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                                            <div class="form-group input-project">
                                                <label class="text-14">Type: <span class="text-danger">*</span></label>
                                                <select class="form-control" name="material_type_id"
                                                    id="material_type_id">
                                                    <option value="" hidden selected>Choose Type</option>
                                                    @php
                                                        $material_types = get_material_types();
                                                    @endphp
                                                    @foreach ($material_types as $material_type)
                                                        <option value="{{ $material_type->id }}">
                                                            {{ $material_type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Division: <span class="text-danger">*</span></label>
                                                <select class="form-control" name="material_division_id"
                                                    id="material_division_id" required>
                                                    <option value="" hidden selected>Choose Division</option>
                                                    @php
                                                        $material_divisions = get_material_divisions();
                                                    @endphp
                                                    @foreach ($material_divisions as $material_division)
                                                        <option value="{{ $material_division->id }}">
                                                            {{ $material_division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Class: <span class="text-danger">*</span></label>
                                                @php
                                                    $material_classes = get_material_classes();
                                                @endphp
                                                <select class="form-control" name="material_class_id" id="material_class_id"
                                                    required>
                                                    <option value="">Choose Class</option>
                                                    @foreach ($material_classes as $material_class)
                                                    <option value="{{ $material_class->id }}">
                                                        {{ $material_class->name }}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Name: <span class="text-danger">*</span></label>
                                                <input class="form-control material_name" placeholder="Material 1"
                                                    name="name" required />
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Description:</label>
                                                <textarea class="form-control" rows="5" placeholder="Description" name="description"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Default Unit Count: <span class="text-danger">*</span></label>
                                                <input class="form-control default_unit" type="text" name="default_unit"
                                                    id="default_unit" list="default_unit-count" placeholder="Unit"
                                                    autocomplete="off" required>
                                                <data-list id="default_unit-count">
                                                    @php
                                                        $default_units = get_default_units();
                                                    @endphp
                                                    @foreach ($default_units as $default_unit)
                                                        <option>{{ $default_unit->unit }}</option>
                                                    @endforeach()
                                                </data-list>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            @php
                                                $units = get_length_units();
                                            @endphp
                                            <input type="hidden" name="measurement_unit" value="{{ $units->symbol }}">
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Length ({{ $units->symbol }}):</label>
                                                <input type="hidden" id="length" value="0" name="length">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <div class="form-control fraction-input" data-target="length"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">0</div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">0</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">0</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Width ({{ $units->symbol }}):</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="hidden" id="width" value="0" name="width">
                                                    <div class="form-control fraction-input" data-target="width"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">0</div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">0</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">0</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Height ({{ $units->symbol }}):</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="hidden" id="height" name="height" value="0">
                                                    <div class="form-control fraction-input" data-target="height"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">0</div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">0</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">0</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="col-6">
                                            <div class="form-group input-project">
                                                <label class="text-14">Weight Per Lf:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="number" step="0.001"
                                                        class="form-control currency-amount"
                                                        placeholder=" weight per lf" size="8"
                                                        required
                                                        name="weight_lf" id="weight_lf" value="0.000">
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="col-6">
                                            <div class="form-group input-project">
                                                <label class="text-14">Sq ft per Cy:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="number" step="0.001"
                                                        class="form-control currency-amount"
                                                        required
                                                        placeholder="sq ft per cy" size="8"
                                                        name="sq_ft_per_cy" id="sq_ft_per_cy" value="0.000">
                                                </div>
                                            </div>
                                        </div> 

                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Price:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="number" step="0.001"
                                                        class="form-control currency-amount" placeholder="$ / "
                                                        size="8" name="prices" id="prices">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Waste(%):</label>
                                                <input type="number" step="0.001" class="form-control"
                                                    placeholder="Waste" name="waste">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Production Rate:</label>
                                                <div class="form-control production_rate">
                                                    <input type="number" step="0.001" min="0"
                                                        class="optional_field production_field"
                                                        data-option="production_subbed_out" placeholder="Unit"
                                                        name="production_rate">
                                                    <span id="production_unit">Piece</span>&nbsp;
                                                    Per
                                                    <select>
                                                        <option value="day">Day</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-center">OR</h6>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Subed Out cost:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control production_subbed_out optional_field"
                                                    data-option="production_field" placeholder="$"
                                                    name="production_subed_out_cost">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Subed Out rate(day):</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control production_subbed_out optional_field"
                                                    data-option="production_field" placeholder="" name="subed_out_rate">
                                            </div>
                                        </div>

                                        <b>Note- this overrides cost associated with production</b>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Cleaning Cost Inhouse:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control cleaning_cost optional_field"
                                                    data-option="cleaning_subbed" placeholder="$" name="cleaning_cost">
                                            </div>
                                        </div>
                                        <h6 class="text-center">OR</h6>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Cleaning Subbed out:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control optional_field cleaning_subbed"
                                                    data-option="cleaning_cost" placeholder="$"
                                                    name="cleaning_subed_out">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <h3 class="fringe_area text-center">Other Material Associated</h3>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mat_field_container input-project">
                                            <div class="col-3">
                                                <label class="text-14">Material</label>
                                                <select class="form-control other_material"
                                                    name="associated_products[0][material_id]">
                                                    <option value="">Material</option>
                                                    @php
                                                        $other_materials = get_materials();
                                                    @endphp
                                                    @foreach ($other_materials as $other_material)
                                                        <option value="{{ $other_material->id }}"
                                                            data-unit="{{ $other_material->default_unit }}">
                                                            {{ $other_material->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-1">
                                                <label class="text-14">Count</label>
                                                <input type="number" class="form-control"
                                                    name="associated_products[0][required]" placeholder="0">
                                            </div>
                                            <div class="col-1">
                                                <label class="text-14">Unit</label>
                                                <input class="form-control other_material_unit"
                                                    name="associated_products[0][unit]" placeholder="Unit" readonly>
                                            </div>

                                            <b>For every</b>
                                            <div class="col-1">
                                                <label class="text-14">Count</label>
                                                <input type="number" class="form-control"
                                                    name="associated_products[0][for]" placeholder="0">
                                            </div>
                                            <div class="col-1">
                                                <label class="text-14">Unit</label>
                                                <input class="form-control default_unit" placeholder="Unit" readonly>
                                            </div>
                                            <div class="col-2">
                                                <label class="text-14">Material</label>
                                                <input class="form-control material_name" placeholder="Material Name"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="more-material"></div>
                                        <div class="col-md-3 mt-2">
                                            <button type="button" class="btn btn-sm back-btn text-black add-more">Add
                                                More</button>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <!--div class="form-group text-center"-->
                                            <div class="form-group text-center d-flex justify-content-center align-items-center gap-3 flex-wrap">
                                                <a href="javascript:void(0);" id="back" class="btn me3co-secondary-btn">
                                                    Back
                                                </a>

                                                <button class="btn me3co-primary-btn">
                                                    <i class="fa fa-save me-1"></i>
                                                    Save Material
                                                </button>
                                                @if(auth()->user()->role == 1)
                                                <div class="form-check d-flex align-items-center m-0">
                                                    <input 
                                                        class="form-check-input me-2" 
                                                        type="checkbox" 
                                                        id="is_global" 
                                                        name="is_global"
                                                    >
                                                    <label class="form-check-label small mb-0" for="is_global">
                                                        Checked: visible to all users
                                                    </label>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                </div>
                <!--end col-->

            </div>
            <!--end row-->

        </div><!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        function goBack() {
            if (window.history.length > 1) {
            window.history.back();
            return;
            }
            window.location.href = "/projects"; // fallback
        }
        $(document).ready(function() {
            $('.new-project').hide();

            $('#create-new').click(function() {
                $('.new-project').fadeIn(250);
                $('.my-project').hide();
            });

            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').fadeIn(250);
            });

            $('#selectAllMaterial').on('change', function () {
                $('.material-checkbox').prop('checked', this.checked);
            });

            $('.material-checkbox').on('change', function () {
                $('#selectAllMaterial').prop(
                    'checked',
                    $('.material-checkbox').length === $('.material-checkbox:checked').length
                );
            });
        })
    </script>
    <script>
        let oldValue = ''
        let MaterialName = '';
        let DefaultUnit = '';
        const updateDefaultUnit = (unit) => {
            DefaultUnit = unit;
            let elems = document.querySelectorAll('.default_unit');
            elems.forEach((elem) => {
                elem.value = DefaultUnit;
            })
        }
        $('.material_name').on('input', function() {
            MaterialName = $(this).val();
            $('.material_name').val(MaterialName);

        })

        $('.unit-field').on('click', function() {
            let value = $(this).val();
            $(this).attr('data-oldValue', value);
        })
        $('.unit-field').on('change', function() {
            let value = $(this).val();
            let oldValue = $(this).attr('data-oldValue');
            $(`[data-value1="${oldValue}"]`).removeAttr('hidden');
            $(`[data-value1="${value}"]`).attr('hidden', "hidden");
        });
        const materialDivision = document.querySelector('#material_division_id');
        const materialClass = document.querySelector('#material_class_id');
        materialDivision.addEventListener('change', async (e) => {
            let value = e.target.value;
            let url = `{{ route('material_class') }}/${value}`;

            let response = await fetch(url);
            let data = await response.json();

            materialClass.innerHTML = '<option value="" selected hidden>Choose Class</option>';
            data.forEach((material_class) => {
                materialClass.innerHTML +=
                    `<option value="${material_class.id}">${material_class.name}</option>`
            })
        })

        const fractions = document.querySelectorAll('.fraction-input');
        fractions.forEach(frac => {
            let target = frac.dataset.target;
            frac.addEventListener('keydown', (e) => {
                let isNumber = isFinite(e.key);

                if (!isNumber && e.key !== 'Backspace') {
                    e.preventDefault();
                }
            })
            frac.addEventListener('input', (e) => {
                let whole = parseInt(frac.querySelector('.whole').textContent || 0);
                let sup = parseInt(frac.querySelector('.sup').textContent || 0);
                let sub = parseInt(frac.querySelector('.sub').textContent || 0);

                if (sub == 0) {
                    document.querySelector(`#${target}`).value = whole;
                    return false;

                }
                if (sub < sup) {
                    frac.dataset.error = 'Denominator should be non zero and greater than Numerator';
                    document.querySelector(`#${target}`).value = '';
                    return false;
                }

                let value = ((sub * whole) + sup) / sub;
                frac.dataset.error = '';
                document.querySelector(`#${target}`).value = value.toFixed(3);
            })
        })

        document.querySelector('#default_unit').addEventListener('blur', (e) => {
            let unit = e.target.value;
            console.log(unit);
            updateDefaultUnit(unit);
            document.querySelector('#prices').placeholder = `$ / ${unit}`;
            document.querySelector('#production_unit').textContent = unit;
        });
        let x = 1;
        $('.add-more').click(function() {
            $('.more-material').append(`@include('components.user.matfield')`);
            x++;
            document.dispatchEvent(new Event('DOMContentLoaded'))
        });
        $('.more-material').on('click', '.remove', function() {
            $(this).parents('.mat-field').remove();
            x--;
        });

        document.addEventListener('DOMContentLoaded', () => {
            let otherMaterials = document.querySelectorAll('.other_material');
            let otherMaterialUnits = document.querySelectorAll('.other_material_unit');

            otherMaterials.forEach((elem, index) => {
                elem.addEventListener('change', (e) => {
                    let options = e.target.querySelectorAll('option');
                    options.forEach((option) => {
                        if (option.selected) {
                            otherMaterialUnits[index].value = option.dataset.unit || '';
                            return
                        }
                    })
                })
            })
        })
    </script>
    <script>
        let importButton = document.querySelector('.import_button');
        importButton.addEventListener('click', async (e) => {
            let importItems = document.querySelectorAll('.import-item');
            let importCount = document.querySelectorAll('.import-item  input:checked');
            if (importCount.length == 0) {
                alert('Please select an Item to import!')
                return false;
            }
            for await (item of importItems) {
                let checkStatus = item.querySelector('input:checked');
                if (checkStatus) {
                    let url = item.querySelector('a').href;
                    await fetch(url);
                }
            }
            document.body.innerHTML += `
            <div class="toast custom show position-fixed align-items-center text-white bg-primary border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
               &check; Labors Imported
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
            `

            setTimeout(() => {
                window.location.reload()
            }, 2000);
        });
    </script>
@endsection()
