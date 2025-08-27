@extends('user.layouts.app')

@section('title', 'Equipments')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">Equipment</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Equipment</li>
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
                        <h2 class="text-black fs-4 fw-bold">My Equipment for @if ($project!=null)
                            for {{$project->name}}
                         @endif</h2>
                    </div>
                </div>
                <div class="col-2">
                    <div class="float-end d-inline-block">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                            <li class="breadcrumb-item active">Equipment</li>
                        </ol>
                    </div>
                    <div class="float-end d-inline-block creat-project-btn">
                        <a 
                            href="{{ route('equipment.create') }}" 
                            class="text-14 fw-bold d-inline-block btn-create-project"
                        >
                        <img src="{{ asset('projects') }}/images/plus-icon.svg">
                            Create Equipment
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
                                <th>Equipment Name</th>
                                <th>Equipment Id</th>
                                <th>Description</th>
                                <th>Cost per day</th>
                                <th>Action</th>
                            </tr>
                            <!--end tr-->
                        </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($equipments as $equipment)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $equipment->name }}</td>
                                        <td>{{ $equipment->unique_id }}</td>
                                        <td>{{ $equipment->description }}</td>
                                        <td>{{ $equipment->cost_per_day }}$</td>
                                        <td>
                                            <a href="{{ route('equipment.edit', ['id' => $equipment->id]) }}">
                                                <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                            </a> 
                                            <a
                                                href="{{ route('equipment.delete', ['id' => $equipment->id]) }}">
                                                <img class="edit-icon" src="{{ asset('projects') }}/images/delete-icon.svg">
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach()
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        </div><!-- container -->
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
@endsection()
