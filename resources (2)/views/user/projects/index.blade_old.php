@extends('user.layouts.app')
@section('title', 'Projects')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">My Project</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Project</li>
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
            <!--end row-->
            <!-- Button trigger modal -->
               

                <!--Save Project Modal -->
                <div class="modal fade" id="create-project" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-16 text-black fw-bold" id="staticBackdropLabel">Create New Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('project.create-new-project') }}">
                     @csrf()
                    <div class="modal-body">
                            <div class="mb-2 input-project">
                                <label class="form-label text-14">Name</label>
                                <input type="text" class="form-control" id="email" name="name">
                            </div>
                            <div class="mb-2 input-project">
                                <label class="form-label text-14">Bid Date</label>
                                <input type="date" class="form-control" id="bid_date" name="bid_date">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn save-btn text-white">Save</button>
                    </div>
                    </form>
                    </div>
                </div>
                </div>
                <!--Edit Project Modal -->
                <div class="modal fade" id="editProjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-black fw-bold" id="staticBackdropLabel">Edit Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('project.update-project') }}">
                     @csrf()
                    <div class="modal-body">
                    <input type="hidden" id="project_id" name="project_id">
                            <div class="mb-2 input-project">
                                <label class="form-label text-14">Name</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="mb-2 input-project">
                                <label class="form-label text-14">Bid Date</label>
                                 <input type="date" class="form-control" id="bidDate" name="bid_date">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn save-btn text-white">Update</button>
                    </div>
                    </form>
                    </div>
                </div>
                </div>
            <!-- end page title end breadcrumb -->

            <div class="container-fluid">
    <div class="row mt-3">
        <div class="col-10">
            <div class="w-100 d-inline-block">
                <h2 class="text-black fs-4 fw-bold">My Project</h2>
            </div>
        </div>
        <div class="col-2">
            <div class="float-end d-inline-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                    <li class="breadcrumb-item active">Project</li>
                </ol>
            </div>
            <div class="float-end d-inline-block creat-project-btn">
                 <button type="button" class="text-14 fw-bold d-inline-block btn-create-project" data-bs-toggle="modal" data-bs-target="#create-project">
                    <img src="{{ asset('projects') }}/images/plus-icon.svg">
                    Create New Project
                </button>
                    {{-- <a class="text-14 fw-bold d-inline-block" 
                    href="javascript:void(0)">
                    <img src="{{ asset('projects') }}/images/plus-icon.svg">
                        Create New Project
                    </a> --}}
                </div>
        </div>
    </div>
    
        <div class="row">
            <div class="col-12">
                <div class="w-100 d-inline-block project-table mt-4">
                    <table class="table table-striped" id="projectTableId">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Project Name</th>
                          <th scope="col">Bid Date</th>
                          <th scope="col">Created At</th>
                          <th scope="col">Action</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                      @foreach ($projects as $key => $project)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $project->name }}</td>
                            @php
                                $bid_date = strtotime($project->bid_date);
                            @endphp
                            <td>{{ date('m/d/Y', $bid_date) }}</td>
                            <td>{{ $project->created_at->format('d F, Y') }}</td>
                            <td>
                                <button type="button" id="editProjectId"  data-bs-toggle="modal" 
                                    data-bs-target="#editProjectModal" value="{{ $project->id }}">
                                    <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                </button>
                            {{-- <a  
                                href="{{ route('project.edit', ['id' => $project->id]) }}"
                                class="btn btn-primary btn-sm"><i class="fa fa-edit"></i>
                            </a> --}}
                                <a href="{{ route('project.delete', ['id' => $project->id]) }}">
                                    <img class="edit-icon" src="{{ asset('projects') }}/images/delete-icon.svg">
                                </a>
                            {{-- <a
                                href="{{ route('project.delete', ['id' => $project->id]) }}"
                                class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>
                            </a> --}}
                            </td>
                            <td>
                            <a class="btn-teke-off text-black text-14 d-inline-block" 
                                href="{{env('APP_URL').'/'.$project->id.'/catalogs' }}">
                                <img src="{{ asset('projects') }}/images/setting--icon.svg">
                                  Catalogs1xxx1 x
                              </a>
                              <a class="btn-teke-off text-black text-14 d-inline-block" 
                                href="{{env('APP_URL').'/'.$project->id.'/application' }}">
                                <img src="{{ asset('projects') }}/images/tekeoff-icon.svg">
                                  Start Takeoff
                              </a>
                                {{-- <a
                                    href="{{env('APP_URL').'/'.$project->id.'/application' }}"
                                    class="btn btn-success text-nowrap btn-sm"
                                >
                                    Start Takeoff
                                </a> --}}
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
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                "paging": true,  // Enable pagination
                "searching": true,  // Enable search
                // You can customize further options here
            });

        });
    </script>
<script>
$(document).ready(function () {
        $(document).on('click', '#editProjectId', function() {
            var project_id = $(this).val();
            console.log('edit id', project_id)

                $.ajax({
                type: "GET",
                url: "/project/edit-new-project/" + project_id,
                success: function(response) {
                    $('#name').val(response.project.name);
                    $('#bidDate').val(response.project.bid_date);
                    $('#project_id').val(project_id);
                }
            });
        });
    });
</script>
    <script>
        $('#create-new').click(function() {
            $('.new-project').show();
            $('.my-project').hide();
        });
        $('#back').click(function() {
            $('.new-project').hide();
            $('.my-project').show();
        });
    </script>
    <script>
        $("#n-next").click(function() {
            $('#myTab li:nth-child(2) a').tab('show');
        });

        $("#p-prev").click(function() {
            $('#myTab li:nth-child(1) a').tab('show');
        });
    </script>
    <script>
        function openCity(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    <script>
        let i = 1;
        $('.item-add').on('click', function() {
            let content = `@include('components.user.item')`
            $('.item-field').append(content)
            i++;
        });
        $('.item-field').on('click', '.item-remove', function() {
            $(this).parents('.form-group.row').remove()
            i--;
        })
    </script>
@endsection()
