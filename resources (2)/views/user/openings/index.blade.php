@extends('user.layouts.app')

@section('title', 'Contacts')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Opening</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Openings</li>
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
            <div class="row mt-3">
                <div class="col-10">
                    <div class="w-100 d-inline-block">
                        <h2 class="text-black fs-4 fw-bold">My Opening</h2>
                    </div>
                </div>
                <div class="col-2">
                    <div class="float-end d-inline-block">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                            <li class="breadcrumb-item active">Opening</li>
                        </ol>
                    </div>
                    <div class="float-end d-inline-block creat-project-btn">
                        <a 
                            href="{{ route('opening.create') }}" 
                            class="text-14 fw-bold d-inline-block btn-create-project"
                        >
                        <img src="{{ asset('projects') }}/images/plus-icon.svg">
                            Create Opening
                        </a>
                    </div>
                </div>
            </div>
            <!--end row-->
            <div class="row">
                <div class="col-12">
                    <div class="w-100 d-inline-block project-table mt-4">
                        <table class="table table-striped" id="projectTableId">
                            <thead class="thead-light">
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
                                        <td>
                                            <a href="{{ route('opening.edit', ['id' => $opening->id]) }}">
                                                <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                            </a> 
                                            <a href="{{ route('opening.delete', ['id' => $opening->id]) }}">
                                                 <img class="edit-icon" src="{{ asset('projects') }}/images/delete-icon.svg">
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end row-->

        </div>
        <!-- container -->
    </div>
@endsection()

@section('script')
    <script>
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                "paging": true,  // Enable pagination
                "searching": true,  // Enable search
                // You can customize further options here
            });

        });
    </script>
@endsection

