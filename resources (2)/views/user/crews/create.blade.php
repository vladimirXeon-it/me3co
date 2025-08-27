@extends('user.layouts.app')

@section('title', 'Crews')

@section('content')

    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Crews  @if ($project!=null)
                                    for {{$project->name}}
                                 @endif</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Crew</li>
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
                <div class="col-md-6 col-lg-4 order-lg-1 order-md-1 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            
                            <div class="pt-3 new-project">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Create crew</h3>
                                    <hr>
                                <h6>Quick Start Crew </h6>
                                <hr>
                                <button class="btn back-btn text-black btn-sm import_button"> <i class="fa fa-file-import"></i>
                                    Import Items</button>
                                <ul class="import-list">
                                    @php
                                        $master_labors = get_master_crews();
                                    @endphp
                                    @foreach ($master_labors as $master_labor)
                                        <li class="import-item">
                                            <input type="checkbox" class="check-box">
                                            {{ $master_labor->name }} <a
                                                href="{{ route('crew.import', ['id' => $master_labor->id]) }}">
                                                <i class="fa fa-file-import"></i> Use
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                </div>
                                <form method="post" action="{{ route('crew.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Crew name:</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Description:</label>
                                                <textarea class="form-control" name="description" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Labor Type:</label>
                                                <select class="form-control" name="labor_info[0][labor_type_id]" required>
                                                    <option value="">Select</option>
                                                    @php
                                                        $labor_types = get_user_labors();
                                                    @endphp

                                                    @foreach ($labor_types as $labor_type)
                                                        <option value="{{ $labor_type->id }}">{{ $labor_type->labor_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">How many of this labor type:</label>
                                                <input type="text" class="form-control" name="labor_info[0][quantity]"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">How many regular Hrs per day:</label>
                                                <input type="text" class="form-control"
                                                    name="labor_info[0][hours_per_day]" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">How many hours Overtime per day:</label>
                                                <input type="text" class="form-control"
                                                    name="labor_info[0][overtime_per_day]">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">How many double time per day:</label>
                                                <input type="text" class="form-control"
                                                    name="labor_info[0][doubletime_per_day]">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="more-labor"></div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <a href="javascript:void(0);" class="btn back-btn text-black btn-labor">Add More</a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn back-btn text-black"
                                                    id="back">Back</a>
                                                <button class="btn save-btn text-white">Save</button>
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
                $(this).parents(".form-row").remove();
                x--;
            })
        });
    </script>

@endsection()
