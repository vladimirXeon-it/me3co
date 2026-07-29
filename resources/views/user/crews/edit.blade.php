@extends('user.layouts.app')

@section('title', 'Crews')

@section('content')

    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="pt-3 new-project">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">
                                        Update Crew
                                    </h3>

                                    <p class="me3co-form-subtitle mb-0">
                                        Update the crew information below.
                                    </p>
                                </div>
                                <form method="post" action="{{ route('crew.update', ['id' => $crew->id]) }}">
                                    @csrf()
                                    <a href="javascript:void(0);" class="btn me3co-secondary-btn mb-3" onclick="goBack()">
                                        Back
                                    </a>        
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Crew Name</label>
                                                <input type="text" name="name" value="{{ $crew->name }}"
                                                    class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Description</label>
                                                <input type="text" class="form-control" name="description"
                                                    value="{{ $crew->description }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-4">

                                    <h6 class="fw-bold text-primary mb-3">Labor Information</h6>

                                    <div class="more-labor">
                                        @php
                                            $labor_infos = json_decode($crew->labor_info);
                                            $i = 0;
                                        @endphp
                                        @foreach ($labor_infos as $labor_info)
                                            <div class="row g-3 form-row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group input-project">
                                                        <label class="form-label fw-bold text-14">Labor Type</label>
                                                        <select class="form-control" name="labor_info[{{ $i }}][labor_type_id]" required>
                                                            <option value="">Select</option>

                                                            @php
                                                                $labor_types = get_user_labors();
                                                            @endphp

                                                            @foreach ($labor_types as $labor_type)
                                                                <option @if ($labor_info->labor_type_id == $labor_type->id) selected @endif
                                                                    value="{{ $labor_type->id }}">
                                                                    {{ $labor_type->labor_type }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group input-project">
                                                        <label class="form-label fw-bold text-14">Quantity</label>
                                                        <input type="text" value="{{ $labor_info->quantity }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][quantity]" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group input-project">
                                                        <label class="form-label fw-bold text-14">Regular Hrs / Day</label>
                                                        <input type="text" value="{{ $labor_info->hours_per_day }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][hours_per_day]" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group input-project">
                                                        <label class="form-label fw-bold text-14">Overtime / Day</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->overtime_per_day }}"
                                                            name="labor_info[{{ $i }}][overtime_per_day]">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group input-project">
                                                        <label class="form-label fw-bold text-14">Double Time / Day</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->doubletime_per_day }}"
                                                            name="labor_info[{{ $i++ }}][doubletime_per_day]">
                                                    </div>
                                                </div>

                                                @if ($i > 1)
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove">
                                                            <i class="fa fa-trash me-1"></i>
                                                            Remove
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3">
                                        <a href="javascript:void(0)" class="btn me3co-outline-btn btn-labor">
                                            <i class="fa fa-plus me-1"></i>
                                            Add More
                                        </a>
                                    </div>
                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0);" class="btn me3co-secondary-btn" onclick="history.back()">
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
            if ($.fn.fSelect) {
                window.fs_test = $('.test').fSelect();
            }
        });
    </script>
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
        })
    </script>
    <script>
        $(document).ready(function() {
            var max_fields = 50;
            var wrapper = $(".more-labor");
            var add_button = $(".btn-labor");
            var x = {{ $i }};

            $(add_button).click(function(e) {
                e.preventDefault();

                if (x < max_fields) {
                    x++;

                    $(wrapper).append(`@include('components.user.laborfield')`);
                }
            });

            $(wrapper).on("click", ".remove", function(e) {

                $(this).closest(".form-row").remove();

                x--;
            });
        });
    </script>

@endsection()
