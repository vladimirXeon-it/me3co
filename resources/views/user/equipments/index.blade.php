@php
    $return_url = request()->fullUrl();
@endphp

@extends('user.layouts.app')

@section('title', 'Equipments')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            
            <div class="project-page-header flex-shrink-0 mb-3">
                <div>
                    <h1>
                        @if($project)
                            My Equipment for {{ $project->name }}
                        @else
                            Equipment
                        @endif
                    </h1>
                </div>

                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('equipment.create', ['return_url' => $return_url]) }}">
                            <i class="fa fa-plus"></i>
                            Create Equipment
                        </a>
                    </div>
                </div>
            </div>

            <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3"> 
                <div class="me3co-table-wrapper">
                    <table class="table align-middle me3co-table mb-0" id="projectTableId">
                        <thead >
                            <tr>
                                <th scope="col" style="width: 50px; background-color: #ffffff; font-size: 13px;">#</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Equipment Name</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Equipment Id</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Description</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Cost per day</th>
                                <th scope="col" class="text-end pe-3" style="background-color: #ffffff; font-size: 13px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @foreach ($equipments as $equipment)
                                <tr>
                                    <td class="fw-bold text-muted ps-2" style="font-size: 13px;">{{ $i++ }}</td>
                                    <td class="fw-semibold text-dark" style="font-size: 13px;">{{ $equipment->name }}</td>
                                    <td style="font-size: 13px;">{{ $equipment->unique_id }}</td>
                                    <td class="text-muted" style="font-size: 13px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $equipment->description }}
                                    </td>
                                    <td class="fw-medium" style="font-size: 13px;">{{ number_format($equipment->cost_per_day, 2) }}$</td>
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
                                                    href="{{ route('equipment.edit', ['id' => $equipment->id, 'return_url' => $return_url]) }}">
                                                        <i class="fa fa-pencil me-2 text-primary"></i>
                                                        Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                    href="{{ route('equipment.delete', ['id' => $equipment->id]) }}"
                                                    onclick="return confirm('Are you sure you want to delete this equipment?')">
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

        </div>
    </div>
@endsection()

@section('script')
    <script>
        function goBack() {
            window.location.href = "/project"; // fallback
        }
        $(document).ready(function() {
            // Inicialización ÚNICA de DataTables corrigiendo duplicados y sin paginación nativa
            if ($.fn.DataTable.isDataTable('#projectTableId')) {
                $('#projectTableId').DataTable().destroy();
            }

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
                    searchPlaceholder: "Search equipment...",
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

            $('.dataTables_filter label').contents().filter(function(){
                return this.nodeType === 3;
            }).remove();

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
