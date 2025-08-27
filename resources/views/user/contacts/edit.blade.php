@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Contact</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Contacts</li>
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
                <div class="col-md-6 col-lg-4 order-lg-1 order-md-1  mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Edit Contact</h3>
                                </div>
                                <form method="post" action="{{ route('contact.update', ['id' => $contact->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Contact Name:</label>
                                                <input type="text" value="{{ $contact->name }}" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Company Name:</label>
                                                <input type="text" class="form-control" value="{{ $contact->company }}" name="company" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Phone:</label>
                                                <input type="tel" class="form-control" name="phone" value="{{ $contact->phone }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Email:</label>
                                                <input type="email" class="form-control" name="email" value="{{ $contact->email }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">Company Address:</label>
                                                <textarea class="form-control" rows="5" name="address" required>{{ $contact->address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">City:</label>
                                                <input type="text" class="form-control" name="city" value="{{ $contact->city }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group input-project">
                                                <label class="text-14">State:</label>
                                                <input type="text" class="form-control" name="state" value="{{ $contact->state }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group input-project">
                                                <label class="text-14">Country:</label>
                                                <input type="text" class="form-control" name="country" value="{{ $contact->country }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group input-project">
                                                <label class="text-14">Zip Code:</label>
                                                <input type="text" class="form-control" name="zip" value="{{ $contact->zip }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn back-btn text-black" onclick="history.back()">Back</a>
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
