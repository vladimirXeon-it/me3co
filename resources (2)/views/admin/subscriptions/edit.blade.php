@extends('admin.layouts.app')

@section('title', 'Edit Subscription Plans')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Subscriptions</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Subscriptions</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-5 mx-auto">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title form-title">Edit Subscription Plan</h5>

                            <!-- Vertical Form -->
                            <form class="row g-3 form" method="POST" action="{{ route('admin.subscriptions.update', ['id'=> $plan->id]) }}">
                                @csrf()
                                <div class="col-12">
                                    <label for="name" class="form-label">Name<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <input type="text" class="form-control" name="name"
                                            placeholder="Eg. Premium Plus Plan" value="{{ $plan->name }}" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Subscription Type<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <select type="text" class="form-control" name="type" required>
                                            <option value="0" @if($plan->type == 0) selected @endif()>Free</option>
                                            <option value="1" @if($plan->type == 1) selected @endif()>Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Price<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <input type="number" step="0.001" class="form-control" value="{{ $plan->price }}" name="price" placeholder="$"
                                            value="0" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Time Unit<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <select class="form-control" name="time_unit" required>
                                            <option value="0" @if($plan->type == 0) selected @endif()>Month</option>
                                            <option value="1" @if($plan->type == 1) selected @endif()>Year</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Number Of Project Allowed<small
                                            class="text-danger">Leave empty for no limit</small></label>
                                    <div class="input-group has-validation">
                                        <input type="number" step="0.001" class="form-control" value="{{ $plan->project_allowed }}" name="project_allowed">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Details<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <textarea class="form-control" name="description" required>{{ $plan->description }}</textarea>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form><!-- Vertical Form -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>


@endsection()

@section('script')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        let deleteButtons = document.querySelectorAll(".delete-button");
        deleteButtons.forEach(b => {
            b.addEventListener("click", (e) => {
                swal({
                        title: "Are you sure?",
                        text: "Once deleted, you will not be able to recover this Record",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            let url = e.target.dataset.action;
                            window.location.href = url
                        }
                    });
            })
        })
    </script>
@endsection()
