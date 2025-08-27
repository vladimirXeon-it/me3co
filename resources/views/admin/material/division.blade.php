@extends('admin.layouts.app')

@section('title', 'Material Division')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Material Division</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Material</li>
                    <li class="breadcrumb-item">Division</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-5">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title form-title">Add Material Division</h5>

                            <!-- Vertical Form -->
                            <form class="row g-3 form" method="POST"
                                action="{{ route('admin.material.division.create') }}">
                                @csrf()
                                <div class="col-12">
                                    <label for="name" class="form-label">Material Division<small
                                            class="text-danger">*</small></label>
                                    <div class="input-group has-validation">
                                        <input type="text" class="form-control" name="name"
                                            placeholder="Eg. Fireblock Foam" id="name" required>
                                        <div class="invalid-feedback">Please Enter Material Division</div>
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
                            <h5 class="card-title">Material Divisions</h5>
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
                                                        class="dataTable-sorter">Material Division</a></th>
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
                                            @foreach ($material_divisions as $material_division)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $material_division->name }}</td>
                                                    <td>{{ $material_division->created_at->format('d F, Y') }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger delete-button"
                                                            data-action="{{ route('admin.material.division.delete', ['id' => $material_division->id]) }}"><i
                                                                class="bi bi-trash"></i></button>
                                                        <button data-raw="{{ json_encode($material_division) }}"
                                                            data-update="{{ route('admin.material.division.update', ['id' => $material_division->id]) }}"
                                                            type="button" class="btn btn-sm btn-primary edit-button">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
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
    <script>
        let editButtons = document.querySelectorAll('[data-update]')
        editButtons.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                let url = btn.dataset.update;
                let data = JSON.parse(btn.dataset.raw);
                let form = document.querySelector('.form')
                let formTitle = document.querySelector('.form-title')

                form.action = url;
                formTitle.innerHTML = 'Edit Unit';

                Object.keys(data).forEach((key) => {
                    let input = document.querySelector(`[name="${key}"]`);
                    if(!input) return
                    document.querySelector(`[name="${key}"]`).value = data[key]
                })
            })
        })
    </script>
@endsection()
