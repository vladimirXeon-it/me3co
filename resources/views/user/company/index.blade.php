@extends('user.layouts.app')

@section('title', 'Company')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">My Company</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Company</li>
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
                <div class="col-md-6 col-lg-4 order-lg-1 order-md-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 my-project">
                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">My Company</h3>
                                <div class="text-center pt-3 pb-1">Create a new Company below or select an existing Company
                                    on the right.</div>

                                <div class="text-center py-3 mb-3">
                                    <img src="{{ asset('fronts') }}/assets/images/logos/new-project.png" class="proj-img">
                                    <a href="javascript:void(0);" id="create-new" class="btn btn-primary">Add Company</a>
                                </div>
                            </div>
                            <div class="pt-3 new-project" style="display:none;">
                                <div class="text-center">
                                    <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Create New Comapny</h3>
                                </div>
                                <form method="post" action="{{ route('company.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Name:</label>
                                                <input type="text" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Estimator Name:</label>
                                                <input type="text" class="form-control" name="estimator" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Phone:</label>
                                                <input type="tel" class="form-control" name="phone" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Email:</label>
                                                <input type="email" class="form-control" name="email" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Address:</label>
                                                <textarea class="form-control" rows="5" name="address" required></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>City:</label>
                                                <input type="text" class="form-control" name="city" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>State:</label>
                                                <input type="text" class="form-control" name="state" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group">
                                                <label>Country:</label>
                                                <input type="text" class="form-control" name="country" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                            <div class="form-group">
                                                <label>Zip Code:</label>
                                                <input type="text" class="form-control" name="zip" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn btn-warning"
                                                    id="back">Back</a>
                                                <button class="btn btn-primary">Create</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                </div>
                <div class="col-lg-8 order-lg-2 order-md-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="card-title">Existing Company</h4>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </div>
                        <!--end card-header-->
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search Company">
                                <button class="btn btn-success" type="button">Search!</button>
                            </div>
                            <div class="table-responsive browser_users">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Company Name</th>
                                            <th>Created Date</th>
                                            <th>Estimator</th>
                                            <th>Action</th>
                                        </tr>
                                        <!--end tr-->
                                    </thead>
                                    <tbody class="project-sec">
                                        @php
                                            $i = 1;
                                        @endphp

                                        @foreach ($companies as $company)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $company->name }}</td>
                                                <td>{{ $company->created_at->format('d F, Y') }}</td>
                                                <td>{{ $company->estimator }}</td>
                                                <td class="text-nowrap"><a
                                                        href="{{ route('company.edit', ['id' => $company->id]) }}"
                                                        class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a> <a
                                                        href="{{ route('company.delete', ['id' => $company->id]) }}"
                                                        class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                                <!--end table-->
                            </div>
                        </div>
                        <!--end card-body-->
                    </div>
                    <!--end card-->
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
    </script>
@endsection()
