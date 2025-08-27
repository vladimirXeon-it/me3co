@extends('user.layouts.app')

@section('title', 'Assemblies')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Assembiles</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Assembiles</li>
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
                            <div class="pt-3">
                                <div class="form-group">
                                    <label>Find Assemblies</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Search Assemblies">
                                        <button class="btn btn-success" type="button">Search</button>
                                    </div>
                                    <hr>
                                </div>
                            </div>
                            <div>
                                <h5>My Assemblies <span class="float-end"><a href=""
                                            class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create New</a></span>
                                </h5>
                                <hr>
                                <div class="form-group">
                                    <ul id="tree1" class="tree">
                                        <li class="branch"><i class="indicator bi-folder-plus"></i><a
                                                href="#">Assembiles</a>
                                            <ul class="collapse">
                                                <li><a href="#"></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <hr>
                            </div>
                            <div>
                                <h5>Quick Start Assemblies</h5>
                                <hr>
                                <div class="form-group">
                                    <ul id="tree2" class="tree">
                                        <li class="branch"><i class="indicator bi-folder-plus"></i><a
                                                href="#">Assembiles 1</a>
                                            <ul class="collapse">
                                                <li><a href="#">Assembiles</a></li>
                                                <li><a href="#">Assembiles</a></li>
                                            </ul>
                                        </li>
                                        <li class="branch"><i class="indicator bi-folder-plus"></i><a
                                                href="#">Assembiles 2</a>
                                            <ul class="collapse">
                                                <li><a href="#">Assembiles</a></li>
                                                <li><a href="#">Assembiles</a></li>
                                            </ul>
                                        </li>
                                        <li class="branch"><i class="indicator bi-folder-plus"></i><a
                                                href="#">Assembiles 3</a>
                                            <ul class="collapse">
                                                <li><a href="#">Assembiles</a></li>
                                                <li><a href="#">Assembiles</a></li>
                                            </ul>
                                        </li>
                                        <li class="branch"><i class="indicator bi-folder-plus"></i><a
                                                href="#">Assembiles 4</a>
                                            <ul class="collapse">
                                                <li><a href="#">Assembiles</a></li>
                                                <li><a href="#">Assembiles</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <hr>
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
                                    <h4 class="card-title">Basic Information</h4>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </div>
                        <!--end card-header-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Assembly Name:</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Assembly ID:</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Unit of Measure:</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label>Description:</label>
                                        <textarea class="form-control" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group text-center">
                                        <button class="btn btn-secondary"><i class="mdi mdi-cancel"></i> Cancel</button>
                                        <button class="btn btn-success"><i class="mdi mdi-check"></i> Save As</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                        <div class="card-footer">
                            <h5>Required Items</h5>
                            <table class="table table-striped">
                                <thead class="bg-dark font-12 text-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Unit Of Measure</th>
                                        <th>Purchase Unit</th>
                                        <th>Formula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Wearing Course</td>
                                        <td>cu yd</td>
                                        <td>Tons</td>
                                        <td>[MeasuredArea]*([DepthOfWearingCourseInInches]/12)/27*2</td>
                                    </tr>
                                    <tr>
                                        <td>Binder Course</td>
                                        <td>cu yd</td>
                                        <td>Tons</td>
                                        <td>[MeasuredArea]*([DepthOfWearingCourseInInches]/12)/27*2</td>
                                    </tr>
                                    <tr>
                                        <td>Asphalt Paving Labor</td>
                                        <td>cu yd</td>
                                        <td>Tons</td>
                                        <td>[MeasuredArea]*([DepthOfWearingCourseInInches]/12)/27*2</td>
                                    </tr>
                                    <tr>
                                        <td>Aggregate Base</td>
                                        <td>cu yd</td>
                                        <td>Tons</td>
                                        <td>[MeasuredArea]*([DepthOfWearingCourseInInches]/12)/27*2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->

            </div>
            <!--end row-->

        </div><!-- container -->
    </div>
@endsection()
