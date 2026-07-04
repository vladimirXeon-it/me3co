@extends('user.layouts.app')
@section('title', 'Labors')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- Page-Title -->
            @if(isset($project) && $project)
            <div class="alert border-0 shadow-sm mb-3"
                style="background:#eef5ff;border-left:4px solid #0d6efd!important;">
                <div class="d-flex align-items-center">
                    <i class="fa fa-folder-open text-primary me-3 fs-4"></i>

                    <div>
                        <div class="fw-semibold">
                            Creating labor for project
                        </div>

                        <div class="text-muted">
                            {{ $project->project_name }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!--end row-->
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">

                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">

                            {{-- QUICK START --}}
                            <div class="my-project">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="me3co-form-title mb-1">My Labor</h3>
                                        <p class="me3co-form-subtitle mb-0">Quick Start Labor Type</p>
                                    </div>

                                    <a href="javascript:void(0);" id="create-new" class="btn me3co-primary-btn">
                                        <i class="fa fa-plus me-1"></i>
                                        Create New
                                    </a>
                                </div>

                                <hr class="my-3">

                                <button type="button" class="btn me3co-outline-btn import_button mb-3">
                                    <i class="fa fa-file-import me-1"></i>
                                    Import Items
                                </button>
                                @php
                                    $master_labors = get_master_labors();
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check m-0">
                                        <input
                                            type="checkbox"
                                            class="form-check-input m-0"
                                            id="selectAllLabor">

                                        <label class="form-check-label fw-semibold" for="selectAllLabor">
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
                                                <input type="checkbox" class="check-box form-check-input labor-checkbox m-0">

                                                <div class="labor-icon">
                                                    <i class="fa fa-user"></i>
                                                </div>

                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $master_labor->labor_type }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Quick start labor template
                                                    </small>
                                                </div>
                                            </div>

                                            <a href="{{ route('labor.import', ['id' => $master_labor->id]) }}"
                                                class="btn me3co-small-primary">
                                                Use
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- CREATE NEW FORM --}}
                            <div class="new-project" style="display:none;">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">Create New Labor</h3>
                                    <p class="me3co-form-subtitle mb-0">
                                        Fill in the details below to create a new labor.
                                    </p>
                                </div>

                                <form method="post" action="{{ route('labor.create') }}">
                                    @csrf()

                                    @if ($project != null)
                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                    @endif

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">
                                                    Labor Class
                                                </label>
                                                <select class="form-control" name="labor_class_id" required>
                                                    <option selected hidden value="">Choose Class</option>
                                                    @php
                                                        $labor_classes = get_labor_class();
                                                    @endphp
                                                    @foreach ($labor_classes as $labor_class)
                                                        <option value="{{ $labor_class->id }}">
                                                            {{ $labor_class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">
                                                    Labor Type
                                                </label>
                                                <input type="text" class="form-control" name="labor_type"
                                                    placeholder="Labor Type">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">
                                                    Hourly Cost
                                                </label>
                                                <div class="input-group me3co-money-input">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.001" value="0"
                                                        class="form-control hourly_cost"
                                                        placeholder="Hourly Cost"
                                                        name="cost_per_hour" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h6 class="fw-bold text-primary mb-3">Burdens</h6>

                                    <div class="form-group row g-3 input-project">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="burdens[0][name]"
                                                placeholder="Burden">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="number" step="0.001" class="form-control burden_percentage"
                                                name="burdens[0][percentage]" placeholder="%">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="number" step="0.001" class="form-control burden_price"
                                                name="burdens[0][price]" placeholder="$">
                                        </div>
                                    </div>

                                    <div class="more-burdon mt-3"></div>

                                    <div class="mt-3">
                                        <a href="javascript:void(0);" class="btn me3co-outline-btn btn-bur">
                                            <i class="fa fa-plus me-1"></i>
                                            Add More
                                        </a>
                                    </div>

                                    <hr class="my-4">

                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <label class="fw-bold mb-0">Total Cost Per Hr:</label>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="input-group me3co-money-input">
                                                <span class="input-group-text">$</span>
                                                <input type="text" name="total_cost" class="form-control total_cost"
                                                    placeholder="0.00" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0);" class="btn me3co-secondary-btn" id="back">
                                            Back
                                        </a>

                                        <button class="btn me3co-primary-btn">
                                            <i class="fa fa-save me-1"></i>
                                            Save
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
                        `<div class="form-group row g-3 align-items-center burden-row">
                            <div class="col-md-4">
                                <input type="text" name="burdens[${x+1}][name]" class="form-control" placeholder="Burden">
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="0.001" name="burdens[${x+1}][percentage]" class="form-control burden_percentage" placeholder="%">
                            </div>

                            <div class="col-md-3">
                                <input type="number" step="0.001" name="burdens[${x+1}][price]" class="form-control burden_price" placeholder="$">
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-danger btn-sm remove">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>`
                    );
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
            });
            $('body').on('input', '.burden_percentage', function() {
                calculateTotalCost();
            });
            $('body').on('input', '.burden_price', function() {
                calculateTotalCost();
            });
            
            $('#selectAllLabor').on('change', function () {

                $('.labor-checkbox').prop('checked', this.checked);

            });

            $('.labor-checkbox').on('change', function () {

                $('#selectAllLabor').prop(
                    'checked',
                    $('.labor-checkbox').length === $('.labor-checkbox:checked').length
                );

            });
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
