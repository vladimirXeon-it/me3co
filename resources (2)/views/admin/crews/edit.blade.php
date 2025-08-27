@extends('admin.layouts.app')

@section('title', 'Crews')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Crews</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">crews</li>
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
                                    Edit Crews
                                </div>
                                <form method="post" action="{{ route('admin.crews.update', ['id' => $crew->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Crew name:</label>
                                                <input type="text" name="name" value="{{ $crew->name }}"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Description:</label>
                                                <textarea class="form-control" name="description" required>{{ $crew->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Equipments:</label>
                                                <select class="test form-control hidden" multiple="multiple"
                                                    name="equipment_ids[]">
                                                    <option value="">Select</option>
                                                    @php
                                                        $equipments = get_equipments();
                                                        $equipment_ids = json_decode($crew->equipment_ids);
                                                    @endphp
                                                    @foreach ($equipments as $equipment)
                                                        <option value="{{ $equipment->id }}"
                                                            @if (in_array($equipment->id, $equipment_ids)) selected @endif>
                                                            {{ $equipment->name }}
                                                        </option>
                                                    @endforeach()
                                                </select>
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
                                                    <div class="form-group">
                                                        <label>Choose Labor Type:</label>
                                                        <select class="form-control"
                                                            name="labor_info[{{ $i }}][labor_type_id]" required>
                                                            <option value="">Choose Labor</option>
                                                            @php
                                                                $labor_types = get_labor_type();
                                                            @endphp

                                                            @foreach ($labor_types as $labor_type)
                                                                <option @if ($labor_info->labor_type_id == $labor_type->id) selected @endif
                                                                    value="{{ $labor_type->id }}">
                                                                    {{ $labor_type->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>How many of this labor type:</label>
                                                        <input type="text" value="{{ $labor_info->quantity }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][quantity]" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>How many regular Hrs per day:</label>
                                                        <input type="text" value="{{ $labor_info->hours_per_day }}"
                                                            class="form-control"
                                                            name="labor_info[{{ $i }}][hours_per_day]" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>How many hours Overtime per day:</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->overtime_per_day }}"
                                                            name="labor_info[{{ $i }}][overtime_per_day]"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>How many double time per day:</label>
                                                        <input type="text" class="form-control"
                                                            value="{{ $labor_info->doubletime_per_day }}"
                                                            name="labor_info[{{ $i++ }}][doubletime_per_day]"
                                                            required>
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
                                    <div class="form-group row mt-3">
                                        <div class="col-sm-12">
                                            <a href="javascript:void(0);" class="btn btn-warning btn-labor">Add More</a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn btn-warning"
                                                    onclick="history.back()">Back</a>
                                                <button class="btn btn-primary">Save</button>
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
    <script src="{{ asset('fronts') }}/assets/js/fSelect.js"></script>
    <script>
        $(document).ready(function(x) {
          //  window.fs_test = $('.test').fSelect();
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
                    $(wrapper).append(
                        `@include('components.user.laborfield')`
                        ); //add input box
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
