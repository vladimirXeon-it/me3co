@php
    $return_url = request()->fullUrl();
@endphp

@extends('user.layouts.app')

@section('title', 'Materials')

@section('content')

    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- Page-Title -->
            <div class="project-page-header flex-shrink-0 mb-3">
                <div>
                    <h1>
                        @if ($project)
                            My Materials for {{ $project->name }}
                        @else
                            Materials
                        @endif
                    </h1>
                </div>

                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('material.add', ['return_url' => $return_url]) }}"
                        class="btn-create-project">
                            <i class="fa fa-plus"></i>
                            Create Material
                        </a>
                    </div>
                </div>
            </div>
            <!--end row-->
            <div class="card border-0 shadow-sm mb-3 p-3" style="border-radius:16px;">
                <form method="post" action="{{ route('material.division') }}">
                    @csrf()

                    <div class="row align-items-end g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold mb-1">
                                Division <span class="text-danger">*</span>
                            </label>

                            <select class="form-control" name="material_division_id" id="material_division_id">
                                <option value="">All Divisions</option>

                                @php
                                    $material_divisions = get_material_divisions();
                                @endphp

                                @foreach ($material_divisions as $material_division)
                                    <option value="{{ $material_division->id }}"
                                        {{ $material_division->id == $material_division_id ? 'selected' : '' }}>
                                        {{ $material_division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-auto">
                            <button class="btn fw-bold text-white px-4"
                                style="height:38px;border-radius:8px;background:#0D5EFF;border:0;">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3"
                        style="border-radius:16px;">
                        <div class="me3co-table-wrapper">
                            <table class="table align-middle me3co-table mb-0" id="projectTableId">
                                <thead>
                                    <tr>
                                        <th class="border-top-0">#</th>
                                        <th class="border-top-0">Material</th>
                                        <th class="border-top-0">Type</th>
                                        <th class="border-top-0">Division</th>
                                        <th class="border-top-0">Class</th>
                                        <th class="border-top-0">Material Id</th>
                                        <th class="border-top-0">Created At</th>
                                        <th class="border-top-0">Action</th>
                                    </tr>
                                    <!--end tr-->
                                </thead>
                                <tbody>
                                    @php
                                        $i = 1;
                                    @endphp

                                    @foreach ($materials as $material)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $material->name }}</td>
                                            <td>{{ $material->material_type->name }}</td>
                                            <td>{{ $material->material_division->name }}</td>
                                            <td>{{ $material->material_class->name }}</td>
                                            <td>{{ $material->unique_id }}</td>
                                            <td>{{ $material->created_at->format('d F, Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm border text-muted px-2"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('material.edit', ['id' => $material->id, 'return_url' => $return_url]) }}">
                                                                <i class="fa fa-pencil me-2 text-primary"></i>
                                                                Edit
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item text-danger"
                                                            href="{{ route('material.delete', ['id' => $material->id]) }}"
                                                            onclick="return confirm('Are you sure you want to delete this material?')">
                                                                <i class="fa fa-trash me-2"></i>
                                                                Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

        </div><!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],

                scrollY: 'calc(100vh - 420px)',
                scrollCollapse: false,
                scrollX: false,

                dom:
                    '<"d-flex justify-content-between align-items-center mb-3"l f>' +
                    't' +
                    '<"d-flex justify-content-between align-items-center mt-3"i p>',

                language: {
                    search: "",
                    searchPlaceholder: "Search materials...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });

            $('.dataTables_filter input')
                .addClass('form-control form-control-sm me3co-search');

            $('.dataTables_filter label').contents().filter(function(){
                return this.nodeType === 3;
            }).remove();
        });
    </script>
    <script>
        function goBack() {
            window.location.href = "/project"; // fallback
        }
        let oldValue = ''
        $('#create-new').click(function() {
            $('.new-project').show();
            $('.my-project').hide();
        });
        $('#back').click(function() {
            $('.new-project').hide();
            $('.my-project').show();
        });

        $(document).ready(function() {
            $('.optional_field').on('input', function() {
                let field = $(this).attr('data-option');
                $(`.${field}`).val(0)
            })
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
        if (materialDivision && materialClass) {
            materialDivision.addEventListener('change', async (e) => {
                let value = e.target.value;
                let url = `{{ route('material_class') }}/${value}`;

                let response = await fetch(url);
                let data = await response.json();

                materialClass.innerHTML = '<option value="" selected hidden>Choose Class</option>';
                data.forEach((material_class) => {
                    materialClass.innerHTML +=
                        `<option value="${material_class.id}">${material_class.name}</option>`
                });
            });
        }

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
            console.log(unit)
            document.querySelector('#prices').placeholder = `$ / ${unit}`;
            document.querySelector('#production_unit').textContent = unit;
        });
        let x = 1;
        $('.add-more').click(function() {
            $('.more-material').append(`@include('components.user.matfield')`);
            x++;
        });
        $('.more-material').on('click', '.remove', function() {
            $(this).parents('.mat-field').remove();
            x--;
        });
        $(document).on('change', '.other_material_name', function() {
            let unit = $(this).find(':selected').data('unit');
            console.log($(this).closest('.other_product_unit'))
            $(this).siblings('.other_product_unit').text(unit);
        })
    </script>
@endsection()
