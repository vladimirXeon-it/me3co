 <header class="header-bg w-100 d-inline-block">
    <div class="container-fluid">
      <div class="row">
          <div class="col-xl-1 col-5 col-sm-5 col-md-5 col-lg-5 pe-xl-0">
            <a class="logo w-100 d-inline-block" href="{{ route('project') }}" id="menu-button">
                <img src="{{ asset('projects') }}/images/logo-image.svg">
            </a>
              {{-- <div class="logo w-100 d-inline-block">
                  <img src="{{ asset('projects') }}/images/logo-image.svg">
              </div> --}}
          </div>
          <div class="col-xl-11 col-7 col-sm-7 col-md-7 col-lg-7 
          ps-xl-0">
            <div class="menu-side float-end d-xl-none d-block mt-xl-3 mt-2">
                <a href="javascript:void(0)" id="menu-button">
                   <img src="{{ asset('projects') }}/images/menu-btn.svg">
                </a>
            </div>
            <div id="sidebar">
                <span class="close-text d-xl-none d-block" 
                    id="close-button">&times;</span>
                  <div class="float-start d-inline-block" 
                  id="myDiv">
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('project') }}">
                            <img src="{{ asset('projects') }}/images/project-icon-image.svg">
                              Projects
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('labor') }}">
                            <img src="{{ asset('projects') }}/images/labor-image-icon.svg">
                              Labors
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('crew') }}">
                            <img src="{{ asset('projects') }}/images/crew-icon-image.svg">
                              Crews
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('equipment') }}">
                            <img src="{{ asset('projects') }}/images/equipment-icon-image.svg">
                              Equipment's
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('material') }}">
                            <img src="{{ asset('projects') }}/images/materials-icon-image.svg">
                              Materials
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('contact') }}">
                            <img src="{{ asset('projects') }}/images/contacts-icon-image.svg">
                              Contacts
                          </a>
                      </div>
                      <div class="float-start w-xs-100 w-sm-100 w-md-100 w-lg-100 mb-xl-0 mb-3 project-text">
                          <a class="text-white text-14 fw-bold d-inline-block" href="{{ route('opening') }}">
                            <img src="{{ asset('projects') }}/images/openings-icon-image.svg">
                              Openings
                          </a>
                      </div>
                  </div>
                  <div class="float-end d-inline-block ms-xl-0 
                  ms-xl-4 w-md-100">
                       {{-- <a class="text-13 login-btn me-xl-3 mb-xl-0 mb-3 d-inline-block folder-icon" data-bs-toggle="dropdown" 
                            href="#" role="button" aria-haspopup="false" aria-expanded="false">
                       <span>
                        <img src="{{ asset('projects') }}/images/folder-open.svg">
                        </span>
                        </a> --}}
                         {{-- <a class="nav-link arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <i class="ti ti-package"></i>
                        </a> --}}
                        {{-- <div class="dropdown-menu dropdown-menu-end">
                            <span class="dropdown-header">Actions</span>
                            <a class="dropdown-item project-toggle btn-close-project" href="#"><i
                                    class="fa fa-times font-16 me-1 align-text-bottom"></i>
                                Close
                            </a>
                            <span class="dropdown-header">Projects</span>
                            @php
                                $user_projects = get_user_projects();
                            @endphp
                            {{$user_projects}}
                            @foreach ($user_projects as $user_project)
                                <a class="dropdown-item project-toggle btn-open-project" 
                                    href="{{env('APP_URL').'/'.$user_project->id.'/application' }}" 
                                >
                                    <i class="ti ti-package font-16 me-1 align-text-bottom"></i>
                                    {{ $user_project->name }}
                                </a>
                            @endforeach

                        </div> --}}
                        {{-- <a class="text-13 login-btn me-xl-3 mb-xl-0 mb-3 d-inline-block folder-icon" href="javascript:void(0)">
                       <span>
                        <img src="{{ asset('projects') }}/images/setting--icon.svg">
                        </span>
                        </a> --}}
                        <div class="dropdown float-end mt-3">
                        <a class="dropdown-toggle text-white text-14" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle-o fs-3 me-2 float-start" aria-hidden="true"></i>
                            {{ Auth::user()->company }}
                          </a>
                          <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)">Profile</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)">Setting</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
                            </li>
                          </ul>
                        </div>
                        <div class="dropdown float-end mt-3 me-3">
                          <a class="dropdown-toggle text-white text-14" 
                            type="button" data-bs-toggle="dropdown" 
                            aria-expanded="false"
                             data-bs-toggle="tooltip" 
                             data-bs-placement="top" 
                             title="Projects"
                          >
                             <img src="{{ asset('projects') }}/images/folder-open.svg">
                          </a>
                          <ul class="dropdown-menu">
                            @php
                                $user_projects = get_user_projects();
                            @endphp
                            @foreach ($user_projects as $user_project)
                            <li>
                                <a 
                                    class="dropdown-item" 
                                    href="{{env('APP_URL').'/'.$user_project->id.'/application' }}"
                                >
                                    {{ $user_project->name }}
                                </a>
                            </li>
                            @endforeach
                          </ul>
                        </div>
                  </div>
              </div>
          </div>
        </div>   
    </div>
</header>
