@extends('user.layouts.app')

@section('title', 'Create Opening')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            
                            <div class="pt-3 new-project">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">
                                        Create New Opening
                                    </h3>

                                    <p class="me3co-form-subtitle mb-0">
                                        Configure dimensions, materials and labor for this opening.
                                    </p>
                                    <hr class="my-4">
                                </div>
                                <form method="post" action="{{ route('opening.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="me3co-section-title">
                                            <span>1</span>
                                            <i class="fa fa-file-alt"></i>
                                            Basic Information
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Description:</label>
                                                <textarea name="description" rows="3" class="form-control" placeholder="Opening Description" required></textarea>
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
                                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
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
                                                        <option value="{{ $labor_class->id }}">{{ $labor_class->name }}
                                                        </option>
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
                                                        <option value="{{ $labor_type->id }}">
                                                            {{ $labor_type->labor_type }}
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
                                                        <option value="{{ $opening_shape->id }}">
                                                            {{ $opening_shape->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 d-none col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Choose Unit:</label>
                                                @php
                                                    $units = get_length_units();
                                                @endphp
                                                <input type="hidden" value="{{ $units->symbol }}" name="measurement_unit"/>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-4">
                                            <div class="me3co-section-title">
                                                <span>2</span>
                                                <i class="fa fa-ruler"></i>
                                                Dimensions
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Length:</label>
                                                <input type="number" step="0.001" name="length"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">height:</label>
                                                <input type="number" step="0.001" name="height"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Elevation:</label>
                                                <input type="number" step="0.001" name="elevation"
                                                    class="form-control measurement" placeholder="" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-4">

                                            <div class="me3co-section-title">
                                                <span>3</span>
                                                <i class="fa fa-door-open"></i>
                                                Header Configuration
                                            </div>

                                            <label class="form-label fw-bold text-14 mt-2">Header Position</label>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="me3co-radio-card">
                                                        <input type="radio" name="header" value="0" checked required>
                                                        <span>Inside</span>
                                                    </label>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="me3co-radio-card">
                                                        <input type="radio" name="header" value="1" required>
                                                        <span>Outside</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-4">

                                            <div class="me3co-section-title">
                                                <span>4</span>
                                                <i class="fa fa-grip-lines"></i>
                                                Lintels & Bearing
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Bearing each End</label>
                                                <input type="number" step="0.001" class="form-control measurement"
                                                    placeholder="" name="bearing" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-4">

                                            <div class="me3co-section-title">
                                                <span>5</span>
                                                <i class="fa fa-cube"></i>
                                                Associated Materials
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Material</label>
                                                <select class="form-control" name="materials[0][name]" id="">
                                                    <option value="" hidden>Select</option>
                                                    @php
                                                        $materials = get_materials();
                                                    @endphp
                                                    @foreach ($materials as $material)
                                                        <option value="{{ $material->name }}">{{ $material->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Length ({{ $units->symbol }}):</label>
                                                <input type="number" step="0.001"
                                                    class="form-control mat_length_0 measurement"
                                                    data-disable=".mat_quantity_0" name="materials[0][length]">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Quantity:</label>
                                                <input type="number" step="0.001" class="form-control mat_quantity_0"
                                                    data-disable=".mat_length_0" name="materials[0][quantity]" readonly>
                                            </div>
                                        </div>
                                        <div class="more-material"></div>
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <a href="javascript:void(0);" class="btn me3co-outline-btn btn-material">
                                                    <i class="fa fa-plus me-1"></i>
                                                    Add Material
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="my-4">

                                            <div class="me3co-section-title">
                                                <span>6</span>
                                                <i class="fa fa-pencil-alt"></i>
                                                Caulking
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Length({{ $units->symbol }}):</label>
                                                <input type="number" step="0.001"
                                                    class="form-control caulking_length measurement"
                                                    name="caulking[length]">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Perimeter Around ({{ $units->symbol }}):</label>
                                                <input type="number" step="0.001"
                                                    class="form-control caulking_perimeter measurement"
                                                    name="caulking[perimeter_around]" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="javascript:void(0);" onclick="goBack()" class="btn me3co-secondary-btn">
                                                    Back
                                                </a>

                                                <button class="btn me3co-primary-btn">
                                                    <i class="fa fa-save me-1"></i>
                                                    Create Opening
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
        function goBack() {
            if (window.history.length > 1) {
            window.history.back();
            return;
            }
            window.location.href = "/projects"; // fallback
        }
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
        let x = 1
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
