<div class="topbar" id="topbar">
    <!-- LOGO -->
    <div class="brand">
        <a href="" class="logo">
            <span>
                <img src="{{ asset('fronts') }}/assets/images/logo.png" alt="logo-large" class="logo-lg logo-light">
            </span>
        </a>
    </div>
    <!--end logo-->
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">
            <li class="notification-list dropdown">
                <a class="nav-link arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i class="ti ti-package"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-header">Actions</span>
                    <a class="dropdown-item project-toggle btn-close-project" href="#"><i
                            class="fa fa-times font-16 me-1 align-text-bottom"></i>
                        Close
                    </a>
                    <span class="dropdown-header">Projects</span>
                    @php
                        $user_projects = get_user_projects();
                    @endphp
                    @foreach ($user_projects as $user_project)
                         <a class="dropdown-item project-toggle btn-open-project" 
                            href="{{env('APP_URL').'/'.$project->id.'/application' }}" 
                        >
                            <i class="ti ti-package font-16 me-1 align-text-bottom"></i>
                            {{ $user_project->name }}
                        </a>
                        {{-- <a class="dropdown-item project-toggle btn-open-project" href="#"
                            data-href="{{ route('project.measurement', ['id' => $user_project->id]) }}"><i
                                class="ti ti-package font-16 me-1 align-text-bottom"></i>
                            {{ $user_project->name }}
                        </a> --}}
                    @endforeach

                </div>
            </li>
            <li class="notification-list">
                <a class="nav-link arrow-none nav-icon offcanvas-btn" href="#" data-bs-toggle="offcanvas"
                    data-bs-target="#Appearance" role="button" aria-controls="Rightbar">
                    <i class="ti ti-settings ti-spin"></i>
                </a>
            </li>



            <li class="dropdown">
                <a class="nav-link dropdown-toggle nav-user" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('fronts') }}/assets/images/users/default.png" alt="profile-user"
                            class="rounded-circle me-0 me-md-2 thumb-sm" />
                        <div class="user-name">
                            <small class="d-none d-lg-block font-11">{{ Auth::user()->company }}</small>
                            <span
                                class="d-none d-lg-block fw-semibold font-12">{{ explode(' ', Auth::user()->name)[0] }}<i
                                    class="mdi mdi-chevron-down"></i></span>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    

                    <a class="dropdown-item" href="#"><i class="ti ti-user font-16 me-1 align-text-bottom"></i>
                        Profile</a>

                    <a class="dropdown-item" href="{{ route('company') }}">
                        <i class="ti ti-building font-16 me-1 align-text-bottom"></i>Company
                    </a>

                    {{-- <a class="dropdown-item" href="#"><i
                            class="ti ti-settings font-16 me-1 align-text-bottom"></i> Settings</a>
                    <div class="dropdown-divider mb-0"></div> --}}
                    <a class="dropdown-item" href="{{ route('logout') }}"><i
                            class="ti ti-power font-16 me-1 align-text-bottom"></i> Logout</a>
                </div>
            </li>
            <!--end topbar-profile-->
            <li class="menu-item">
                <!-- Mobile menu toggle-->
                <a class="navbar-toggle nav-link" id="mobileToggle" onclick="toggleMenu()" onclick="toggleMenu()">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a><!-- End mobile menu toggle-->
            </li>
            <!--end menu item-->
        </ul>
        <!--end topbar-nav-->

        <div class="navbar-custom-menu">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('project') }}">
                            <span><i class="ti ti-smart-home menu-icon"></i>Projects</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('labor') }}">
                            <span><i class="ti ti-apps menu-icon"></i>Labor</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('crew') }}">
                            <span><i class="ti ti-users menu-icon"></i>Crew</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('equipment') }}">
                            <span><i class="ti ti-planet menu-icon"></i>Equipment</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('material') }}">
                            <span><i class="ti ti-file-diff menu-icon"></i>Materials</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    {{-- <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('assembly') }}">
                            <span><i class="ti ti-shield-lock menu-icon"></i>Assemblies</span>
                        </a>
                    </li> --}}
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('contact') }}">
                            <span><i class="ti ti-phone menu-icon"></i>Contacts</span>
                        </a>
                    </li>
                    <!--end nav-item-->
                    {{-- <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('company') }}">
                            <span><i class="ti ti-building menu-icon"></i>Company</span>
                        </a>
                    </li> --}}
                    <!--end nav-item-->
                    <li class="nav-item parent-menu-item">
                        <a class="nav-link" href="{{ route('opening') }}">
                            <span><i class="ti ti-lock menu-icon"></i>Openings</span>
                        </a>
                    </li>
                    <!--end nav-item-->

                </ul><!-- End navigation menu -->
            </div> <!-- end navigation -->
        </div>
        <!-- Navbar -->
    </nav>
    <!-- end navbar-->
</div>
