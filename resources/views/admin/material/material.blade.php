@extends('admin.layouts.app')

@section('title', 'Materials')

@section('content')
@push('styles')
<style>
    /* Premium Form Layout */
    .gc-form {
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Premium Form Layout - Force Grid/Multi-column */
    
    /* Ensure the container allows inline elements */
    .gc-form, .gc-form-container, .modal-body form {
        display: block !important;
        width: 100% !important;
    }

    /* Target all field containers */
    .gc-field-container, div[data-field-name] {
        display: block !important;
        width: 100% !important;
        padding: 5px 15px !important;
        margin-bottom: 10px !important;
        float: none !important;
        clear: both !important;
    }

    /* Multi-column overrides */
    div[data-field-name="material_type_id"],
    div[data-field-name="material_division_id"],
    div[data-field-name="material_class_id"],
    div[data-field-name="length"],
    div[data-field-name="width"],
    div[data-field-name="height"] {
        width: 33.33% !important;
        display: inline-block !important;
        float: left !important;
        clear: none !important;
    }

    div[data-field-name="weight_lf"],
    div[data-field-name="sq_ft_per_cy"],
    div[data-field-name="production_subed_out_cost"],
    div[data-field-name="subbed_out_rate"] {
        width: 50% !important;
        display: inline-block !important;
        float: left !important;
        clear: none !important;
    }

    /* Force internal labels and inputs to take full width of their new column */
    div[data-field-name="material_type_id"] .col-sm-3,
    div[data-field-name="material_type_id"] .col-sm-9,
    div[data-field-name="material_division_id"] .col-sm-3,
    div[data-field-name="material_division_id"] .col-sm-9,
    div[data-field-name="material_class_id"] .col-sm-3,
    div[data-field-name="material_class_id"] .col-sm-9,
    div[data-field-name="length"] .col-sm-3,
    div[data-field-name="length"] .col-sm-9,
    div[data-field-name="width"] .col-sm-3,
    div[data-field-name="width"] .col-sm-9,
    div[data-field-name="height"] .col-sm-3,
    div[data-field-name="height"] .col-sm-9,
    div[data-field-name="weight_lf"] .col-sm-3,
    div[data-field-name="weight_lf"] .col-sm-9,
    div[data-field-name="sq_ft_per_cy"] .col-sm-3,
    div[data-field-name="sq_ft_per_cy"] .col-sm-9,
    div[data-field-name="production_subed_out_cost"] .col-sm-3,
    div[data-field-name="production_subed_out_cost"] .col-sm-9,
    div[data-field-name="subbed_out_rate"] .col-sm-3,
    div[data-field-name="subbed_out_rate"] .col-sm-9 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        text-align: left !important;
        padding-left: 0 !important;
    }

    /* Ensure clarity on next full blocks */
    div[data-field-name="name"],
    div[data-field-name="description"],
    div[data-field-name="default_unit"],
    div[data-field-name="prices"],
    div[data-field-name="waste"],
    div[data-field-name="production_rate"],
    div[data-field-name="cleaning_cost"],
    div[data-field-name="cleaning_subed_out"],
    div[data-field-name="associated_products"] {
        clear: both !important;
    }

    /* Styling Input Groups */
    .input-group-text {
        background-color: #f0f1f3 !important;
        border: 1px solid #ddd !important;
        font-weight: 500;
        color: #444;
    }

    /* OR Separators styling */
    .or-separator {
        text-align: center;
        font-size: 0.85em;
        font-weight: 800;
        color: #777;
        padding: 12px 0;
        margin: 5px 0;
        text-transform: uppercase;
        width: 100%;
        clear: both;
        display: block;
    }
    
    /* Fraction UI Refinement */
    .fraction-input {
        background-color: #fdfdfd !important;
        height: 110px !important;
        border: 1px solid #ddd !important;
        transition: all 0.2s;
    }
    .fraction-input:focus-within {
        border-color: #0d6efd !important;
        background-color: #fff !important;
    }

    /* Styling Input Groups */
    .input-group-text {
        background-color: #eee !important;
        border: 1px solid #ddd !important;
        font-weight: 500;
        color: #666;
    }

    /* OR Separators styling */
    .or-separator {
        text-align: center;
        font-size: 0.9em;
        font-weight: bold;
        color: #888;
        padding: 10px 0;
        margin: 5px 0;
        text-transform: uppercase;
        width: 100%;
        clear: both;
    }
    
    /* Fraction UI Refinement */
    .fraction-input {
        background-color: #fcfcfc !important;
        height: 120px !important;
        border: 1px solid #ccc !important;
    }
    .fraction-input:focus-within {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Fraction Input Styles */
    .fraction-input {
        display: flex;
        align-items: center;
        background: #fdfdfd;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px 10px;
        width: 100%;
        min-height: 100px; /* Bigger vertical space for fraction like screenshot */
        justify-content: center;
    }
    .fraction-input .whole {
        font-size: 2em;
        font-weight: 500;
        margin-right: 15px;
        outline: none;
        min-width: 30px;
        text-align: center;
    }
    .fraction-input .fraction {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 1.2em;
    }
    .fraction-input .fraction hr {
        width: 40px;
        margin: 3px 0;
        border-top: 2px solid #333;
    }
    .fraction-input .fraction .sup,
    .fraction-input .fraction .sub {
        outline: none;
        min-width: 20px;
        text-align: center;
    }

    /* Associated Products Manager Styles */
    .associated-products-manager {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .associated-products-manager .mat-field {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
    }

    /* Labels styling to match screenshot color */
    .gc-form label {
        color: #444;
        font-weight: 600 !important;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: 6px !important;
        border: 1px solid #ddd !important;
        background-color: #f9f9f9 !important;
    }
</style>
@endpush

<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <div>
            <h1>Materials</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item active">Materials</li>
                </ol>
            </nav>
        </div>

        {{-- Use GroceryCRUD Button if possible, or trigger it via JS --}}
        <button class="btn btn-primary" onclick="triggerGCAdd()">
            <i class="fa fa-plus"></i> Add Material
        </button>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card p-3">
                    {!! $output ?? '' !!}
                </div>
            </div>
        </div>
    </section>

</main>

{{-- Grocery CRUD JS --}}
@if(!empty($js_files))
    @foreach ($js_files as $file)
        <script src="{{ $file }}"></script>
    @endforeach
@endif

<script>
    // ✅ Materials Manager for GC Integration
    window.MaterialsManager = {
        reorganizeLayout: function(container) {
            const form = container.closest('form') || document.querySelector('.gc-form');
            if (!form) return;

            console.log("Reorganizing Layout...");

            const groups = [
                { fields: ['material_type_id', 'material_division_id', 'material_class_id'], name: 'header-group' },
                { fields: ['length', 'width', 'height'], name: 'dim-group' },
                { fields: ['weight_lf', 'sq_ft_per_cy'], name: 'weight-group' },
                { fields: ['production_subed_out_cost', 'subbed_out_rate'], name: 'subed-group' }
            ];

            groups.forEach(group => {
                const firstField = form.querySelector(`div[data-field-name="${group.fields[0]}"]`);
                if (!firstField || firstField.parentElement.classList.contains('custom-row')) return;

                const row = document.createElement('div');
                row.className = 'row custom-row ' + group.name;
                row.style.display = 'flex';
                row.style.flexWrap = 'wrap';
                row.style.margin = '0 -10px';
                
                firstField.parentNode.insertBefore(row, firstField);
                
                group.fields.forEach(f => {
                    const el = form.querySelector(`div[data-field-name="${f}"]`);
                    if (el) {
                        el.style.width = (100 / group.fields.length) + '%';
                        el.style.flex = '0 0 ' + (100 / group.fields.length) + '%';
                        el.style.display = 'block';
                        el.style.padding = '0 10px';
                        
                        // Force label on top
                        const labelCont = el.querySelector('.col-sm-3, .gc-label-container');
                        const inputCont = el.querySelector('.col-sm-9, .gc-input-container');
                        if (labelCont) {
                            labelCont.className = 'col-12 gc-label-container';
                            labelCont.style.width = '100%';
                            labelCont.style.textAlign = 'left';
                        }
                        if (inputCont) {
                            inputCont.className = 'col-12 gc-input-container';
                            inputCont.style.width = '100%';
                        }
                        
                        row.appendChild(el);
                    }
                });
            });

            // Ensure full width for other fields and top label
            form.querySelectorAll('div[data-field-name]').forEach(el => {
                if (!el.parentElement.classList.contains('custom-row')) {
                    const labelCont = el.querySelector('.col-sm-3, .gc-label-container');
                    const inputCont = el.querySelector('.col-sm-9, .gc-input-container');
                    if (labelCont) {
                        labelCont.className = 'col-12 gc-label-container';
                        labelCont.style.width = '100%';
                        labelCont.style.textAlign = 'left';
                    }
                    if (inputCont) {
                        inputCont.className = 'col-12 gc-input-container';
                        inputCont.style.width = '100%';
                    }
                }
            });
        },

        initForm: function(container) {
            console.log("Initializing General Form Logic");
            this.reorganizeLayout(container);
            const form = container.closest('form');
            if (!form) return;

            // 1. "OR" Logic for optional fields
            form.querySelectorAll('.optional_field').forEach(el => {
                el.addEventListener('input', () => {
                    if (el.value.trim() !== '') {
                        // Clear related fields logic
                        let related = [];
                        if (el.name.startsWith('production')) {
                            related = ['production_rate', 'production_subed_out_cost'].filter(n => n !== el.name);
                        } else if (el.name.startsWith('cleaning')) {
                            related = ['cleaning_cost', 'cleaning_subed_out'].filter(n => n !== el.name);
                        }
                        related.forEach(name => {
                            const other = form.querySelector(`[name="${name}"]`);
                            if (other) other.value = '';
                        });
                    }
                });
            });

            // 2. Unit Syncing (default_unit)
            const defUnit = form.querySelector('[name="default_unit"]');
            if (defUnit) {
                defUnit.addEventListener('change', () => {
                    const unit = defUnit.value;
                    // Update any display units
                    form.querySelectorAll('.display-unit').forEach(el => {
                        if (el.tagName === 'INPUT') el.value = unit;
                        else el.textContent = unit;
                    });
                    // Update suffixes
                    form.querySelectorAll('.display-unit-suffix').forEach(el => el.textContent = `$ / ${unit}`);
                });
            }

            // 2b. Name Syncing for Associated Products
            const nameInput = form.querySelector('[name="name"]');
            if (nameInput) {
                nameInput.addEventListener('input', () => {
                    form.querySelectorAll('.main-material-name').forEach(el => el.value = nameInput.value);
                });
            }

                const classRoute = '{{ route("material_class", ["id" => ""]) }}/';

                $divSelect.on('change', async function(e, isInitial) {
                    const divisionId = $(this).val();
                    if (!divisionId) return;

                    const currentClassId = $classSelect.val();

                    try {
                        const res = await fetch(classRoute + divisionId);
                        const data = await res.json();

                        // Empty and update Class select
                        $classSelect.empty().append('<option value="">Choose Class</option>');
                        data.forEach(item => {
                            const selected = (item.id == currentClassId) ? 'selected' : '';
                            $classSelect.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                        });
                        
                        // Notify Select2 to refresh
                        $classSelect.trigger('change');
                    } catch (err) {
                        console.error("Error fetching classes:", err);
                    }
                });

                // Trigger initial load if editing
                if ($divSelect.val()) {
                    $divSelect.trigger('change', [true]);
                }
            }
        },

        initDimensions: function(container) {
            console.log("Initializing Dimensions in:", container);
            const frac = container.querySelector('.fraction-input');
            if (!frac) return;
            const targetId = frac.dataset.target;
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;

            const updateValue = () => {
                let whole = parseInt(frac.querySelector('.whole').textContent || 0);
                let sup = parseInt(frac.querySelector('.sup').textContent || 0);
                let sub = parseInt(frac.querySelector('.sub').textContent || 0);
                if (sub === 0) {
                    targetInput.value = whole;
                } else {
                    targetInput.value = (whole + (sup / sub)).toFixed(3);
                }
                // Trigger change for GC validation if needed
                targetInput.dispatchEvent(new Event('change', { bubbles: true }));
            };

            frac.addEventListener('input', updateValue);
            frac.addEventListener('keydown', e => {
                if (!/[\d]|Backspace|Tab|Arrow|Delete|Enter/.test(e.key)) e.preventDefault();
            });
        },

        initAssociated: function(container) {
            console.log("Initializing Associated Materials in:", container);
            const addBtn = container.querySelector('.add-more');
            const list = container.querySelector('.more-material');
            const tpl = container.querySelector('.matfield-template');
            
            if (!addBtn || !list || !tpl) return;

            let x = list.querySelectorAll('.mat-field').length;

            addBtn.addEventListener('click', () => {
                let html = tpl.innerHTML.replace(/__INDEX__/g, x);
                list.insertAdjacentHTML('beforeend', html);
                const newRow = list.lastElementChild;
                
                // Populating main values from the main form
                const mainName = form.querySelector('[name="name"]')?.value || 'Material Name';
                const mainUnit = form.querySelector('[name="default_unit"]')?.value || 'Unit';
                
                newRow.querySelectorAll('.main-material-name').forEach(el => el.value = mainName);
                newRow.querySelectorAll('.display-unit').forEach(el => {
                    if (el.tagName === 'INPUT') el.value = mainUnit;
                    else el.textContent = mainUnit;
                });

                // Initialize Select2 on the new select if jQuery/Select2 exists
                if (window.jQuery && jQuery.fn.select2) {
                    $(newRow).find('.other_material').select2({
                        dropdownParent: $(container).closest('.modal') || $('body')
                    });
                }

                x++;
            });

            this.reorganizeLayout(container);

            container.addEventListener('click', e => {
                const removeBtn = e.target.closest('.remove');
                if (removeBtn) {
                    removeBtn.closest('.mat-field').remove();
                }
            });

            container.addEventListener('change', e => {
                if (e.target.classList.contains('other_material')) {
                    const row = e.target.closest('.mat-field');
                    const unitInput = row.querySelector('.other_material_unit');
                    const selectedOpt = e.target.options[e.target.selectedIndex];
                    if (unitInput && selectedOpt) {
                        unitInput.value = selectedOpt.dataset.unit || '';
                    }
                }
            });
        }
    };

    function triggerGCAdd() {
        // Attempt to find GC native add button and click it
        const gcAddBtn = document.querySelector('.gc-export + .btn-primary, .gc-add-button, .add-button');
        if (gcAddBtn) {
            gcAddBtn.click();
        } else {
            // Fallback: check if we can find any button with "Add" text
            const buttons = document.querySelectorAll('button, a.btn');
            for (let b of buttons) {
                if (b.textContent.includes('Add Material') && b !== event.currentTarget) {
                    b.click();
                    return;
                }
            }
            console.warn("Could not find GC native add button");
        }
    }

    // Polling fallback to ensure layout is applied even if onload fails
    setInterval(() => {
        const form = document.querySelector('.gc-form');
        if (form && !form.querySelector('.custom-row')) {
            window.MaterialsManager.reorganizeLayout(form);
        }
    }, 1000);
</script>

@endsection
