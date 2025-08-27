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
                                <h4 class="page-title pb-md-0">Create Labor @if ($project!=null)
                                    for {{$project->name}}
                                 @endif</h2></h4>

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
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 my-project" style="">
                                <h3 class="text-dark font-24 fw-bold line-height-lg">My Labor <span class="float-end"><a
                                            href="javascript:void(0);" id="create-new" class="btn save-btn text-white">Create
                                            New</a></span></h3>
                                <hr>
                                <h6>Quick Start Labor Type</h6>
                                <hr>
                                <button class="btn back-btn text-black btn-sm import_button"> <i class="fa fa-file-import"></i>
                                    Import Items</button>
                                <ul class="import-list">
                                    @php
                                        $master_labors = get_master_labors();
                                    @endphp
                                    @foreach ($master_labors as $master_labor)
                                        <li class="import-item">
                                            <input type="checkbox" class="check-box">
                                            {{ $master_labor->labor_type }} <a
                                                href="{{ route('labor.import', ['id' => $master_labor->id]) }}">
                                                <i class="fa fa-file-import"></i> Use
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="pt-3 new-project" style="display: none;">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Create New Labor</h3>
                                </div>
                                <form method="post" action="{{ route('labor.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Labor Class:</label>
                                                <select class="form-control" name="labor_class_id" required>
                                                    <option selected hidden value="">Choose Class</option>
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
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Labor Type:</label>
                                                <input type="text" class="form-control" name="labor_type"
                                                    placeholder="Labor Type">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Hourly Cost:</label>
                                                <input type="number" step="0.001" value="0"
                                                    class="form-control hourly_cost" placeholder="Hourly Cost"
                                                    name="cost_per_hour" required>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group row input-project">
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="burdens[0][name]"
                                                placeholder="Burden">
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="number" step="0.001" class="form-control burden_percentage"
                                                name="burdens[0][percentage]" placeholder="%">
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="number" step="0.001" class="form-control burden_price"
                                                name="burdens[0][price]" placeholder="$">
                                        </div>
                                    </div>
                                    <div class="more-burdon"></div>
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
                                            <input type="text" name="total_cost" class="form-control total_cost"
                                                placeholder="Cost" readonly>
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

            var x = 1; //initlal box count

            $(add_button).click(function(e) { //on add input button click
                e.preventDefault();
                if (x < max_fields) { //max input box allowed
                    x++; //text box increment
                    $(wrapper).append(
                        `<div class="form-group row"><div class="col-sm-4"><input type="text" name="burdens[${x+1}][name]" class="form-control" placeholder="Burden"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][percentage]" class="form-control burden_percentage" placeholder="%"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][price]" class="form-control burden_price" placeholder="$"></div><div class="col-md-1"><div class="form-group"><button class="btn btn-danger btn-sm remove" style=""><i class="fa fa-trash"></i></button></div></div></div>`
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
        let importButton = document.querySelector('.import_button');
        importButton.addEventListener('click', async (e) => {
            let importItems = document.querySelectorAll('.import-item');
            let importCount = document.querySelectorAll('.import-item  input:checked');
            if (importCount.length == 0) {
                alert('Please select an Item to import!')
                return false;
            }
            for await (item of importItems) {
                let checkStatus = item.querySelector('input:checked');
                if (checkStatus) {
                    let url = item.querySelector('a').href;
                    await fetch(url);
                }
            }
            document.body.innerHTML+=`
            <div class="toast custom show position-fixed align-items-center text-white bg-primary border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
               &check; Labors Imported
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
            `

            setTimeout(() => {
                window.location.reload()
            }, 2000);
        })
    </script>
@endsection()
