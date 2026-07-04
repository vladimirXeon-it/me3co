@extends('user.layouts.app')

@section('title', 'Crews')

@section('content')

    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            @if ($project != null)
                <div class="alert border-0 shadow-sm mb-3"
                    style="background:#eef5ff;border-left:4px solid #0d6efd!important;border-radius:14px;">
                    <div class="d-flex align-items-center">
                        <div class="labor-icon me-3">
                            <i class="fa fa-folder"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Creating crew for project</div>
                            <div class="text-muted">{{ $project->name }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">

                            <div class="my-project">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="me3co-form-title mb-1">My Crews</h3>
                                        <p class="me3co-form-subtitle mb-0">Quick Start Crew Templates</p>
                                    </div>

                                    <a href="javascript:void(0);" id="create-new" class="btn me3co-primary-btn">
                                        <i class="fa fa-plus me-1"></i>
                                        Create New
                                    </a>
                                </div>

                                <hr class="my-3">

                                @php
                                    $master_labors = get_master_crews();
                                @endphp

                                <button type="button" class="btn me3co-outline-btn import_button mb-3">
                                    <i class="fa fa-file-import me-1"></i>
                                    Import Items
                                </button>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check m-0">
                                        <input type="checkbox" class="form-check-input m-0" id="selectAllCrew">
                                        <label class="form-check-label fw-semibold" for="selectAllCrew">
                                            Select All
                                        </label>
                                    </div>

                                    <small class="text-muted">
                                        {{ count($master_labors) }} templates
                                    </small>
                                </div>

                                <div class="labor-template-list">
                                    @foreach ($master_labors as $master_labor)
                                        <div class="labor-template-item import-item">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="check-box form-check-input crew-checkbox m-0">

                                                <div class="labor-icon">
                                                    <i class="fa fa-users"></i>
                                                </div>

                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $master_labor->name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Quick start crew template
                                                    </small>
                                                </div>
                                            </div>

                                            <a href="{{ route('crew.import', ['id' => $master_labor->id]) }}"
                                                class="btn me3co-small-primary">
                                                Use
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="new-project" style="display:none;">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">Create New Crew</h3>
                                    <p class="me3co-form-subtitle mb-0">
                                        Fill in the details below to create a new crew.
                                    </p>
                                </div>

                                <form method="post" action="{{ route('crew.create') }}">
                                    @csrf()
                                    <input type="hidden" name="return_url" value="{{ request('return_url') }}">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Crew Name</label>
                                                <input type="text" name="name" class="form-control" placeholder="Crew Name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Description</label>
                                                <input type="text" class="form-control" name="description" placeholder="Description" required>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h6 class="fw-bold text-primary mb-3">Labor Information</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Labor Type</label>
                                                <select class="form-control" name="labor_info[0][labor_type_id]" required>
                                                    <option value="">Select</option>
                                                    @php
                                                        $labor_types = get_user_labors();
                                                    @endphp
                                                    @foreach ($labor_types as $labor_type)
                                                        <option value="{{ $labor_type->id }}">
                                                            {{ $labor_type->labor_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Quantity</label>
                                                <input type="text" class="form-control" name="labor_info[0][quantity]"
                                                    placeholder="How many of this labor type" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Regular Hrs / Day</label>
                                                <input type="text" class="form-control" name="labor_info[0][hours_per_day]"
                                                    placeholder="Regular hours" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Overtime / Day</label>
                                                <input type="text" class="form-control" name="labor_info[0][overtime_per_day]"
                                                    placeholder="Overtime hours">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Double Time / Day</label>
                                                <input type="text" class="form-control" name="labor_info[0][doubletime_per_day]"
                                                    placeholder="Double time">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="more-labor mt-3"></div>

                                    <div class="mt-3">
                                        <a href="javascript:void(0);" class="btn me3co-outline-btn btn-labor">
                                            <i class="fa fa-plus me-1"></i>
                                            Add More
                                        </a>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0);" class="btn me3co-secondary-btn" id="back">
                                            Back
                                        </a>

                                        <button class="btn me3co-primary-btn">
                                            <i class="fa fa-save me-1"></i>
                                            Save Crew
                                        </button>
                                    </div>
                                </form>
                            </div>

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
        function goBack() {
            if (window.history.length > 1) {
            window.history.back();
            return;
            }
            window.location.href = "/projects"; // fallback
        }
        $(document).ready(function(x) {
            //window.fs_test = $('.test').fSelect();
        })
    </script>
    <script>
        $(document).ready(function() {
            var max_fields = 50; //maximum input boxes allowed
            var wrapper = $(".more-labor"); //Fields wrapper
            var add_button = $(".btn-labor"); //Add button ID

            var x = 1; //initlal box count

            $(add_button).click(function(e) { //on add input button click
                e.preventDefault();
                if (x < max_fields) { //max input box allowed
                    x++; //text box increment
                    $(wrapper).append(`@include('components.user.laborfield')`); //add input box
                }
            });

            $(wrapper).on("click", ".remove", function(e) { //user click on remove text
                e.preventDefault();
                $(this).parents(".labor-extra-row").remove();
                x--;
            });

            $('#selectAllCrew').on('change', function () {
                $('.crew-checkbox').prop('checked', this.checked);
            });

            $('.crew-checkbox').on('change', function () {
                $('#selectAllCrew').prop(
                    'checked',
                    $('.crew-checkbox').length === $('.crew-checkbox:checked').length
                );
            });
            $(".new-project").hide();

            $("#create-new").click(function () {
                $(".my-project").hide();
                $(".new-project").fadeIn(250);
            });

            $("#back").click(function () {
                $(".new-project").hide();
                $(".my-project").fadeIn(250);
            });

            $('#selectAllCrew').on('change', function () {
                $('.crew-checkbox').prop('checked', this.checked);
            });

            $('.crew-checkbox').on('change', function () {
                $('#selectAllCrew').prop(
                    'checked',
                    $('.crew-checkbox').length === $('.crew-checkbox:checked').length
                );
            });
        });
    </script>

@endsection()
