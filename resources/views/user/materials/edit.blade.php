@extends('user.layouts.app')

@section('title', 'Materials')

@section('content')

    <div class="page-content-tab" data-select2-id="11">
        <div class="container-fluid" data-select2-id="10">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Materials</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Materials</li>
                                </ol>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end page-title-box-->
                </div>
                <!--end col-->
            </div>
            <!--end row-->
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Edit Material
                                    </h3>
                                </div>
                                <form method="post" action="{{ route('material.update', ['id' => $material->id]) }}">
                                    @csrf()

                                    <div class="row">
                                        <div class="col-lg4 col-md-4 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Type: <span class="text-danger">*</span></label>
                                                <select class="form-control" name="material_type_id"
                                                    id="material_type_id">
                                                    <option value="{{ $material->material_type_id }}" hidden selected>
                                                        {{ $material->material_type->name }}</option>
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
                                                    id="material_division_id">
                                                    <option value="{{ $material->material_division_id }}" hidden selected>
                                                        {{ $material->material_division->name }}</option>
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
                                                <select class="form-control" name="material_class_id"
                                                    id="material_class_id">
                                                    <option value="{{ $material->material_class_id }}" selected hidden>
                                                        {{ $material->material_class->name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Name: <span class="text-danger">*</span></label>
                                                <input class="form-control material_name" value="{{ $material->name }}"
                                                    placeholder="Material 1" name="name" required />
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Description:</label>
                                                <textarea class="form-control" rows="5" placeholder="Description" name="description">{{ $material->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Default Unit Count: <span class="text-danger">*</span></label>
                                                <input class="form-control default_unit" type="text" name="default_unit"
                                                    id="default_unit" value="{{ $material->default_unit }}"
                                                    list="default_unit-count" placeholder="Unit" autocomplete="off"
                                                    required>
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
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label class="text-14">Length ({{ $units->symbol }}):</label>
                                                @php
                                                    $nums = explode('.', $material->length . '.00');
                                                    if (array_key_exists(1, $nums)) {
                                                        $denom = 10 ** strlen($nums[1]);
                                                        $gcd = gmp_gcd($nums[1], $denom);
                                                    } else {
                                                        $nums[1] = 0;
                                                        $gcd = 1;
                                                        $denom = 0;
                                                    }
                                                @endphp
                                                <input type="hidden" id="length" name="length"
                                                    value="{{ $material->length }}">
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <div class="form-control fraction-input" data-target="length"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">{{ $nums[0] }}
                                                        </div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">
                                                                {{ $nums[1] / $gcd }}</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">{{ $denom / $gcd }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label class="text-14">Width ({{ $units->symbol }}):</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    @php
                                                        $nums = explode('.', $material->width . '.00');
                                                        if (array_key_exists(1, $nums)) {
                                                            $denom = 10 ** strlen($nums[1]);
                                                            $gcd = gmp_gcd($nums[1], $denom);
                                                        } else {
                                                            $nums[1] = 0;
                                                            $gcd = 1;
                                                            $denom = 0;
                                                        }
                                                    @endphp
                                                    <input type="hidden" id="width" name="width"
                                                        value="{{ $material->width }}">
                                                    <div class="form-control fraction-input" data-target="width"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">{{ $nums[0] }}
                                                        </div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">
                                                                {{ $nums[1] / $gcd }}</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">
                                                                {{ $denom / $gcd }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label class="text-14">Height ({{ $units->symbol }}):</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="hidden" id="height" name="height"
                                                        value="{{ $material->height }}">
                                                    @php
                                                        $nums = explode('.', $material->height . '.00');
                                                        if (array_key_exists(1, $nums)) {
                                                            $denom = 10 ** strlen($nums[1]);
                                                            $gcd = gmp_gcd($nums[1], $denom);
                                                        } else {
                                                            $nums[1] = 0;
                                                            $gcd = 1;
                                                            $denom = 0;
                                                        }
                                                    @endphp
                                                    <div class="form-control fraction-input" data-target="height"
                                                        tabindex="-1">
                                                        <div class="whole" contenteditable="true">{{ $nums[0] }}
                                                        </div>
                                                        <div class="fraction">
                                                            <div class="sup" contenteditable="true">
                                                                {{ $nums[1] / $gcd }}</div>
                                                            <hr>
                                                            <div class="sub" contenteditable="true">
                                                                {{ $denom / $gcd }}</div>
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
                                                        name="weight_lf" id="weight_lf" value="{{ $material->weight_lf }}">
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="col-6">
                                            <div class="form-group input-project">
                                                <label class="text-14">Sq ft per Cy:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="number" step="0.001"
                                                        class="form-control currency-amount"
                                                        placeholder="sq ft per cy" size="8"
                                                        name="sq_ft_per_cy" id="sq_ft_per_cy" value="{{ $material->sq_ft_per_cy }}">
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Price:</label>
                                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="number" step="0.001"
                                                        class="form-control currency-amount"
                                                        placeholder="$ / {{ $material->default_unit }}" size="8"
                                                        name="prices" id="prices" value="{{ $material->prices }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Waste(%):</label>
                                                <input type="number" step="0.001" class="form-control"
                                                    placeholder="Waste" name="waste" value="{{ $material->waste }}">
                                            </div>
                                        </div>
                                        <div class="col-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label  class="text-14">Production Rate:</label>
                                                <div class="form-control production_rate">
                                                    <input type="number" step="0.001" min="0"
                                                        class="optional_field production_field"
                                                        data-option="production_subbed_out" placeholder="Unit"
                                                        name="production_rate" value="{{ $material->production_rate }}">
                                                    <span id="production_unit">{{ $material->default_unit }}</span>&nbsp;
                                                    Per
                                                    <select>
                                                        <option value="day" selected>Day</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-center p_hidden">OR</h6>
                                        <div class="col-md-6 col-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label class="text-14">Subed Out cost:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control production_subbed_out optional_field"
                                                    data-option="production_field" placeholder="$"
                                                    name="production_subed_out_cost"
                                                    value="{{ $material->production_subed_out_cost }}">
                                                <b>Note- this overrides cost associated with production</b>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Subed Out rate(day):</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control production_subbed_out optional_field"
                                                    data-option="production_field" placeholder=""
                                                    name="subed_out_rate" value="{{ $material->subbed_out_rate }}">
                                            </div>
                                        </div>

                                        <div class="col-12 p_hidden">
                                            <div class="form-group p_hidden input-project">
                                                <label class="text-14">Cleaning Cost Inhouse:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control cleaning_cost optional_field"
                                                    data-option="cleaning_subbed" placeholder="$" name="cleaning_cost"
                                                    value="{{ $material->cleaning_cost }}">
                                            </div>
                                        </div>
                                        <h6 class="text-center p_hidden">OR</h6>
                                        <div class="col-12 p_hidden">
                                            <div class="form-group input-project">
                                                <label class="text-14">Cleaning Subbed out:</label>
                                                <input type="number" step="0.001" min="0"
                                                    class="form-control optional_field cleaning_subbed"
                                                    data-option="cleaning_cost" placeholder="$" name="cleaning_subed_out"
                                                    value="{{ $material->cleaning_subed_out }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <h3 class="fringe_area text-center">Other Material Associated</h3>
                                        </div>
                                        <div class="more-material">
                                            @php
                                                $other_products = json_decode($material->associated_products);
                                            @endphp
                                            @foreach ($other_products as $key => $other_product)
                                                <div class="row mat-field py-3">
                                                    <div
                                                        class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mat_field_container input-project">
                                                        <div class="col-3">
                                                            <label class="text-14">Material</label>
                                                            <select class="form-control other_material"
                                                                name="associated_products[{{ $key }}][material_id]">
                                                                <option value="">Material</option>
                                                                @php
                                                                    $other_materials = get_materials();
                                                                @endphp
                                                                @foreach ($other_materials as $other_material)
                                                                    <option value="{{ $other_material->id }}"
                                                                        data-unit="{{ $other_material->default_unit }}"
                                                                        @if ($other_material->id == $other_product->material_id) selected @endif()>
                                                                        {{ $other_material->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-1">
                                                            <label class="text-14">Count</label>
                                                            <input type="number" class="form-control"
                                                                name="associated_products[{{ $key }}][required]"
                                                                value="{{ $other_product->required }}" placeholder="0">
                                                        </div>
                                                        <div class="col-1">
                                                            <label class="text-14">Unit</label>
                                                            <input class="form-control other_material_unit"
                                                                name="associated_products[{{ $key }}][unit]"
                                                                value="{{ $other_product->unit }}" placeholder="Unit"
                                                                readonly>
                                                        </div>

                                                        <b>For every</b>
                                                        <div class="col-1">
                                                            <label class="text-14">Count</label>
                                                            <input type="number" class="form-control"
                                                                name="associated_products[{{ $key }}][for]"
                                                                value="{{ $other_product->for }}" placeholder="0">
                                                        </div>
                                                        <div class="col-1">
                                                            <label class="text-14">Unit</label>
                                                            <input class="form-control default_unit"
                                                                value="{{ $material->default_unit }}" placeholder="Unit"
                                                                readonly>
                                                        </div>
                                                        <div class="col-2">
                                                            <label class="text-14">Material</label>
                                                            <input class="form-control material_name"
                                                                placeholder="Material Name" value="{{ $material->name }}"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                    @if ($key > 1)
                                                        <div class="col-2 mt-2">
                                                            <button type="button" class="btn btn-sm back-btn text-black remove">
                                                                Remove <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endif()
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="col-md-3 mt-2">
                                            <button type="button" class="btn btn-sm back-btn text-black add-more">Add
                                                More</button>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="#" onclick="history.back()"
                                                    class="btn back-btn text-black">Back</a>
                                                <button class="btn save-btn text-white">Save Changes</button>
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
        let oldValue = ''
        let MaterialName = '{{ $material->name }}';
        let DefaultUnit = '{{ $material->default_unit }}';
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
        let x = {{ $key+1 }};
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

@endsection()
