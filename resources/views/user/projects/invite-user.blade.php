@extends('user.layouts.app')
@section('title', 'Projects')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!--Invite Project Modal -->
            <div class="modal fade" id="inviteProjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-black fw-bold" id="staticBackdropLabel">Invite User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form method="post" action="{{ route('invite') }}">
                            @csrf()
                            <div class="modal-body">
                                <input type="hidden" id="invite_project_id" value="{{$projectId}}" name="project_id">
                                <div class="mb-2 input-project">
                                    <label class="form-label text-14">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                <p>
                                    <span class="text-danger">Note:</span>
                                    Invited user will only see your current project and have full access to this project.
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn save-btn text-white">Invite</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- end invite modal -->
            <!-- end page title end breadcrumb -->

            <div class="container-fluid">
                <div class="row mt-3">
                    <div class="col-10">
                        <div class="w-100 d-inline-block">
                            <h2 class="text-black fs-4 fw-bold">{{$projectName}}</h2>
                        </div>
                    </div>
                    <div class="col-2">
                        {{-- <div class="float-end d-inline-block">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                <li class="breadcrumb-item active">Project</li>
                                <li class="breadcrumb-item active">{{$projectName}}</li>
                            </ol>
                        </div> --}}
                        <div class="float-end d-inline-block creat-project-btn">
                            <button class="btn-teke-off" type="button" id="inviteProjectId" data-bs-toggle="modal"
                                data-bs-target="#inviteProjectModal" value="{{ $projectId }}">
                                Invite New User
                            </button>
                            {{-- <button type="button" class="text-14 fw-bold d-inline-block btn-create-project"
                                data-bs-toggle="modal" data-bs-target="#create-project">
                                <img src="{{ asset('projects') }}/images/plus-icon.svg">
                                Invite New User
                            </button> --}}
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
                                        <th scope="col">User Email</th>
                                        <th scope="col">Accept Status</th>
                                        <th scope="col">Invited at</th>
                                        <th scope="col">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($projectUsers as $key => $project)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $project->invite_to }}</td>
                                            <td>
                                                <span
                                                    class="{{ $project->status == '1' ? 'bg-success text-white rounded-3 accept-btn' : 'bg-warning text-dark accept-btn rounded-3' }}">
                                                    {{ $project->status == '1' ? 'Accepted' : 'Pending' }}
                                                </span>
                                            </td>
                                            <td>{{ $project->created_at->format('d F, Y') }}</td>
                                            <td>
                                                <a href="{{ route('delete-invite-user', ['id' => $project->id]) }}" class="bg-danger text-white accept-btn rounded-3">
                                                    Remove From Project
                                                </a>
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
                        "paging": true, // Enable pagination
                        "searching": true, // Enable search
                        // You can customize further options here
                    });

                });
            </script>
        @endsection()
