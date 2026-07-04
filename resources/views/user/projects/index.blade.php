@extends('user.layouts.app')
@section('title', 'Projects')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            
            <div class="project-page-header flex-shrink-0 mb-2">
                <div>
                    <h1 class="fw-bold m-0" style="font-size: 24px; color: #0f172a;">Projects</h1>
                    <p class="text-muted m-0" style="font-size: 13px;">Manage all your construction takeoffs and estimates.</p>
                </div>
                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <button type="button" class="btn btn-primary fw-bold d-flex align-items-center gap-2 px-3 py-2" 
                                data-bs-toggle="modal" data-bs-target="#create-project" style="border-radius: 6px; background-color: #0052ff; border: none; font-size: 13px;">
                            <i class="fa fa-plus"></i> Create New Project
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-1 flex-shrink-0">
                <div class="col-6 col-sm-6 col-md-3">
                    <div class="project-card-box m-0 shadow-sm d-flex flex-row align-items-center gap-2 p-2" style="border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; height: 100%;">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 36px; height: 36px; border-radius: 8px; font-size: 16px; flex-shrink: 0;">
                            <i class="fa fa-folder-open-o" style="color: #0052ff;"></i>
                        </div>
                        <div class="project-card-content overflow-hidden">
                            <span class="text-muted d-block small fw-medium" style="font-size: 11px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Projects</span>
                            <span class="fw-bold text-dark d-block lh-1 my-1" style="font-size: 16px;">{{ count($projects) + count($inviteProjects) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                    <div class="project-card-box m-0 shadow-sm d-flex flex-row align-items-center gap-2 p-2" style="border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; height: 100%;">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 36px; height: 36px; border-radius: 8px; font-size: 16px; flex-shrink: 0;">
                            <i class="fa fa-users" style="color: #0052ff;"></i>
                        </div>
                        <div class="project-card-content overflow-hidden">
                            <span class="text-muted d-block small fw-medium" style="font-size: 11px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Crews</span>
                            <span class="fw-bold text-dark d-block lh-1 my-1" style="font-size: 16px;">1</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                    <div class="project-card-box m-0 shadow-sm d-flex flex-row align-items-center gap-2 p-2" style="border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; height: 100%;">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 36px; height: 36px; border-radius: 8px; font-size: 16px; flex-shrink: 0;">
                            <i class="fa fa-cube" style="color: #0052ff;"></i>
                        </div>
                        <div class="project-card-content overflow-hidden">
                            <span class="text-muted d-block small fw-medium" style="font-size: 11px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Materials</span>
                            <span class="fw-bold text-dark d-block lh-1 my-1" style="font-size: 16px;">72</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                    <div class="project-card-box m-0 shadow-sm d-flex flex-row align-items-center gap-2 p-2" style="border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; height: 100%;">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 36px; height: 36px; border-radius: 8px; font-size: 16px; flex-shrink: 0;">
                            <i class="fa fa-wrench" style="color: #0052ff;"></i>
                        </div>
                        <div class="project-card-content overflow-hidden">
                            <span class="text-muted d-block small fw-medium" style="font-size: 11px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">Equipment's</span>
                            <span class="fw-bold text-dark d-block lh-1 my-1" style="font-size: 16px;">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card me3co-list-card border-0 shadow-sm d-flex flex-column mb-2 p-3"
                style="border-radius: 12px; background-color: #ffffff; min-height: 0;">
                
                <div class="table-responsive me3co-table-wrapper">
                    <table class="table table-hover align-middle mb-0" id="projectTableId">
                        <thead >
                            <tr>
                                <th scope="col" style="width: 40px; background-color: #ffffff; font-size: 13px;">#</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Project Name</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Bid Date</th>
                                <th scope="col" style="background-color: #ffffff; font-size: 13px;">Created At</th>
                                <th scope="col" class="text-center" style="background-color: #ffffff; font-size: 13px;">Crews</th>
                                <th scope="col" class="text-center" style="background-color: #ffffff; font-size: 13px;">Equipment's</th>
                                <th scope="col" class="text-center" style="background-color: #ffffff; font-size: 13px;">Materials</th>
                                <th scope="col" class="text-end pe-3" style="background-color: #ffffff; font-size: 13px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNum = 1; @endphp
                            
                            @foreach ($inviteProjects as $project)
                                <tr>
                                    <td class="fw-bold text-muted ps-2">{{ $rowNum++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="width: 32px; height: 32px; border-radius: 6px; flex-shrink: 0;">
                                                <i class="fa fa-building-o" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block" style="font-size: 13px;">{{ $project->name }}</span>
                                                <span class="text-muted" style="font-size: 11px;">Created {{ $project->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    @php $bid_date = strtotime($project->bid_date); @endphp
                                    <td class="text-dark fw-medium" style="font-size: 13px;">{{ date('m/d/Y', $bid_date) }}</td>
                                    <td class="text-muted" style="font-size: 13px;">{{ $project->created_at->format('M d, Y') }}</td>
                                    
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/crew/'.$project->id }}" class="badge-count-link bg-crews">
                                            <i class="fa fa-user-o me-1"></i> 0
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/equipment/'.$project->id }}" class="badge-count-link bg-equipments">
                                            <i class="fa fa-wrench me-1"></i> 0
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/material/'.$project->id }}" class="badge-count-link bg-materials">
                                            <i class="fa fa-cube me-1"></i> 0
                                        </a>
                                    </td>
                                    
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <a class="btn btn-primary btn-sm fw-bold px-2 btn-takeoff" href="{{ env('APP_URL') . '/' . $project->id . '/application' }}" style="font-size: 12px;">
                                                Start Takeoff
                                            </a>
                                            <button class="btn btn-light btn-sm border fw-medium px-2 text-dark btn-invite" data-bs-toggle="modal" data-bs-target="#inviteProjectModal" id="inviteProjectId" value="{{ $project->id }}" style="font-size: 12px;">
                                                Invite
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @foreach ($projects as $project)
                                <tr>
                                    <td class="fw-bold text-muted ps-2">{{ $rowNum++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="width: 32px; height: 32px; border-radius: 6px; flex-shrink: 0;">
                                                <i class="fa fa-building-o" style="font-size: 14px;"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block" style="font-size: 13px;">{{ $project->name }}</span>
                                                <span class="text-muted" style="font-size: 11px;">Created {{ $project->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    @php $bid_date = strtotime($project->bid_date); @endphp
                                    <td class="text-dark fw-medium" style="font-size: 13px;">{{ date('m/d/Y', $bid_date) }}</td>
                                    <td class="text-muted" style="font-size: 13px;">{{ $project->created_at->format('M d, Y') }}</td>
                                    
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/crew/'.$project->id }}" class="badge-count-link">
                                            <i class="fa fa-user-o me-1"></i> {{ $project->crews()->count() }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/equipment/'.$project->id }}" class="badge-count-link">
                                            <i class="fa fa-wrench me-1"></i> {{ $project->equipments()->count() }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ env('APP_URL').'/material/'.$project->id }}" class="badge-count-link">
                                            <i class="fa fa-cube me-1"></i> {{ $project->materials()->count() }}
                                        </a>
                                    </td>
                                    
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <a class="btn btn-primary btn-sm fw-bold px-2 btn-takeoff" href="{{ env('APP_URL').'/'.$project->id.'/application' }}" style="font-size: 12px;">
                                                Start Takeoff
                                            </a>
                                            <a class="btn btn-light btn-sm border fw-medium px-2 text-dark btn-invite" href="{{ route('invite-project-user', ['id' => $project->id ]) }}" style="font-size: 12px;">
                                                Invite
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm border text-muted px-2" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <button type="button"
                                                            class="dropdown-item editProjectId"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editProjectModal"
                                                            value="{{ $project->id }}">
                                                            <i class="fa fa-pencil me-2 text-primary"></i>
                                                            Edit
                                                        </button>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            href="{{ route('project.delete', ['id' => $project->id]) }}"
                                                            onclick="return confirm('Are you sure you want to delete this project?')">
                                                            <i class="fa fa-trash me-2"></i>
                                                            Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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
    <!-- ========================================================== -->
    <!-- MODAL: CREATE PROJECT (Estilos Modernizados)               -->
    <!-- ========================================================== -->
    <div class="modal fade" id="create-project" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff;">
                <div class="modal-header border-bottom px-4 py-3" style="border-color: #edf2f7;">
                    <h5 class="modal-title fw-bold text-dark" id="createProjectTitle" style="font-size: 16px; color: #0f172a;">Create New Project</h5>
                    <button type="button" class="btn-close style-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
                </div>
                <form method="post" action="{{ route('project.create-new-project') }}">
                    @csrf()
                    <div class="modal-body px-4 py-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Project Name *</label>
                                <input type="text" class="form-control" id="email" name="name" required placeholder="e.g. Oakridge Commercial Takeoff" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Bid Date *</label>
                                <input type="date" class="form-control" id="bid_date" name="bid_date" required style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Tax (%)</label>
                                <input type="number" step="0.01" class="form-control" id="tax" name="tax" placeholder="0" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">OH (%)</label>
                                <input type="number" step="0.01" class="form-control" id="oh" name="oh" placeholder="0" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Profit (%)</label>
                                <input type="number" step="0.01" class="form-control" id="profit" name="profit" placeholder="0" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 justify-content-end gap-2" style="border-color: #edf2f7; background-color: #f8f9fa; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-light border fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 6px; font-size: 13px;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 6px; background-color: #0052ff; border: none; font-size: 13px;">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- MODAL: EDIT PROJECT (Estilos Modernizados)                 -->
    <!-- ========================================================== -->
    <div class="modal fade" id="editProjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff;">
                <div class="modal-header border-bottom px-4 py-3" style="border-color: #edf2f7;">
                    <h5 class="modal-title fw-bold text-dark" id="editProjectTitle" style="font-size: 16px; color: #0f172a;">Edit Project Details</h5>
                    <button type="button" class="btn-close style-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
                </div>
                <form method="post" action="{{ route('project.update-project') }}">
                    @csrf()
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" id="project_id" name="project_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Project Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Bid Date *</label>
                                <input type="date" class="form-control" id="bidDate" name="bid_date" required style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Tax (%)</label>
                                <input type="number" step="0.01" class="form-control" id="tax_edit" name="tax" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">OH (%)</label>
                                <input type="number" step="0.01" class="form-control" id="oh_edit" name="oh" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Profit (%)</label>
                                <input type="number" step="0.01" class="form-control" id="profit_edit" name="profit" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 justify-content-end gap-2" style="border-color: #edf2f7; background-color: #f8f9fa; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-light border fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 6px; font-size: 13px;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 6px; background-color: #0052ff; border: none; font-size: 13px;">Update Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- MODAL: INVITE USER (Estilos Modernizados)                  -->
    <!-- ========================================================== -->
    <div class="modal fade" id="inviteProjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="inviteProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: #ffffff;">
                <div class="modal-header border-bottom px-4 py-3" style="border-color: #edf2f7;">
                    <h5 class="modal-title fw-bold text-dark" id="inviteProjectTitle" style="font-size: 16px; color: #0f172a;">Invite Team Member</h5>
                    <button type="button" class="btn-close style-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
                </div>
                <form method="post" action="{{ route('invite') }}">
                    @csrf()
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" id="invite_project_id" name="project_id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 12px;">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="collaborator@company.com" style="border-radius: 6px; font-size: 13px; padding: 8px 12px; border: 1px solid #e2e8f0;">
                        </div>
                        <div class="p-3" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px;">
                            <p class="m-0 text-amber-800" style="font-size: 12px; color: #92400e; line-height: 1.5;">
                                <i class="fa fa-exclamation-triangle me-1"></i> <span class="fw-bold">Important Note:</span>
                                Invited users will gain full access to view, update, and modify parameters inside this project scope exclusively.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 justify-content-end gap-2" style="border-color: #edf2f7; background-color: #f8f9fa; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-light border fw-semibold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 6px; font-size: 13px;">Close</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 6px; background-color: #0052ff; border: none; font-size: 13px;">Send Invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection()

@section('script')
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
    <script>
        $(document).ready(function() {
            // Inicialización limpia de DataTables vinculada a tu estilo
            $('#projectTableId').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],

                scrollY: 'calc(100vh - 360px)',
                scrollCollapse: false,
                scrollX: false,

                language: {
                    search: "",
                    searchPlaceholder: "Search projects...",
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
            
            // Estilizar el input de búsqueda nativo para que encaje perfectamente
            $('.dataTables_filter label').contents().filter(function(){
                return this.nodeType === 3;
            }).remove();

            $('#projectTableId_wrapper .dataTables_scrollBody').css({
                'max-height': 'calc(100vh - 360px)',
                'overflow-y': 'auto'
            });
        });
    </script>
    <script>
        $(document).on('click', '.editProjectId', function() {
            let project_id = $(this).attr('value');

            console.log('edit id:', project_id);

            $.ajax({
                type: "GET",
                url: "{{ url('/project/edit-new-project') }}/" + project_id,
                dataType: "json",
                success: function(response) {
                    console.log(response);

                    $('#project_id').val(response.project.id);
                    $('#name').val(response.project.name);
                    $('#bidDate').val(response.project.bid_date);
                    $('#tax_edit').val(response.project.tax ?? 0);
                    $('#oh_edit').val(response.project.oh ?? 0);
                    $('#profit_edit').val(response.project.profit ?? 0);
                },
                error: function(xhr) {
                    console.log('Error edit project:', xhr.responseText);
                }
            });
        });
    </script>
@endsection()
