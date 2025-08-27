@extends('user.layouts.app')
@section('title', 'Labors')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Edit Labor</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Labor</li>
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
                            <div class="pt-3 new-project" style="display: block;">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Edit Labor</h3>
                                </div>
                                <form method="post" action="{{ route('labor.update', ['id' => $labor->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Labor Class:</label>
                                                <select class="form-control" name="labor_class_id"
                                                    value="{{ $labor->labor_class_id }}" required>
                                                    <option hidden value="">Choose Class</option>
                                                    @php
                                                        $labor_classes = get_labor_class();
                                                    @endphp
                                                    @foreach ($labor_classes as $labor_class)
                                                        <option value="{{ $labor_class->id }}"
                                                            @if ($labor->labor_class_id == $labor_class->id) selected @endif()>
                                                            {{ $labor_class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Labor Type:</label>
                                                <input type="text" class="form-control" name="labor_type"
                                                    value="{{ $labor->labor_type }}" placeholder="Labor Type">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Hourly Cost:</label>
                                                <input type="text" class="form-control hourly_cost"
                                                    value="{{ $labor->cost_per_hour }}" placeholder="Hourly Cost"
                                                    name="cost_per_hour" required>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    @php
                                        $burdens = json_decode($labor->burdens);
                                    @endphp
                                    <div class="more-burdon">
                                        @foreach ($burdens as $key => $burden)
                                            <div class="form-group row burden input-project">
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control"
                                                        name="burdens[{{ $key }}][name]"
                                                        value="{{ $burden->name }}" placeholder="Burden">
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control burden_percentage"
                                                        name="burdens[{{ $key }}][percentage]" placeholder="%"
                                                        value="{{ $burden->percentage }}">
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control burden_price"
                                                        name="burdens[{{ $key }}][price]" placeholder="$"
                                                        value="{{ $burden->price }}">
                                                </div>
                                                @if ($key >= 3)
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-danger btn-sm remove"
                                                                style="">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <a href="javascript:void(0);" class="btn back-btn text-black btn-bur">Add More</a>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label class="col-form-label">Total Cost Per Hr:</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="text" name="total_cost" value="{{ $labor->total_cost }}"
                                                class="form-control total_cost" placeholder="Cost" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group text-center">
                                                    <a href="javascript:void(0);" class="btn back-btn text-black"
                                                        onclick="history.back()">Back</a>
                                                    <button class="btn save-btn text-white">Save</button>
                                                </div>
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
        let totalBurdens = 0;
        let totalBurdenPrice = 0;
        const calculateTotalCost = () => {
            let cost_per_hour = parseFloat($('.hourly_cost').val() || 0);
            let burdens = 0;
            let burdenPrice = 0;
            document.querySelectorAll('.burden_percentage').forEach(burden => {
                burdens += parseFloat(burden.value || 0);
                totalBurdens = burdens;
            });
            document.querySelectorAll('.burden_price').forEach((price) => {
                burdenPrice += parseFloat(price.value || 0);
                totalBurdenPrice = burdenPrice;
            })
            let totalCost = cost_per_hour + (cost_per_hour * totalBurdens / 100) + totalBurdenPrice;
            $('.total_cost').val(totalCost.toFixed(2))
        }
        $(document).ready(function() {
            var max_fields = 50; //maximum input boxes allowed
            var wrapper = $(".more-burdon"); //Fields wrapper
            var add_button = $(".btn-bur"); //Add button ID

            var x = {{ $key }}; //initlal box count

            $(add_button).click(function(e) { //on add input button click
                e.preventDefault();
                if (x < max_fields) { //max input box allowed
                    x++; //text box increment
                    $(wrapper).append(
                        `<div class="form-group row"><div class="col-sm-4"><input type="text" name="burdens[${x+1}][name]" class="form-control" placeholder="Burden"></div><div class="col-sm-3"><input type="text" name="burdens[${x+1}][percentage]" class="form-control burden_percentage" placeholder="%"></div><div class="col-sm-3"><input type="text" name="burdens[${x+1}][price]" class="form-control burden_price" placeholder="$"></div><div class="col-md-1"><div class="form-group"><button class="btn btn-danger btn-sm remove" style=""><i class="fa fa-trash"></i></button></div></div></div>`
                    ); //add input box
                }
            });

            $(wrapper).on("click", ".remove", function(e) { //user click on remove text
                e.preventDefault();
                $(this).parents(".form-group").remove();
                x--;
                calculateTotalCost();
            })
        });
        $('#create-new').click(function() {
            $('.new-project').show();
            $('.my-project').hide();
        });
        $('#back').click(function() {
            $('.new-project').hide();
            $('.my-project').show();
        });

        $(document).ready(function() {
            $('.hourly_cost').on('input', function() {
                calculateTotalCost();
            })
            $('body').on('input', '.burden_percentage', function() {
                calculateTotalCost();
            });
            $('body').on('input', '.burden_price', function() {
                calculateTotalCost();
            })
        })
    </script>
@endsection()
