@extends('admin.layouts.app')

@section('title', 'Company')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Company</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Company</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project" >
                                <div class="text-center card-title">
                                    Edit Company
                                </div>
                                <form method="post" action="{{ route('admin.company.update', ['id' => $company->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Name:</label>
                                                <input type="text" value="{{ $company->name }}" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Estimator Name:</label>
                                                <input type="text" value="{{ $company->estimator }}" class="form-control" name="estimator" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Phone:</label>
                                                <input type="tel" class="form-control" value="{{ $company->phone }}" name="phone" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Email:</label>
                                                <input type="email" class="form-control" value="{{ $company->email }}" name="email" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Address:</label>
                                                <textarea class="form-control" rows="5" name="address" required>{{ $company->address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>City:</label>
                                                <input type="text" class="form-control" value="{{ $company->city }}" name="city" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>State:</label>
                                                <input type="text" class="form-control" name="state" value="{{ $company->state }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group">
                                                <label>Country:</label>
                                                <input type="text" class="form-control" name="country" value="{{ $company->country }}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group">
                                                <label>Zip Code:</label>
                                                <input type="text" class="form-control" name="zip" value="{{ $company->zip }}" required>
                                            </div>
                                        </div>
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