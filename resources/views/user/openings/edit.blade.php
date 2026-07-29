@extends('user.layouts.app')

@section('title', 'Edit Opening')

@section('content')
    <div class="page-content-tab d-flex flex-column"
    style="background-color:#f8f9fa;height:calc(100vh - 65px);min-height:0;overflow:hidden;padding:15px 12px;">

        <div class="container-fluid d-flex flex-column flex-grow-1 p-0"
            style="max-width:100%;height:100%;min-height:0;">
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-md-6 col-lg-4 order-lg-1 order-md-1 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="pt-3 new-project">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">
                                        Update Opening
                                    </h3>
                                </div>
                                <form method="post" action="{{ route('opening.update', ['id' => $opening->id]) }}">
                                    @csrf()
                                    <div class="row g-3">
                                        <div class="col-lg-8 col-md-10 mx-auto">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Description:</label>
                                                <textarea name="description" rows="3" class="form-control" placeholder="Opening Description" required>{{ $opening->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Project:</label>
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
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Labor Type:</label>
                                                <select name="labor_id" class="form-control" required>
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $labor_types = get_labor_names();
                                                    @endphp

                                                    @foreach ($labor_types as $labor_type)
                                                        <option value="{{ $labor_type->id }}"
                                                            @if ($opening->labor_id == $labor_type->id) selected @endif()>
                                                            {{ $labor_type->labor_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Labor Class:</label>
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
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Opening Shape:</label>
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
                                        <div class="col-lg-12 d-none col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <input type="hidden" class="form-control measurement_unit" name="measurement_unit" value="{{ $opening->measurement_unit }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Length:</label>
                                                <input type="number" step="0.001" value="{{ $opening->length }}" name="length"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">height:</label>
                                                <input type="number" step="0.001" name="height" class="form-control measurement"
                                                    placeholder="" value="{{ $opening->height }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Elevation:</label>
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
                                                <label class="form-label fw-bold text-14">Inside:</label>
                                                <input type="radio" class="form-radio ms-4" name="header"
                                                    placeholder="Inside" value="0"
                                                    @if ($opening->header == 0) checked @endif() required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group d-flex align-items-center">
                                                <label class="form-label fw-bold text-14">Outside:</label>
                                                <input type="radio" class="form-radio ms-4" name="header" value="1"
                                                    @if ($opening->header == 1) checked @endif() required>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12">
                                            <h5 class="text-dark mb-3 fs-6">Lintels and Bearing</h5>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Bearing each End</label>
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
                                                        <div class="form-group input-project">
                                                            <label class="form-label fw-bold text-14">Material</label>
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
                                                        <div class="form-group input-project">
                                                            <label class="form-label fw-bold text-14">Length:</label>
                                                            <input type="number" step="0.001"
                                                                class="form-control mat_length_{{ $i }} measurement"
                                                                data-disable=".mat_quantity_{{ $i }}"
                                                                value="{{ $opening_material->length }}"
                                                                name="materials[{{ $i }}][length]">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="form-group input-project">
                                                            <label class="form-label fw-bold text-14">Quantity:</label>
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
                                                <a href="javascript:void(0);" class="btn back-btn text-black btn-material">Add
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
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Length:</label>
                                                <input type="number" step="0.001" class="form-control caulking_length measurement"
                                                    name="caulking[length]" value="{{ $caulking->length }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Perimeter Around:</label>
                                                <input type="number" step="0.001" class="form-control caulking_perimeter measurement"
                                                    name="caulking[perimeter_around]"
                                                    value="{{ $caulking->perimeter_around }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-md-10 mx-auto">
                                            <hr class="my-4">

                                            <div class="d-flex justify-content-end gap-2">

                                                <a href="javascript:void(0);"
                                                    onclick="history.back()"
                                                    class="btn me3co-secondary-btn">

                                                    Back

                                                </a>

                                                <button class="btn me3co-primary-btn">

                                                    <i class="fa fa-save me-1"></i>

                                                    Save Opening

                                                </button>

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
