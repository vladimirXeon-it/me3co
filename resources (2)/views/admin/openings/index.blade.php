@extends('admin.layouts.app')

@section('title', 'Openings')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Openings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Openings</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Openings</h5>
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
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Project</th>
                                                <th>Description</th>
                                                <th>Labor Class</th>
                                                <th>Labor Type</th>
                                                <th>Opening Shape</th>
                                                <th>Height</th>
                                                <th>Width</th>
                                                <th>Elevation</th>
                                                <th>Header</th>
                                                <th>Bearing</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($openings as $opening)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $opening->user->name }}</td>
                                                    <td>{{ $opening->project->name }}</td>
                                                    <td>{{ $opening->description }}</td>
                                                    <td>{{ $opening->labor_class->name }}</td>
                                                    <td>{{ $opening->labor_type->name }}</td>
                                                    <td>{{ $opening->opening_shape->name }}</td>
                                                    <td>{{ $opening->height . $opening->measurement_unit }}</td>
                                                    <td>{{ $opening->length . $opening->measurement_unit }}</td>
                                                    <td>{{ $opening->elevation . $opening->measurement_unit }}</td>
                                                    <td>
                                                        @if (!$opening->header)
                                                            Inside
                                                        @else()
                                                            Outside
                                                        @endif()
                                                    </td>
                                                    <td>{{ $opening->bearing . $opening->measurement_unit }}</td>
                                                    <td>{{ $opening->created_at->format('d F, Y') }}</td>
                                                    <td class="text-nowrap"><a
                                                            href="{{ route('admin.opening.edit', ['id' => $opening->id]) }}"
                                                            class="btn btn-primary btn-sm"><i class="bi bi-pen"></i></a> <a
                                                            href="{{ route('admin.opening.delete', ['id' => $opening->id]) }}"
                                                            class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
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
        let noteButtons = document.querySelectorAll('[data-note]');

        noteButtons.forEach(b => {
            b.addEventListener('click', (e) => {
                let note = e.target.dataset.note;
                swal({
                    title: "Note",
                    text: note,
                    icon: "success",
                    buttons: false,
                })

            })
        })
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
