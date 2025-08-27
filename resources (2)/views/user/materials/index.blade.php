@extends('user.layouts.app')

@section('title', 'Materials')

@section('content')

    <div class="page-content-tab" data-select2-id="11">
        <div class="container-fluid" data-select2-id="10">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">My Materials</h4>

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
            </div> --}}
            <div class="row mt-3">
                <div class="col-10">
                    <div class="w-100 d-inline-block">
                        <h2 class="text-black fs-4 fw-bold">My Materials @if ($project != null)
                                for {{ $project->name }}
                            @endif
                        </h2>
                    </div>
                </div>
                <div class="col-2">
                    <div class="float-end d-inline-block">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                            <li class="breadcrumb-item active">Materials</li>
                        </ol>
                    </div>
                    <div class="float-end d-inline-block creat-project-btn">
                        <a href="{{ route('material.add') }}" class="text-14 fw-bold d-inline-block btn-create-project">
                            <img src="{{ asset('projects') }}/images/plus-icon.svg">
                            Create Material
                        </a>
                    </div>
                </div>
            </div>
            <!--end row-->
            <div class="row">
               
                <form method="post" action="{{ route('material.division') }}">
                    @csrf()
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group input-project">
                            <label class="text-14">Division: <span class="text-danger">*</span></label>
                            <select class="form-control" name="material_division_id" id="material_division_id" >
                                <option value="">All Divisions</option>
                                @php
                                    $material_divisions = get_material_divisions();
                                @endphp
                                @foreach ($material_divisions as $material_division)
                                    @php
                                    $selected="";
                                    if($material_division->id == $material_division_id)
                                        $selected="selected";
                                    @endphp
                                    <option value="{{ $material_division->id }}" {{$selected}}>
                                        {{ $material_division->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn save-btn text-white">Filter</button>
                        </div>

                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="w-100 d-inline-block project-table mt-4">
                        <table class="table table-striped" id="projectTableId">
                            <thead class="thead-light">
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
                                        <td class="text-nowrap">
                                            <a type="button" class="delete-button"
                                                href="{{ route('material.delete', ['id' => $material->id]) }}">
                                                <img class="edit-icon"
                                                    src="{{ asset('projects') }}/images/delete-icon.svg">
                                            </a>
                                            <a type="button" class="edit-button"
                                                href="{{ route('material.edit', ['id' => $material->id]) }}">
                                                <img class="edit-icon me-2"
                                                    src="{{ asset('projects') }}/images/edit-icon.svg">
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                "paging": true, // Enable pagination
                "searching": true, // Enable search
                // You can customize further options here
            });
        });
    </script>
    <script>
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
