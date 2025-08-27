<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.index') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.equipments') }}">
                <i class="bi bi-tools"></i>
                <span>Equipments</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.crews') }}">
                <i class="bi bi-cpu"></i>
                <span>Crews</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.contacts') }}">
                <i class="bi bi-cpu"></i>
                <span>Contacts</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.company') }}">
                <i class="bi bi-building"></i>
                <span>Company</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#opening-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-square"></i><span>Openings</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="opening-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.opening.shape') }}">
                        <i class="bi bi-circle"></i><span>Opening Shapes</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.opening') }}">
                        <i class="bi bi-circle"></i>
                        <span>Openings</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#labors-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-hypnotize"></i><span>Labors</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="labors-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.labor.class') }}">
                        <i class="bi bi-circle"></i><span>Labor Class</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.labor') }}">
                        <i class="bi bi-circle"></i><span>Labor Type</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#material-nav" data-bs-toggle="collapse" href="#">
                <i class="bi-menu-button-wide-fill"></i><span>Materials</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="material-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.material.division') }}">
                        <i class="bi bi-circle"></i>
                        <span>Material Division</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.material.class') }}">
                        <i class="bi bi-circle"></i>
                        <span>Material Class</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.material.units') }}">
                        <i class="bi bi-circle"></i>
                        <span>Default Units</span>
                    </a>
                </li>

                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.material') }}">
                        <i class="bi bi-circle"></i><span>Material</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#page-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-file-code-fill"></i><span>Pages</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="page-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a class="nav-link collapsed" href="{{ route('admin.static.add') }}">
                        <i class="bi bi-circle"></i>
                        <span>Add Page</span>
                    </a>
                </li>
                @foreach (static_pages() as $page)
                    <li>
                        <a class="nav-link collapsed" href="{{ route('admin.static.edit', ['id' => $page->id]) }}">
                            <i class="bi bi-circle"></i>
                            <span>{{ $page->title }}</span>
                        </a>
                    </li>
                @endforeach

            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.users') }}">
                <i class="bi bi-people-fill"></i>
                <span>User Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('admin.subscriptions') }}">
                <i class="bi bi-check"></i>
                <span>Subscriptions</span>
            </a>
        </li>

    </ul>

</aside>
