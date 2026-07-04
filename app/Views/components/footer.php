<div class="bg-body-tertiary border-top py-4 px-3">
    <div class="container-fluid">
        <div class="row g-4 text-body-secondary">

            <!-- Brand -->
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-hard-hat text-primary"></i>
                    <span class="fw-bold text-body fs-6">MINE-OPS</span>
                </div>
                <p class="small mb-1">Mining Operations Management System</p>
                <p class="small mb-2">Developed by <span class="fw-semibold text-body">FULLSTACK-KARBITAN</span></p>
                <span class="badge bg-primary bg-opacity-75" style="font-size:.7rem;">Beta Release v1.0</span>
            </div>

            <!-- Quick links -->
            <div class="col-md-4">
                <h6 class="text-body fw-semibold mb-3">
                    <i class="fas fa-link me-1 opacity-50"></i> Quick Access
                </h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-1">
                    <li>
                        <a href="/admin-dashboard" class="text-body-secondary text-decoration-none footer-link">
                            <i class="fas fa-tachometer-alt me-2 opacity-50" style="width:14px;"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/prestart-insepction" class="text-body-secondary text-decoration-none footer-link">
                            <i class="fas fa-clipboard-check me-2 opacity-50" style="width:14px;"></i>Pre-Start Inspection
                        </a>
                    </li>
                    <li>
                        <a href="/manpower" class="text-body-secondary text-decoration-none footer-link">
                            <i class="fas fa-users me-2 opacity-50" style="width:14px;"></i>Manpower
                        </a>
                    </li>
                    <li>
                        <a href="/plm-record" class="text-body-secondary text-decoration-none footer-link">
                            <i class="fas fa-screwdriver-wrench me-2 opacity-50" style="width:14px;"></i>PLM Records
                        </a>
                    </li>
                </ul>
            </div>

            <!-- System info -->
            <div class="col-md-4">
                <h6 class="text-body fw-semibold mb-3">
                    <i class="fas fa-circle-info me-1 opacity-50"></i> System
                </h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-1">
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-code-branch opacity-50" style="width:14px;"></i>
                        <span>Version <strong class="text-body">1.0.0-beta</strong></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-server opacity-50" style="width:14px;"></i>
                        <span>Environment: <strong class="text-body">Production</strong></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-envelope opacity-50" style="width:14px;"></i>
                        <span>ops-support@hte-msm.internal</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-circle text-success opacity-75" style="width:14px; font-size:.5rem;"></i>
                        <span>All systems <strong class="text-success">operational</strong></span>
                    </li>
                </ul>
            </div>

        </div>

        <hr class="my-3 opacity-25">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small text-body-secondary">
            <span>&copy; <?= date('Y') ?> FULLSTACK-KARBITAN. All rights reserved.</span>
            <span class="opacity-50">
                <i class="fas fa-shield-halved me-1"></i>Internal use only
            </span>
        </div>
    </div>
</div>

<style>
    .footer-link:hover { color: var(--bs-body-color) !important; }
</style>
