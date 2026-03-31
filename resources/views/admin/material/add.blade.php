{{-- resources/views/admin/material/add.blade.php --}}
@php
    $isModal = $isModal ?? request()->ajax() || request()->query('modal') == 1;

    $isEdit = isset($material) && $material;

    // ✅ Rutas reales (según tus routes)
    $action = $isEdit
        ? route('admin.material.update', $material->id)
        : route('admin.material.store');

    // ✅ Template seguro para “Add More”
    $matFieldTemplate = '';
    try {
        $matFieldTemplate = view('components.user.matfield')->render();
    } catch (\Throwable $e) {
        $matFieldTemplate = '';
    }
@endphp

@if(!$isModal)
    @extends('admin.layouts.app')
    @section('title', 'Materials')
    @section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Material</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Material</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
@endif

<div class="row">
    <div class="col-md-12 mx-auto">
        <div class="card">
            <div class="card-body">
                <div class="pt-3 new-project">
                    <div class="text-center card-title">
                        {{ $isEdit ? 'Edit Material' : 'Create Material' }}
                    </div>

                    <form id="material-form" method="post" action="{{ $action }}">
                        @csrf()

                        <div class="row">

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Division: <span class="text-danger">*</span></label>
                                    <select class="form-control" name="material_division_id" id="material_division_id" required>
                                        <option value="" hidden selected>Choose Division</option>
                                        @php $material_divisions = get_material_divisions(); @endphp
                                        @foreach ($material_divisions as $material_division)
                                            <option value="{{ $material_division->id }}"
                                                @if(old('material_division_id', $material->material_division_id ?? '') == $material_division->id) selected @endif>
                                                {{ $material_division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Class: <span class="text-danger">*</span></label>

                                    <select
                                        class="form-control"
                                        name="material_class_id"
                                        id="material_class_id"
                                        data-selected="{{ old('material_class_id', $material->material_class_id ?? '') }}"
                                        required
                                    >
                                        <option value="">Choose Class</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Name: <span class="text-danger">*</span></label>
                                    <input class="form-control material_name" placeholder="Material 1" name="name"
                                           value="{{ old('name', $material->name ?? '') }}" required />
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Description:</label>
                                    <textarea class="form-control" rows="3" placeholder="Description" name="description">{{ old('description', $material->description ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Default Unit Count: <span class="text-danger">*</span></label>
                                    <input class="form-control default_unit" type="text" name="default_unit"
                                           id="default_unit" list="default_unit-count" placeholder="Unit"
                                           autocomplete="off" value="{{ old('default_unit', $material->default_unit ?? '') }}" required>
                                    <datalist id="default_unit-count">
                                        @php $default_units = get_default_units(); @endphp
                                        @foreach ($default_units as $default_unit)
                                            <option>{{ $default_unit->unit }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                @php $units = get_length_units(); @endphp
                                <input type="hidden" name="measurement_unit" value="{{ $units->symbol }}">
                            </div>

                            {{-- Length --}}
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Length ({{ $units->symbol }}):</label>
                                    <input type="hidden" id="length" value="{{ old('length', $material->length ?? 0) }}" name="length">
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <div class="form-control fraction-input" data-target="length" tabindex="-1">
                                            @php $len = explode('.', (string)($material->length ?? '0.0')); @endphp
                                            <div class="whole" contenteditable="true">{{ $len[0] }}</div>
                                            <div class="fraction">
                                                <div class="sup" contenteditable="true">0</div>
                                                <hr>
                                                <div class="sub" contenteditable="true">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Width --}}
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Width ({{ $units->symbol }}):</label>
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <input type="hidden" id="width" value="{{ old('width', $material->width ?? 0) }}" name="width">
                                        <div class="form-control fraction-input" data-target="width" tabindex="-1">
                                            @php $wid = explode('.', (string)($material->width ?? '0.0')); @endphp
                                            <div class="whole" contenteditable="true">{{ $wid[0] }}</div>
                                            <div class="fraction">
                                                <div class="sup" contenteditable="true">0</div>
                                                <hr>
                                                <div class="sub" contenteditable="true">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Height --}}
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label>Height ({{ $units->symbol }}):</label>
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <input type="hidden" id="height" name="height" value="{{ old('height', $material->height ?? 0) }}">
                                        <div class="form-control fraction-input" data-target="height" tabindex="-1">
                                            @php $hei = explode('.', (string)($material->height ?? '0.0')); @endphp
                                            <div class="whole" contenteditable="true">{{ $hei[0] }}</div>
                                            <div class="fraction">
                                                <div class="sup" contenteditable="true">0</div>
                                                <hr>
                                                <div class="sub" contenteditable="true">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Price --}}
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Price:</label>
                                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                        <input type="number" step="0.001" class="form-control currency-amount"
                                               placeholder="$ / " name="prices" id="prices"
                                               value="{{ old('prices', $material->prices ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Waste --}}
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Waste(%):</label>
                                    <input type="number" step="0.001" class="form-control" placeholder="Waste" name="waste"
                                           value="{{ old('waste', $material->waste ?? '') }}">
                                </div>
                            </div>

                            {{-- Production --}}
                            <div class="col-12 mt-2">
                                <div class="form-group">
                                    <label>Production Rate:</label>
                                    <div class="btn-group w-100" role="group">
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.001" min="0" class="form-control optional_field production_field" data-option="production_subbed_out" placeholder="Unit" name="production_rate" value="{{ old('production_rate', $material->production_rate ?? '') }}">
                                            <span class="input-group-text" id="production_unit">Piece</span>
                                            <span class="input-group-text">Per</span>
                                            <select class="form-select form-select-sm">
                                                <option value="day">Day</option>
                                                <option value="week">Week</option>
                                                <option value="month">Month</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3 text-center"><hr><small>OR</small><hr></div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Subed Out cost:</label>
                                    <input type="number" step="0.001" min="0"
                                           class="form-control production_subbed_out optional_field"
                                           data-option="production_field" placeholder="$"
                                           name="production_subed_out_cost"
                                           value="{{ old('production_subed_out_cost', $material->production_subed_out_cost ?? '') }}">
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <h5 class="text-center mt-4">Associated Materials</h5>
                                <div class="more-material">
                                    @php $other_products = isset($associated_products) ? $associated_products : []; @endphp
                                    @foreach ($other_products as $key => $other_product)
                                        <div class="row mat-field border p-2 mb-2 rounded bg-light">
                                            <div class="col-md-3">
                                                <label class="small">Material</label>
                                                <select class="form-control form-control-sm other_material" name="associated_products[{{$key}}][material_id]">
                                                    <option value="">Choose</option>
                                                    @php $list = get_materials(); @endphp
                                                    @foreach ($list as $m)
                                                        <option value="{{$m->id}}" data-unit="{{$m->default_unit}}" @if($m->id == ($other_product->material_id ?? 0)) selected @endif>{{$m->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="small">Count</label>
                                                <input type="number" class="form-control form-control-sm" name="associated_products[{{$key}}][required]" value="{{$other_product->required ?? 0}}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small">Unit</label>
                                                <input class="form-control form-control-sm other_material_unit" name="associated_products[{{$key}}][unit]" value="{{$other_product->unit ?? ''}}" readonly>
                                            </div>
                                            <div class="col-md-1 text-center small mt-4">For every</div>
                                            <div class="col-md-1">
                                                <label class="small">Count</label>
                                                <input type="number" class="form-control form-control-sm" name="associated_products[{{$key}}][for]" value="{{$other_product->for ?? 1}}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small">Unit</label>
                                                <input class="form-control form-control-sm default_unit" value="{{$material->default_unit ?? ''}}" readonly>
                                            </div>
                                            <div class="col-md-1 mt-4">
                                                <button type="button" class="btn btn-sm btn-danger remove"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary add-more mt-2">
                                    <i class="bi bi-plus-circle"></i> Add More
                                </button>
                            </div>

                            {{-- ✅ Template HTML para Add More --}}
                            <template id="matfield-template">
                                <div class="row mat-field border p-2 mb-2 rounded bg-light">
                                    <div class="col-md-3">
                                        <label class="small">Material</label>
                                        <select class="form-control form-control-sm other_material" name="associated_products[${x}][material_id]">
                                            <option value="">Choose</option>
                                            @php $list = get_materials(); @endphp
                                            @foreach ($list as $m)
                                                <option value="{{$m->id}}" data-unit="{{$m->default_unit}}">{{$m->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="small">Count</label>
                                        <input type="number" class="form-control form-control-sm" name="associated_products[${x}][required]">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small">Unit</label>
                                        <input class="form-control form-control-sm other_material_unit" name="associated_products[${x}][unit]" readonly>
                                    </div>
                                    <div class="col-md-1 text-center small mt-4">For every</div>
                                    <div class="col-md-1">
                                        <label class="small">Count</label>
                                        <input type="number" class="form-control form-control-sm" name="associated_products[${x}][for]" value="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small">Unit</label>
                                        <input class="form-control form-control-sm default_unit" value="${DefaultUnit}" readonly>
                                    </div>
                                    <div class="col-md-1 mt-4">
                                        <button type="button" class="btn btn-sm btn-danger remove"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </template>

                            <div class="col-12 mt-4 text-center">
                                @if($isModal)
                                    {{-- El boton de guardar real lo maneja material.blade.php o este mismo si no es AJAX --}}
                                @else
                                    <a href="#" onclick="history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                @endif
                            </div>

                        </div>
                    </form>

                </div>
            </div>
            <!--end card-body-->
        </div>
    </div>
</div>

@if(!$isModal)
        </section>
    </main>
    @endsection
@endif

{{-- ✅ Scripts: si es modal, imprimir directo; si NO, usar section --}}
@if($isModal)
    <script>
        function closeMaterialModal() {
            try {
                if (window.parent && window.parent.jQuery && window.parent.jQuery.fancybox) {
                    window.parent.jQuery.fancybox.close();
                    return;
                }
            } catch(e) {}
            try { window.close(); } catch(e) {}
        }

        let MaterialName = '';
        let DefaultUnit = '';

        const updateDefaultUnit = (unit) => {
            DefaultUnit = unit;
            document.querySelectorAll('.default_unit').forEach((elem) => {
                elem.value = DefaultUnit;
            });
        };

        // ✅ Sin jQuery (para que no truene en modal)
        document.addEventListener('input', (e) => {
            if (e.target && e.target.classList.contains('material_name')) {
                MaterialName = e.target.value;
                document.querySelectorAll('.material_name').forEach((el) => {
                    el.value = MaterialName;
                });
            }

            if (e.target && e.target.classList.contains('optional_field')) {
                const field = e.target.getAttribute('data-option');
                if (field) {
                    document.querySelectorAll(`.${field}`).forEach((el) => {
                        el.value = 0;
                    });
                }
            }
        });

        const materialDivision = document.querySelector('#material_division_id');
        const materialClass = document.querySelector('#material_class_id');

        const loadClasses = async (divisionId) => {
            if (!divisionId) return;

            let url = `{{ route('material_class') }}/${divisionId}`;
            let response = await fetch(url);
            let data = await response.json();

            const selected = materialClass.getAttribute('data-selected') || '';

            materialClass.innerHTML = '<option value="" selected hidden>Choose Class</option>';

            data.forEach((mc) => {
                const opt = document.createElement('option');
                opt.value = mc.id;
                opt.textContent = mc.name;

                if (selected && String(mc.id) === String(selected)) {
                    opt.selected = true;
                }

                materialClass.appendChild(opt);
            });
        };

        materialDivision.addEventListener('change', (e) => {
            loadClasses(e.target.value);
        });

        // ✅ Auto-carga si ya hay Division seleccionada (edit / old)
        document.addEventListener('DOMContentLoaded', () => {
            if (materialDivision && materialDivision.value) {
                loadClasses(materialDivision.value);
            }
        });

        const fractions = document.querySelectorAll('.fraction-input');
        fractions.forEach(frac => {
            let target = frac.dataset.target;

            frac.addEventListener('keydown', (e) => {
                let isNumber = isFinite(e.key);
                if (!isNumber && e.key !== 'Backspace') {
                    e.preventDefault();
                }
            });

            frac.addEventListener('input', () => {
                let whole = parseInt(frac.querySelector('.whole').textContent || 0);
                let sup   = parseInt(frac.querySelector('.sup').textContent || 0);
                let sub   = parseInt(frac.querySelector('.sub').textContent || 0);

                if (sub == 0) {
                    document.querySelector(`#${target}`).value = whole;
                    return;
                }
                if (sub < sup) {
                    frac.dataset.error = 'Denominator should be non zero and greater than Numerator';
                    document.querySelector(`#${target}`).value = '';
                    return;
                }

                let value = ((sub * whole) + sup) / sub;
                frac.dataset.error = '';
                document.querySelector(`#${target}`).value = value.toFixed(3);
            });
        });

        const defaultUnitEl = document.querySelector('#default_unit');
        if (defaultUnitEl) {
            defaultUnitEl.addEventListener('blur', (e) => {
                let unit = e.target.value;
                updateDefaultUnit(unit);

                const pricesEl = document.querySelector('#prices');
                if (pricesEl) pricesEl.placeholder = `$ / ${unit}`;

                const prodUnit = document.querySelector('#production_unit');
                if (prodUnit) prodUnit.textContent = unit;
            });
        }

        // ✅ Add More (desde <template>)
        const tplEl = document.getElementById('matfield-template');
        const matFieldTemplate = tplEl ? tplEl.innerHTML.trim() : '';

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.add-more');
            if (!btn) return;

            if (!matFieldTemplate) {
                console.warn('matFieldTemplate vacío. Revisa components.user.matfield');
                return;
            }

            const container = document.querySelector('.more-material');
            if (!container) return;

            container.insertAdjacentHTML('beforeend', matFieldTemplate);
        });

        // ✅ Remove (sin jQuery)
        document.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove');
            if (!removeBtn) return;

            const row = removeBtn.closest('.mat-field');
            if (row) row.remove();
        });

        // ✅ Units para Other Material (delegado, sirve también para los que agregues con Add More)
        document.addEventListener('change', (e) => {
            const sel = e.target.closest('.other_material');
            if (!sel) return;

            const opt = sel.selectedOptions?.[0];
            const unit = opt?.dataset?.unit || '';

            const wrap = sel.closest('.mat-field') || sel.closest('.mat_field_container') || document;
            const unitInput = wrap.querySelector('.other_material_unit');

            if (unitInput) unitInput.value = unit;
        });
    </script>
@else
    @section('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

        <script>
            function closeMaterialModal() {
                try {
                    if (window.parent && window.parent.jQuery && window.parent.jQuery.fancybox) {
                        window.parent.jQuery.fancybox.close();
                        return;
                    }
                } catch(e) {}
                try { window.close(); } catch(e) {}
            }

            let MaterialName = '';
            let DefaultUnit = '';

            const updateDefaultUnit = (unit) => {
                DefaultUnit = unit;
                document.querySelectorAll('.default_unit').forEach((elem) => {
                    elem.value = DefaultUnit;
                });
            };

            document.addEventListener('input', (e) => {
                if (e.target && e.target.classList.contains('material_name')) {
                    MaterialName = e.target.value;
                    document.querySelectorAll('.material_name').forEach((el) => {
                        el.value = MaterialName;
                    });
                }

                if (e.target && e.target.classList.contains('optional_field')) {
                    const field = e.target.getAttribute('data-option');
                    if (field) {
                        document.querySelectorAll(`.${field}`).forEach((el) => {
                            el.value = 0;
                        });
                    }
                }
            });

            const materialDivision = document.querySelector('#material_division_id');
            const materialClass = document.querySelector('#material_class_id');

            const loadClasses = async (divisionId) => {
                if (!divisionId) return;

                let url = `{{ route('material_class') }}/${divisionId}`;
                let response = await fetch(url);
                        // Calculate on load
                        updateValue();
                    });
                };
                initFractions();

                // ✅ Divisions & Classes
                const divEl = document.getElementById('material_division_id');
                const classEl = document.getElementById('material_class_id');
                if (divEl && classEl) {
                    const loadClasses = async (divId, selectedId = null) => {
                        if (!divId) return;
                        try {
                            const resp = await fetch(`{{ route('material_class') }}/${divId}`);
                            const data = await resp.json();
                            classEl.innerHTML = '<option value="">Choose Class</option>';
                            data.forEach(c => {
                                const opt = document.createElement('option');
                                opt.value = c.id;
                                opt.textContent = c.name;
                                if (selectedId && c.id == selectedId) opt.selected = true;
                                classEl.appendChild(opt);
                            });
                        } catch (err) { console.error('Error loading classes:', err); }
                    };

                    divEl.addEventListener('change', e => loadClasses(e.target.value));
                    if (divEl.value) loadClasses(divEl.value, classEl.dataset.selected);
                }

                // ✅ Associated Products (Template Substitution)
                const tplEl = document.getElementById('matfield-template');
                const matFieldTemplate = tplEl ? tplEl.innerHTML.trim() : '';

                document.addEventListener('click', e => {
                    const btn = e.target.closest('.add-more');
                    if (btn) {
                        if (!matFieldTemplate) return;
                        const container = document.querySelector('.more-material');
                        // Sustitución manual de los placeholders del template
                        let html = matFieldTemplate
                            .replace(/\${x}/g, x)
                            .replace(/\${DefaultUnit}/g, DefaultUnit);
                        
                        container.insertAdjacentHTML('beforeend', html);
                        x++;
                    }

                    const removeBtn = e.target.closest('.remove');
                    if (removeBtn) {
                        removeBtn.closest('.mat-field').remove();
                    }
                });

                // Sync unit for associated products
                document.addEventListener('change', e => {
                    if (e.target.classList.contains('other_material')) {
                        const row = e.target.closest('.mat-field');
                        const unitInput = row.querySelector('.other_material_unit');
                        const selectedOpt = e.target.options[e.target.selectedIndex];
                        if (unitInput && selectedOpt) {
                            unitInput.value = selectedOpt.dataset.unit || '';
                        }
                    }
                });

                // ✅ Optional fields override
                document.querySelectorAll('.optional_field').forEach(el => {
                    el.addEventListener('input', () => {
                        const otherClass = el.dataset.option;
                        const other = document.querySelector(`.${otherClass}`);
                        if (other) other.value = '';
                    });
                });

                // ✅ AJAX Submit (if in modal)
                @if($isModal)
                const form = document.getElementById('material-form');
                if (form) {
                    form.addEventListener('submit', async e => {
                        e.preventDefault();
                        const formData = new FormData(form);
                        try {
                            const resp = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const result = await resp.json();
                            if (result.status === 'success') {
                                // Si estamos en el modal de material.blade.php
                                if (window.refreshTable) window.refreshTable();
                                if (window.closeMaterialModal) window.closeMaterialModal();
                                else if(typeof bootstrap !== 'undefined') {
                                    // Fallback a buscar el modal
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('formModal'));
                                    if(modal) modal.hide();
                                }
                            } else {
                                alert('Error: ' + (result.message || 'Unknown error'));
                            }
                        } catch (err) {
                            console.error('Submit error:', err);
                            alert('Error submitting form');
                        }
                    });
                }
                @endif
            })();
        </script>
    @endsection
@endif
