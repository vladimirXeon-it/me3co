@extends('user.layouts.app')

@section('title', 'Edit Equipment')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">

            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="new-project">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">Update Equipment</h3>
                                    <p class="me3co-form-subtitle mb-0">
                                        Update the equipment information below.
                                    </p>
                                </div>

                                <form method="post" action="{{ route('equipment.update', ['id' => $equipment->id]) }}">
                                    @csrf()
                                    <input type="hidden" name="return_url" value="{{ request('return_url') }}">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Equipment Name</label>
                                                <input type="text" name="name" value="{{ $equipment->name }}"
                                                    class="form-control" placeholder="Equipment Name" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Cost per Day</label>
                                                <div class="input-group me3co-money-input">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.001" name="cost_per_day"
                                                        value="{{ $equipment->cost_per_day }}"
                                                        class="form-control" placeholder="0.00" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Equipment Description</label>
                                                <textarea name="description" class="form-control"
                                                    placeholder="Equipment Description"
                                                    style="height:110px;">{{ $equipment->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="javascript:void(0);" class="btn me3co-secondary-btn" onclick="history.back()">
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

        </div>
    </div>
@endsection()
