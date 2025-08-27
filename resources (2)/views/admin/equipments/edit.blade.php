@extends('admin.layouts.app')

@section('title', 'Edit Equipment')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Equipments</h1>
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
                                    Edit Equipment
                                </div>
                                <form method="post" action="{{ route('admin.equipments.update', ['id' => $equipment->id]) }}">
                                    @csrf()
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-group">
                                                <label>Equipment Name:</label>
                                                <input type="text" name="name" value="{{ $equipment->name }}" class="form-control"
                                                    placeholder="Equipment Name" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-3">
                                            <div class="form-group">
                                                <label>Cost per day:</label>
                                                <input type="number" step="0.001" name="cost_per_day" value="{{ $equipment->cost_per_day }}" class="form-control"
                                                    placeholder="$" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-4">
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
