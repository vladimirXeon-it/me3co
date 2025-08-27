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
             <div class="row mt-3">
                <div class="col-10">
                    <div class="w-100 d-inline-block">
                        <h2 class="text-black fs-4 fw-bold">My Crews @if ($project!=null)
                            for {{$project->name}}
                         @endif</h2>
                    </div>
                </div>
                <div class="col-2">
                    <div class="float-end d-inline-block">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                            <li class="breadcrumb-item active">Crews</li>
                        </ol>
                    </div>
                    <div class="float-end d-inline-block creat-project-btn">
                        <a href="{{ route('crew.create') }}" 
                            class="text-14 fw-bold d-inline-block btn-create-project"
                        >
                            <img src="{{ asset('projects') }}/images/plus-icon.svg">
                            Create Crew
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
                                        <td>
                                            <a href="{{ route('crew.edit', ['id' => $crew->id]) }}">
                                                <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                            </a> 
                                            <a href="{{ route('crew.delete', ['id' => $crew->id]) }}">
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
                "paging": true,  // Enable pagination
                "searching": true,  // Enable search
                // You can customize further options here
            });
        });
    </script>
    <script>
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
