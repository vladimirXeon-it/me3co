@extends('admin.layouts.app')

@section('title', 'Add Equipment')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Equipment</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Equipments</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-6 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="pt-3 new-project">
                                <div class="text-center card-title">
                                    Add Equipment
                                </div>
                                <form method="post" action="{{ route('admin.equipments.create') }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Equipment Name:</label>
                                                <input type="text" name="name" class="form-control"
                                                     required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Description:</label>
                                                <textarea name="description" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-3">
                                            <div class="form-group">
                                                <label>Cost per day:</label>
                                                <input type="number" step="0.001" name="cost_per_day" class="form-control"
                                                    placeholder="$" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-4">
                                            <div class="form-group text-center">
                                                <a href="javascript:void(0);" class="btn btn-warning"
                                                    onclick="history.back()">Back</a>
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
            </div>
        </section>

    </main>


@endsection()
