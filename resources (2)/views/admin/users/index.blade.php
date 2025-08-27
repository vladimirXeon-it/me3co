@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Users</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Users</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Users</h5>
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
                                                        class="dataTable-sorter">Email</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Username</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Company</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Joined At</a></th>
                                                <th scope="col" data-sortable=""><a href="#"
                                                        class="dataTable-sorter">Actions</a></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($users as $user)
                                                <tr>
                                                    <td>{{ $i++ }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->username }}</td>
                                                    <td>{{ $user->company }}</td>
                                                    <td>{{ $user->created_at->format('d F, Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.users.get', ['id'=>$user->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
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
