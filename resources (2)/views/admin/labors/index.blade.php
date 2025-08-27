@extends('admin.layouts.app')

@section('title', 'Labors')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Labor Type</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">labor type</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                Labor Type
                                <a href="{{ route('admin.labor.add') }}" class="btn btn-primary float-end">Create</a>
                            </h5>
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
                                <div class="dataTable-container table-responsive">
                                    <table class="table datatable dataTable-table text-nowrap">
                                        <thead>
                                            <tr>
                                                <th scope="col" data-sortable="" style="width: 5.58912%;"><a
                                                        href="#" class="dataTable-sorter">#</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Labor
                                                        Id</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Labor
                                                        Class</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Labor
                                                        Type</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Cost
                                                        Per Hour</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Burdens</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Total
                                                        Cost</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Created At</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Actions</a></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($labors as $labor)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $labor->unique_id }}</td>
                                                    <td>{{ $labor->labor_class->name }}</td>
                                                    <td>{{ $labor->labor_type }}</td>
                                                    <td>{{ $labor->cost_per_hour }}$</td>
                                                    <td>
                                                        @php
                                                            $burdens = json_decode($labor->burdens);
                                                        @endphp
                                                        <ul>
                                                            @foreach ($burdens as $burden)
                                                                <li><b>{{ $burden->name }}</b>({{ $burden->percentage?$burden->percentage:'0' }}%, {{ $burden->price?$burden->price:'0' }}$)
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                    <td>{{ $labor->total_cost }}$</td>
                                                    <td>
                                                        {{ $labor->created_at->format('d F, Y') }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.labor.edit', ['id' => $labor->id]) }}"
                                                            class="btn btn-primary btn-sm"><i class="bi bi-pen"></i></a> <a
                                                            href="#"
                                                            data-action="{{ route('admin.labor.delete', ['id' => $labor->id]) }}"
                                                            class="btn btn-danger btn-sm delete-button"><i
                                                                class="bi bi-trash"></i></a>
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
