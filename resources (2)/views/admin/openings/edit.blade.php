@extends('admin.layouts.app')

@section('title', 'Openings')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Openings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Openings</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project">
                                <div class="text-center card-title">
                                    Edit Opening
                                </div>
                                <form method="post" action="{{ route('admin.opening.update', ['id' => $opening->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Description:</label>
                                                <textarea name="description" rows="3" class="form-control" placeholder="Opening Description" required>{{ $opening->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Project:</label>
                                                <select name="project_id" class="form-control" required>
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $projects = get_projects();
                                                    @endphp

                                                    @foreach ($projects as $project)
                                                        <option value="{{ $project->id }}"
                                                            @if ($opening->project_id == $project->id) selected @endif()>
                                                            {{ $project->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Labor Type:</label>
                                                <select name="labor_type_id" class="form-control" required>
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $labor_types = get_labor_type();
                                                    @endphp

                                                    @foreach ($labor_types as $labor_type)
                                                        <option value="{{ $labor_type->id }}"
                                                            @if ($opening->labor_type_id == $labor_type->id) selected @endif()>
                                                            {{ $labor_type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Labor Class:</label>
                                                <select name="labor_class_id" class="form-control" required>
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $labor_classes = get_labor_class();
                                                    @endphp

                                                    @foreach ($labor_classes as $labor_class)
                                                        <option value="{{ $labor_class->id }}"
                                                            @if ($opening->labor_class_id == $labor_class->id) selected @endif()>
                                                            {{ $labor_class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Opening Shape:</label>
                                                <select name="opening_shape_id" class="form-control" required>
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $opening_shapes = get_opening_shapes();
                                                    @endphp

                                                    @foreach ($opening_shapes as $opening_shape)
                                                        <option value="{{ $opening_shape->id }}"
                                                            @if ($opening->opening_shape_id == $opening_shape->id) selected @endif()>
                                                            {{ $opening_shape->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Choose Unit:</label>
                                                <select class="form-control measurement_unit" name="measurement_unit"
                                                    required>
                                                    <option value="{{ $opening->measurement_unit }}" hidden>
                                                        {{ $opening->measurement_unit }}</option>
                                                    <option value="Feet">Feet</option>
                                                    <option value="Yard">Yard</option>
                                                    <option value="CM">CM</option>
                                                    <option value="M">M</option>
                                                    <option value="MM">MM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Length:</label>
                                                <input type="number" step="0.001" value="{{ $opening->length }}" name="length"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>height:</label>
                                                <input type="number" step="0.001" name="height" class="form-control measurement"
                                                    placeholder="" value="{{ $opening->height }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Elevation:</label>
                                                <input type="number" step="0.001" value="{{ $opening->elevation }}" name="elevation"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12">
                                            <h5 class="text-dark mb-3 fs-6">Header</h5>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group d-flex align-items-center">
                                                <label>Inside:</label>
                                                <input type="radio" class="form-radio ms-4" name="header"
                                                    placeholder="Inside" value="0"
                                                    @if ($opening->header == 0) checked @endif() required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group d-flex align-items-center">
                                                <label>Outside:</label>
                                                <input type="radio" class="form-radio ms-4" name="header" value="1"
                                                    @if ($opening->header == 1) checked @endif() required>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12">
                                            <h5 class="text-dark mb-3 fs-6">Lintels and Bearing</h5>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Bearing each End</label>
                                                <input type="number" step="0.001" class="form-control measurement" placeholder=""
                                                    name="bearing" value="{{ $opening->bearing }}" required>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12">
                                            <h5 class="text-dark mb-3 fs-6">Associated Materials</h5>
                                        </div>
                                        <div class="more-material">
                                            @php
                                                $opening_materials = json_decode($opening->materials);
                                                $i = 0;
                                            @endphp
                                            @foreach ($opening_materials as $opening_material)
                                                <div class="row my-3">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Material</label>
                                                            <select class="form-control"
                                                                name="materials[{{ $i }}][name]"
                                                                id="">
                                                                <option value="" hidden>Select</option>
                                                                @php
                                                                    $materials = get_materials();
                                                                @endphp
                                                                @foreach ($materials as $material)
                                                                    <option value="{{ $material->name }}"
                                                                        @if ($material->name == $opening_material->name) selected @endif()>
                                                                        {{ $material->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="form-group">
                                                            <label>Length:</label>
                                                            <input type="number" step="0.001"
                                                                class="form-control mat_length_{{ $i }} measurement"
                                                                data-disable=".mat_quantity_{{ $i }}"
                                                                value="{{ $opening_material->length }}"
                                                                name="materials[{{ $i }}][length]">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="form-group">
                                                            <label>Quantity:</label>
                                                            <input type="number" step="0.001"
                                                                class="form-control mat_quantity_{{ $i }}"
                                                                data-disable=".mat_length_{{ $i }}"
                                                                value="{{ $opening_material->quantity }}"
                                                                name="materials[{{ $i++ }}][quantity]" readonly>
                                                        </div>
                                                    </div>
                                                    @if ($i > 1)
                                                        <button type="button"
                                                            class="col-md-3 col-4 mx-2 btn btn-sm btn-danger remove">
                                                            <i class="fa fa-trash"></i> Remove
                                                        </button>
                                                    @endif()
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <a href="javascript:void(0);" class="btn btn-warning btn-material">Add
                                                    More</a>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12">
                                            <h5 class="text-dark mb-3 fs-6">Caulking</h5>
                                        </div>
                                        @php
                                            $caulking = json_decode($opening->caulking);
                                        @endphp
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Length:</label>
                                                <input type="number" step="0.001" class="form-control caulking_length measurement"
                                                    name="caulking[length]" value="{{ $caulking->length }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Perimeter Around:</label>
                                                <input type="number" step="0.001" class="form-control caulking_perimeter measurement"
                                                    name="caulking[perimeter_around]"
                                                    value="{{ $caulking->perimeter_around }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn btn-warning"
                                                    onclick="history.back()">Back</a>
                                                <button class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                </div>
            </div>
        </section>

    </main>


@endsection()

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#create-new').click(function() {
                $('.new-project').show();
                $('.my-project').hide();
            });
            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').show();
            });
            $('.measurement_unit').on('change', function() {
                let unit = $(this).val();
                $('.measurement').attr('placeholder', unit)
            });
            $('.caulking_length').on('click', function() {
                $('.caulking_perimeter').attr('readonly', 'readonly');
                $('.caulking_perimeter').val('');
                $(this).removeAttr('readonly');
            })
            $('.caulking_perimeter').on('click', function() {
                $('.caulking_length').attr('readonly', 'readonly');
                $('.caulking_length').val('');
                $(this).removeAttr('readonly');
            });
            $('body').on('click', '[data-disable]', function() {
                let target = $(this).attr('data-disable');
                $(this).removeAttr('readonly');
                $(target).attr('readonly', 'readonly');
                $(target).val('');
            })


        })
        let x = {{ $i }}
        $('.btn-material').on('click', function() {
            $('.more-material').append(`@include('components.user.materialfield')`)
            x++;
        })
        $('.more-material').on('click', '.remove', function() {
            $(this).parent('.row').remove();
            x--;
        })
    </script>
@endsection()
