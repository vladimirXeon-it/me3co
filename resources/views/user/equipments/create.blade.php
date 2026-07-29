@extends('user.layouts.app')

@section('title', 'Edit Equipment')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- Page-Title -->
            @if ($project != null)
                <div class="alert border-0 shadow-sm mb-3"
                    style="background:#eef5ff;border-left:4px solid #0d6efd!important;border-radius:14px;">
                    <div class="d-flex align-items-center">
                        <div class="labor-icon me-3">
                            <i class="fa fa-folder"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Creating equipment for project</div>
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
                                        <h3 class="me3co-form-title mb-1">My Equipment</h3>
                                        <p class="me3co-form-subtitle mb-0">Quick Start Equipment Templates</p>
                                    </div>

                                    <a href="javascript:void(0);" id="create-new" class="btn me3co-primary-btn">
                                        <i class="fa fa-plus me-1"></i>
                                        Create New
                                    </a>
                                </div>

                                <hr class="my-3">

                                @php
                                    if (request('project_id')) {
                                        session()->put(
                                            'idProject',
                                            request('project_id')
                                        );
                                    }
                                    $master_equipments = get_master_equipments();
                                @endphp

                                <button type="button" class="btn me3co-outline-btn import_button mb-3">
                                    <i class="fa fa-file-import me-1"></i>
                                    Import Items
                                </button>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check m-0">
                                        <input type="checkbox" class="form-check-input m-0" id="selectAllEquipment">
                                        <label class="form-check-label fw-semibold" for="selectAllEquipment">
                                            Select All
                                        </label>
                                    </div>

                                    <small class="text-muted">
                                        {{ count($master_equipments) }} templates
                                    </small>
                                </div>

                                <div class="labor-template-list">
                                    @foreach ($master_equipments as $master_equipment)
                                        <div class="labor-template-item import-item">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" class="check-box form-check-input equipment-checkbox m-0">

                                                <div class="labor-icon">
                                                    <i class="fa fa-wrench"></i>
                                                </div>

                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">
                                                        {{ $master_equipment->name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Quick start equipment template
                                                    </small>
                                                </div>
                                            </div>

                                            <a href="{{ route('equipment.import', ['id' => $master_equipment->id]) }}"
                                                class="btn me3co-small-primary">
                                                Use
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="new-project" style="display:none;">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">Create New Equipment</h3>
                                    <p class="me3co-form-subtitle mb-0">
                                        Fill in the details below to create a new equipment item.
                                    </p>
                                </div>

                                <form method="post" action="{{ route('equipment.create') }}">
                                    @csrf()
                                    <input type="hidden" name="return_url" value="{{ request('return_url') }}">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Equipment Name</label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Equipment Name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Cost per Day</label>
                                                <div class="input-group me3co-money-input">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.001" name="cost_per_day"
                                                        class="form-control" placeholder="0.00" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Equipment Description</label>
                                                <textarea name="description" class="form-control"
                                                    placeholder="Equipment Description"
                                                    style="height:110px;"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0);" class="btn me3co-secondary-btn" id="back">
                                            Back
                                        </a>

                                        <button class="btn me3co-primary-btn">
                                            <i class="fa fa-save me-1"></i>
                                            Save Equipment
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
        $(document).ready(function() {
            /*$('#create-new').click(function() {
                $('.new-project').show();
                $('.my-project').hide();
            });
            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').show();
            });*/

            $(".new-project").hide();

            $('#create-new').click(function() {
                $('.new-project').fadeIn(250);
                $('.my-project').hide();
            });

            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').fadeIn(250);
            });

            $('#selectAllEquipment').on('change', function () {
                $('.equipment-checkbox').prop('checked', this.checked);
            });

            $('.equipment-checkbox').on('change', function () {
                $('#selectAllEquipment').prop(
                    'checked',
                    $('.equipment-checkbox').length === $('.equipment-checkbox:checked').length
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
            document.body.innerHTML += `
            <div class="toast custom show position-fixed align-items-center text-white bg-primary border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                    &check; Equipment Imported
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
