@extends('user.layouts.app')

@section('title', 'Edit Equipment')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Equipment  @if ($project!=null)
                                    for {{$project->name}}
                                 @endif</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Equipment</li>
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
                <div class="col-md-8 mx-auto order-lg-1 order-md-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 my-project">
                                <h3 class="text-dark font-24 fw-bold line-height-lg">Equipment <span class="float-end"><a
                                            href="javascript:void(0);" id="create-new" class="btn save-btn text-white">Create
                                            New</a></span></h3>
                                <hr>
                                <h6>Quick Start Equipments</h6>
                                <hr>
                                <button class="btn back-btn text-black btn-sm import_button"> <i class="fa fa-file-import"></i>
                                    Import Items</button>
                                <ul class="import-list">
                                    @php
                                        $master_equipments = get_master_equipments();
                                    @endphp
                                    @foreach ($master_equipments as $master_equipment)
                                        <li class="import-item"><input type="checkbox" class="check-box">
                                            {{ $master_equipment->name }} <a
                                                href="{{ route('equipment.import', ['id' => $master_equipment->id]) }}">
                                                <i class="fa fa-file-import"></i> Use
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="pt-3 new-project" style="display:none;">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Create New Equipment
                                    </h3>
                                </div>
                                <form method="post" action="{{ route('equipment.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Equipment Name:</label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Equipment Name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Equipment Description:</label>
                                                <textarea type="text" name="description" class="form-control" placeholder="Equipment Description"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Cost per day:</label>
                                                <input type="number" step="0.001" name="cost_per_day"
                                                    class="form-control" placeholder="$" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn back-btn text-black"
                                                    id="back">Back</a>
                                                <button class="btn save-btn text-white">Create</button>
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
            document.body.innerHTML += `
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
