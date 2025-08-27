<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
            <img src="{{ asset('admins') }}/assets/img/logo.png" alt="">
            <span class="d-none d-lg-block">Me3Co.com</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
        <form class="search-form d-flex align-items-center" method="POST" action="#">
            <input type="text" name="query" placeholder="Search" title="Enter search keyword">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item d-block d-lg-none">
                <a class="nav-link nav-icon search-bar-toggle " href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li><!-- End Search Icon-->

            <li class="nav-item dropdown">
                @php
                    $notification_number = get_notification_count();
                @endphp
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown" data-read="{{ route('notification.status') }}">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-primary badge-number">{{ $notification_number }}</span>
                </a><!-- End Notification Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                    <li class="dropdown-header">
                        You have {{ $notification_number }} new notifications
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @php
                        $notifications = get_user_notifications();
                    @endphp

                    @foreach ($notifications as $notification)
                        <li class="notification-item">
                            <i class="bi bi-info-circle text-success"></i>
                            <div>
                                <p>{{ $notification->message }}</p>
                                <p>{{ $notification->created_at->format('H:i, d F') }}</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    @endforeach

                </ul><!-- End Notification Dropdown Items -->

            </li><!-- End Notification Nav -->

            <li class="nav-item dropdown">

                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i>
                </a><!-- End Messages Icon -->

                <form action="{{ route('admin.settings.update') }}" id="settings-form"
                    class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">

                    @csrf()
            <li class="dropdown-header" style="width: 240px">
                Account Settings
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <li class="message-item">
                <a href="#" class="justify-content-between">
                    <h4 class="form-check-label">Auto Updates</h4>
                    <div class="form-check form-switch">
                        <input class="form-check-input setting-checks" type="checkbox" name="auto_update"
                            {!! get_user_settings()->auto_update ? 'checked' : '' !!}>
                    </div>
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <li class="message-item">
                <a href="#" class="justify-content-between">
                    <h4 class="form-check-label">Location Permission</h4>
                    <div class="form-check form-switch">
                        <input class="form-check-input setting-checks" type="checkbox" name="location"
                            {!! get_user_settings()->location ? 'checked' : '' !!}>
                    </div>
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li class="dropdown-header" style="width: 240px">
                General Settings
            </li>
            <li class="message-item">
                <a href="#" class="justify-content-between">
                    <h4 class="form-check-label">Show me Online</h4>
                    <div class="form-check form-switch">
                        <input class="form-check-input setting-checks" type="checkbox" name="status"
                            {!! get_user_settings()->status ? 'checked' : '' !!}>
                    </div>
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <li class="message-item">
                <a href="#" class="justify-content-between">
                    <h4 class="form-check-label">Notification Popup</h4>
                    <div class="form-check form-switch">
                        <input class="form-check-input setting-checks" type="checkbox" name="notification"
                            {!! get_user_settings()->notification ? 'checked' : '' !!}>
                    </div>
                </a>
            </li>

            <li class="message-item">
                <a href="{{ route('admin.security.change') }}" class="justify-content-between">
                    <h4 class="form-check-label">Change Credentials</h4>
                </a>
            </li>

            </form><!-- End Messages Dropdown Items -->

            </li><!-- End Messages Nav -->

            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ get_user_details()->name }}</span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ get_user_details()->name }}</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    {{-- <li>
                        <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                            <i class="bi bi-gear"></i>
                            <span>Account Settings</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                            <i class="bi bi-question-circle"></i>
                            <span>Need Help?</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li> --}}

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.logout') }}">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header>
