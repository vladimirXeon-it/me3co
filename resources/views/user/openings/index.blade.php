@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            <!-- Page-Title -->
            <div class="project-page-header flex-shrink-0 mb-3">
                <div>
                    <h1>Openings</h1>
                </div>

                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('opening.create') }}">
                            <i class="fa fa-plus"></i>
                            Create Opening
                        </a>
                    </div>
                </div>
            </div>
            <!--end row-->
            <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3"> 
                <div class="me3co-table-wrapper">
                    <table class="table align-middle me3co-table mb-0" id="projectTableId">
                        <thead >
                            <tr>
                                <th>#</th>
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
                            <!--end tr-->
                        </thead>
                        <tbody class="project-sec">
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($openings as $opening)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $opening->project->name }}</td>
                                    <td>{{ $opening->description }}</td>
                                    <td>{{ $opening->labor_class->name }}</td>
                                    <td>{{ $opening->labor->labor_type }}</td>
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
                                                        href="{{ route('opening.edit', ['id' => $opening->id]) }}">
                                                        <i class="fa fa-pencil me-2 text-primary"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        href="{{ route('opening.delete', ['id' => $opening->id]) }}"
                                                        onclick="return confirm('Are you sure you want to delete this opening?')">
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

        </div>
        <!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        function goBack() {
            window.location.href = "/project"; // fallback
        }
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
                scrollX: true,

                dom:
                    '<"d-flex justify-content-between align-items-center mb-3"l f>' +
                    't' +
                    '<"d-flex justify-content-between align-items-center mt-3"i p>',

                language: {
                    search: "",
                    searchPlaceholder: "Search openings...",
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
@endsection

