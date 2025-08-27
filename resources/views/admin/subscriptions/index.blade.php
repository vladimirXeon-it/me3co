@extends('admin.layouts.app')

@section('title', 'Subscription Plans')

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
                <div class="col-5">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title form-title">Add Subscription Plan</h5>

                            <!-- Vertical Form -->
                            <form class="row g-3 form" method="POST" action="{{ route('admin.subscriptions') }}">
                                @csrf()
                                <div class="col-12">
                                    <label for="name" class="form-label">Name<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <input type="text" class="form-control" name="name"
                                            placeholder="Eg. Premium Plus Plan" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Subscription Type<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <select type="text" class="form-control" name="type" required>
                                            <option value="0">Free</option>
                                            <option value="1">Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Price<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <input type="number" step="0.001" class="form-control" name="price" placeholder="$"
                                            value="0" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Time Unit<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <select class="form-control" name="time_unit" required>
                                            <option value="0">Month</option>
                                            <option value="1">Year</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Number Of Project Allowed<small
                                            class="text-danger">(Leave empty for no limit)</small></label>
                                    <div class="input-group has-validation">
                                        <input type="number" step="0.001" class="form-control" name="project_allowed">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="name" class="form-label">Details<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <textarea class="form-control" name="description" required></textarea>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form><!-- Vertical Form -->

                        </div>
                    </div>

                </div>
                <div class="col-7">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Subscription Plans</h5>
                            <div class="dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns">
                                <div class="dataTable-top">
                                    <div class="dataTable-dropdown"><label><select class="dataTable-selector">
                                                <option value="5">5</option>
                                                <option value="10" selected="">10</option>
                                                <option value="15">15</option>
                                                <option value="20">20</option>
                                                <option value="25">25</option>
                                            </select> entries per page</label></div>
                                    <div class="dataTable-search"><input class="dataTable-input" placeholder="Search..."
                                            type="text"></div>
                                </div>
                                <div class="dataTable-container">
                                    <table class="table datatable dataTable-table">
                                        <thead>
                                            <tr>
                                                <th scope="col" data-sortable="" style="width: 5.58912%;"><a
                                                        href="#" class="dataTable-sorter">#</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Name</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Price</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">
                                                        Created At
                                                    </a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Actions</a></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($plans as $plan)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $plan->name }}</td>
                                                    <td> $ {{ $plan->price }}/ {{ get_time_unit($plan->time_unit) }}</td>
                                                    <td>{{ $plan->created_at->format('d F, Y') }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger delete-button"
                                                            data-action="{{ route('admin.subscriptions.delete', ['id' => $plan->id]) }}"><i
                                                                class="bi bi-trash"></i></button>
                                                        <a type="button" class="btn btn-sm btn-primary"
                                                            href="{{ route('admin.subscriptions.edit', ['id' => $plan->id]) }}"><i
                                                                class="bi bi-pen"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="dataTable-bottom">
                                    <nav class="dataTable-pagination">
                                        <ul class="dataTable-pagination-list"></ul>
                                    </nav>
                                </div>
                            </div>
                            <!-- End Table with stripped rows -->

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
