@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab d-flex flex-column"
     style="background-color:#f8f9fa;height:calc(100vh - 65px);min-height:0;overflow:hidden;padding:15px 12px;">

        <div class="container-fluid d-flex flex-column flex-grow-1 p-0"
            style="max-width:100%;height:100%;min-height:0;">
            <!-- end page title end breadcrumb -->
            <div class="row">
                <div class="col-lg-8 col-md-10 mx-auto">
                    <div class="card me3co-form-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="new-project">
                                <div class="mb-4">
                                    <h3 class="me3co-form-title mb-1">
                                        Update Contact
                                    </h3>
                                </div>
                                <form method="post" action="{{ route('contact.update', ['id' => $contact->id]) }}">
                                    @csrf()
                                    <div class="row g-3">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Contact Name:</label>
                                                <input type="text" value="{{ $contact->name }}" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Company Name:</label>
                                                <input type="text" class="form-control" value="{{ $contact->company }}" name="company" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Phone:</label>
                                                <input type="tel" class="form-control" name="phone" value="{{ $contact->phone }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Email:</label>
                                                <input type="email" class="form-control" name="email" value="{{ $contact->email }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Company Address:</label>
                                                <textarea class="form-control" rows="5" name="address" required>{{ $contact->address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">City:</label>
                                                <input type="text" class="form-control" name="city" value="{{ $contact->city }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">State:</label>
                                                <input type="text" class="form-control" name="state" value="{{ $contact->state }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Country:</label>
                                                <input type="text" class="form-control" name="country" value="{{ $contact->country }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group input-project">
                                                <label class="form-label fw-bold text-14">Zip Code:</label>
                                                <input type="text" class="form-control" name="zip" value="{{ $contact->zip }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <hr class="my-4">

                                            <div class="d-flex justify-content-end gap-2">

                                                <a href="javascript:void(0);"
                                                onclick="history.back()"
                                                class="btn me3co-secondary-btn">
                                                    Back
                                                </a>

                                                <button class="btn me3co-primary-btn">
                                                    <i class="fa fa-save me-1"></i>
                                                    Save Contact
                                                </button>

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
