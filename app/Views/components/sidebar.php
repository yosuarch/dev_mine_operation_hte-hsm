<!-- ─────────────────────────────────────────────────────────────
     DESKTOP SIDEBAR
     • Always in the flex flow on ≥ lg screens (d-none d-lg-flex).
     • Minimized / maximized state is driven by JS in main_layout.php
       and persisted in localStorage under "mine_ops_sidebar_minimized".
     • data-sidebar-tip — tooltip label shown only when minimized.
──────────────────────────────────────────────────────────────── -->
<aside id="desktopSidebar" class="d-none d-lg-flex flex-column">

    <!-- Primary navigation -->
    <nav class="nav flex-column pt-3 flex-grow-1 sidebar-nav" aria-label="Primary navigation">

        <a class="nav-link" href="/admin-dashboard"
            data-sidebar-tip="Dashboard">
            <i class="fas fa-tachometer-alt"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <a class="nav-link" href="/prestart-insepction"
            data-sidebar-tip="Pre-Start Inspection">
            <i class="fas fa-clipboard-check"></i>
            <span class="sidebar-text">Pre-Start Inspection</span>
        </a>

        <a class="nav-link" href="/manpower"
            data-sidebar-tip="Manpower">
            <i class="fas fa-users"></i>
            <span class="sidebar-text">Manpower</span>
        </a>

    </nav>

    <!-- Footer: logout + collapse button -->
    <div class="sidebar-footer">
        <div class="sidebar-divider"></div>

        <nav class="nav flex-column sidebar-nav" aria-label="Account navigation">
            <a class="nav-link" href="<?= url_to('logout'); ?>"
                data-sidebar-tip="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span class="sidebar-text">Logout</span>
            </a>
        </nav>

        <div class="sidebar-divider"></div>

        <button id="sidebarInternalToggle" title="" data-sidebar-tip="Expand sidebar">
            <i class="fas fa-chevron-left" id="sidebarChevronIcon"></i>
            <span class="sidebar-text">Collapse</span>
        </button>
    </div>

</aside>


<!-- ─────────────────────────────────────────────────────────────
     MOBILE OFFCANVAS DRAWER
     • position: fixed — has zero impact on desktop flex layout.
     • Opened by the navbar hamburger button (via JS in main_layout).
     • Bootstrap's own close button handles dismissal.
──────────────────────────────────────────────────────────────── -->
<div class="offcanvas offcanvas-start"
    tabindex="-1"
    id="mobileSidebarDrawer"
    aria-labelledby="mobileSidebarLabel">

    <div class="offcanvas-header">
        <span class="fw-bold fs-5 text-white" id="mobileSidebarLabel">MINE-OPS</span>
        <button type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <nav class="nav flex-column pt-2 sidebar-nav" aria-label="Primary navigation">

            <a class="nav-link" href="/admin-dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <a class="nav-link" href="/prestart-insepction">
                <i class="fas fa-clipboard-check"></i>
                <span class="sidebar-text">Pre-Start Inspection</span>
            </a>

            <a class="nav-link" href="/manpower">
                <i class="fas fa-users"></i>
                <span class="sidebar-text">Manpower</span>
            </a>

            <div class="sidebar-divider"></div>

            <a class="nav-link" href="<?= url_to('logout'); ?>">
                <i class="fas fa-sign-out-alt"></i>
                <span class="sidebar-text">Logout</span>
            </a>

        </nav>
    </div>

</div>