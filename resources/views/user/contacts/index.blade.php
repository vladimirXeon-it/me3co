@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- Page-Title -->
            <div class="project-page-header flex-shrink-0 mb-3">
                <div>
                    <h1>Contacts</h1>
                </div>

                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('contact.create') }}">
                            <i class="fa fa-plus"></i>
                            Create Contact
                        </a>
                    </div>
                </div>
            </div>
            <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3">
                <div class="me3co-table-wrapper">
                    <table class="table align-middle me3co-table mb-0" id="projectTableId">
                        <thead >
                            <tr>
                                <th>#</th>
                                <th>Contact Name</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Created Date</th>
                                <th>Company Name</th>
                                <th>Action</th>
                            </tr>
                            <!--end tr-->
                        </thead>
                        <tbody class="project-sec">
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->email }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>{{ $contact->created_at->format("d F, Y") }}</td>
                                    <td>{{ $contact->company }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm border text-muted px-2"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('contact.edit', ['id' => $contact->id]) }}">
                                                        <i class="fa fa-pencil me-2 text-primary"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        href="{{ route('contact.delete', ['id' => $contact->id]) }}"
                                                        onclick="return confirm('Are you sure you want to delete this contact?')">
                                                        <i class="fa fa-trash me-2"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!--end row-->
        </div><!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                scrollY: 'calc(100vh - 420px)',
                scrollCollapse: false,
                scrollX: false,
                dom:
                    '<"d-flex justify-content-between align-items-center mb-3"l f>' +
                    't' +
                    '<"d-flex justify-content-between align-items-center mt-3"i p>',
                language: {
                    search: "",
                    searchPlaceholder: "Search contacts...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });

            $('.dataTables_filter input')
                .addClass('form-control form-control-sm me3co-search');

            $('.dataTables_filter label').contents().filter(function () {
                return this.nodeType === 3;
            }).remove();

        });
    </script>
    <script>
        function goBack() {
            window.location.href = "/project"; // fallback
        }
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
