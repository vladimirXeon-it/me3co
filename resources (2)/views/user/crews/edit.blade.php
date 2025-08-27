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
                                <h4 class="page-title pb-md-0">Crews</h4>

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
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Update Crew</h3>
                                </div>
                                <form method="post" action="{{ route('crew.update', ['id' => $crew->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Crew name:</label>
                                                <input type="text" name="name" value="{{ $crew->name }}"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Description:</label>
                                                <textarea class="form-control" name="description" required>{{ $crew->description }}</textarea>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <hr>
                                    <div class="more-labor">
                                        @php
                                            $labor_infos = json_decode($crew->labor_info);
                                            $i = 0;
                                        @endphp
                                        @foreach ($labor_infos as $labor_info)
                                            <div class="row form-row">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group input-project">
                                                        <label class="text-14">Choose Labor Type:</label>
                                                        <select class="form-control"
                                                            name="labor_info[{{ $i }}][labor_type_id]" required>
                                                            <option value="">Choose Labor</option>
                                                            @php
                                                                $labor_types = get_user_labors();
                                                            @endphp

                                                            @foreach ($labor_types as $labor_type)
                                                                <option @if ($labor_info->labor_type_id == $labor_type->id) selected @endif
                                                                    value="{{ $labor_type->id }}">
                                                                    {{ $labor_type->labor_type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group input-project">
                                                        <label class="text-14">How many of this labor type:</label>
                                                        <input type="text" value="{{ $labor_info->quantity }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][quantity]" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group input-project">
                                                        <label class="text-14">How many regular Hrs per day:</label>
                                                        <input type="text" value="{{ $labor_info->hours_per_day }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][hours_per_day]" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group input-project">
                                                        <label class="text-14">How many hours Overtime per day:</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->overtime_per_day }}"
                                                            name="labor_info[{{ $i }}][overtime_per_day]">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group input-project">
                                                        <label class="text-14">How many double time per day:</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->doubletime_per_day }}"
                                                            name="labor_info[{{ $i++ }}][doubletime_per_day]">
                                                    </div>
                                                </div>

                                                @if ($i > 1)
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="form-group"><button class="btn btn-danger btn-sm remove"
                                                                style="">
                                                                <i class="fa fa-trash"></i> Remove</button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <a href="javascript:void(0);" class="btn back-btn text-black btn-labor">Add More</a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn back-btn text-black"
                                                    onclick="history.back()">Back</a>
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
            window.fs_test = $('.test').fSelect();
        })
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
            var max_fields = 50; //maximum input boxes allowed
            var wrapper = $(".more-labor"); //Fields wrapper
            var add_button = $(".btn-labor"); //Add button ID

            var x = {{ $i }}; //initlal box count

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
