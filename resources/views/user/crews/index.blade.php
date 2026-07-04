@php
    $return_url = request()->fullUrl();
@endphp

@extends('user.layouts.app')

@section('title', 'Crews')

@section('content')

    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Crews</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Crew</li>
                                </ol>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end page-title-box-->
                </div>
                <!--end col-->
            </div> --}}
            <div class="project-page-header flex-shrink-0 mb-3">
                <div>
                    <h1>
                        @if(isset($project) && $project)
                            My Crews for {{ $project->name }}
                        @else
                            Crews
                        @endif
                    </h1>
                </div>

                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('crew.create', ['return_url' => $return_url]) }}">
                            <i class="fa fa-plus"></i>
                            Create New Crew
                        </a>
                    </div>
                </div>
            </div>
            <div class="row g-2 mb-3 flex-shrink-0">
                <div class="col-12">
                    <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3"
                        style="border-radius:16px;">
                        <div class="me3co-table-wrapper flex-grow-1">
                            <table class="table align-middle me3co-table mb-0" id="projectTableId">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Crew</th>
                                        <th>Description</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                    <!--end tr-->
                                </thead>
                                <tbody>
                                    @php
                                        $i = 1;
                                    @endphp
                                    @foreach ($crews as $crew)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $crew->name }}</td>
                                            <td>{{ $crew->description }}</td>
                                            <td>{{ $crew->created_at->format('d F, Y') }}</td>
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
                                                                href="{{ route('crew.edit', ['id' => $crew->id, 'return_url' => $return_url]) }}">
                                                                <i class="fa fa-pencil me-2 text-primary"></i>
                                                                Edit
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item text-danger"
                                                                href="{{ route('crew.delete', ['id' => $crew->id]) }}"
                                                                onclick="return confirm('Are you sure you want to delete this crew?')">
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
            <!-- end page title end breadcrumb -->
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
                    searchPlaceholder: "Search crews...",
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
        });
    </script>
    <script>
        function goBack() {
            window.location.href = "/project"; // fallback
        }
        $(document).ready(function(x) {
            window.fs_test = $('.test').fSelect();
        })
    </script>
    <script>
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
    <script>
        $(document).ready(function() {
            var max_fields = 50; //maximum input boxes allowed
            var wrapper = $(".more-labor"); //Fields wrapper
            var add_button = $(".btn-labor"); //Add button ID

            var x = 1; //initlal box count

            $(add_button).click(function(e) { //on add input button click
                e.preventDefault();
                if (x < max_fields) { //max input box allowed
                    x++; //text box increment
                    $(wrapper).append(`@include('components.user.laborfield')`); //add input box
                }
            });

            $(wrapper).on("click", ".remove", function(e) { //user click on remove text
                e.preventDefault();
                $(this).parents(".form-row").remove();
                x--;
            })
        });
    </script>

@endsection()
