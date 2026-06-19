<div class="offcanvas offcanvas-start offcanvas-lg bg-light"
     tabindex="-1"
     id="mobileSidebar"
     aria-labelledby="mobileSidebarLabel">

    <!-- Header visible only on mobile (close button) -->
    <div class="offcanvas-header border-bottom d-lg-none">
        <span class="fw-bold fs-5" id="mobileSidebarLabel">MINE-OPS</span>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#mobileSidebar"
                aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <nav class="nav flex-column py-2">
            <a class="nav-link" href="/">Dashboard</a>
            <a class="nav-link" href="/prestart-insepction">Pre-Start Inspection</a>
            <a class="nav-link" href="/manpower">Manpower</a>
            <hr class="my-2">
            <a class="nav-link" href="<?= url_to('logout'); ?>">Logout</a>
        </nav>
    </div>
</div>
