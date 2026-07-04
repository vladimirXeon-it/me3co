<header class="me3co-topbar">
    <div class="me3co-topbar-left">
        <button type="button" class="me3co-menu-toggle" id="sidebarToggle">
            <i class="fa fa-bars"></i>
        </button>

        <a href="{{ route('project') }}" class="me3co-topbar-logo">
            <img src="{{ asset('projects') }}/images/logo-image.svg">
        </a>
    </div>

    <div class="me3co-topbar-actions">

        <div class="dropdown">
            <a class="me3co-topbar-icon dropdown-toggle" data-bs-toggle="dropdown" href="#">
                <img src="{{ asset('projects') }}/images/folder-open.svg">
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                @php
                    $user_projects = get_user_projects();
                @endphp

                @foreach ($user_projects as $user_project)
                    <li>
                        <a class="dropdown-item"
                           href="{{ env('APP_URL').'/'.$user_project->id.'/application' }}">
                            {{ $user_project->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="javascript:void(0)" class="me3co-topbar-icon">
            <i class="fa fa-bell-o"></i>
        </a>

        <div class="dropdown">
            <a class="dropdown-toggle me3co-user-menu" data-bs-toggle="dropdown" href="#">
                <i class="fa fa-user-circle-o"></i>
                <span>{{ Auth::user()->company }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="javascript:void(0)">Profile</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)">Setting</a></li>
                <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
            </ul>
        </div>

    </div>
</header>

<aside class="me3co-sidebar" id="me3coSidebar">
    <nav class="me3co-sidebar-nav">
        @php
            $currentRoute = Route::currentRouteName();
        @endphp

        <a href="{{ route('project') }}" class="{{ Str::startsWith($currentRoute, 'project') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/project-icon-image.svg" alt="Projects">
            <span>Projects</span>
        </a>

        <a href="{{ route('labor') }}" class="{{ Str::startsWith($currentRoute, 'labor') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/labor-image-icon.svg" alt="Labor">
            <span>Labors</span>
        </a>

        <a href="{{ route('crew') }}" class="{{ Str::startsWith($currentRoute, 'crew') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/crew-icon-image.svg" alt="Crews">
            <span>Crews</span>
        </a>

        <a href="{{ route('equipment') }}" class="{{ Str::startsWith($currentRoute, 'equipment') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/equipment-icon-image.svg" alt="Equipment">
            <span>Equipment's</span>
        </a>

        <a href="{{ route('material') }}" class="{{ Str::startsWith($currentRoute, 'material') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/materials-icon-image.svg" alt="Materials">
            <span>Materials</span>
        </a>

        <a href="{{ route('contact') }}" class="{{ Str::startsWith($currentRoute, 'contact') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/contacts-icon-image.svg" alt="Contacts">
            <span>Contacts</span>
        </a>

        <a href="{{ route('opening') }}" class="{{ Str::startsWith($currentRoute, 'opening') ? 'active' : '' }}">
            <img src="{{ asset('projects') }}/images/openings-icon-image.svg" alt="Openings">
            <span>Openings</span>
        </a>
    </nav>

    <div class="me3co-sidebar-help">
        <i class="fa fa-headphones"></i>
        <h6>Need help?</h6>
        <p>We're here to help you succeed.</p>
        <a href="javascript:void(0)">
            Contact Support
            <i class="fa fa-arrow-right"></i>
        </a>
    </div>
</aside>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('me3coSidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
            });
        }
    });
</script>