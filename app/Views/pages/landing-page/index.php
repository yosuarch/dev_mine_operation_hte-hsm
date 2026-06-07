<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('pageStyles'); ?>
<style>
    /* Apply to the entire document */
    html,
    body {
        overflow: -moz-scrollbars-none;
        /* Firefox */
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
        overflow-y: scroll;
        /* Keeps functionality */
    }

    /* Container for cards: fluid on mobile, fixed on desktop */
    .landing-container {
        width: 100%;
        /* Start full width for mobile */
        max-width: 450px;
        /* The 'fixed' look on desktop */
        margin: 0 auto;
        /* Centers the container */
        padding: 0 15px;
        /* Adds breathing room on small screens */
    }
</style>

<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="landing-container d-flex flex-column gap-4">
            <!-- to admin panel -->
            <div class="card text-center">
                <div class="card-header">Admin</div>
                <div class="card-body">
                    <h5 class="card-title">Manage Mine-Operation Data</h5>
                    <p class="card-text">
                        Administrators may access this portal to manage department
                        operations. For optimal performance, please use a desktop
                        device.
                    </p>
                    <a href="/admin" class="btn btn-primary">Access Dashboard</a>
                </div>
                <div class="card-footer text-body-secondary">
                    <p>
                        <small>note: this admin-panel is not design for mobile device,
                            please use desktop or larger screen</small>
                    </p>
                </div>
            </div>
            <!-- group-leader -->
            <div class="card text-center">
                <div class="card-header">Group Leader</div>
                <div class="card-body">
                    <h5 class="card-title">Monitor Mine-Operation Data</h5>
                    <p class="card-text">
                        View and monitor daily mining operations and team performance
                        metrics through this portal.
                    </p>
                    <a href="/group-leader" class="btn btn-primary">View Reports</a>
                </div>
                <div class="card-footer text-body-secondary"></div>
            </div>
            <!-- to operator/driver panel -->
            <div class="card text-center">
                <div class="card-header">Operation</div>
                <div class="card-body">
                    <h5 class="card-title">Operator and Driver</h5>
                    <p class="card-text">
                        Operators and drivers should use this portal to manage daily
                        task assignments and operational inputs.
                    </p>
                    <a href="/operator-driver" class="btn btn-primary">Access Portal</a>
                </div>
                <div class="card-footer text-body-secondary"></div>
            </div>
            <!-- PLM panel -->
            <div class="card text-center">
                <div class="card-header">PLM Panel</div>
                <div class="card-body">
                    <h5 class="card-title">Update Status Equipment Status</h5>
                    <p class="card-text">
                        Manage equipment status updates, report breakdowns, and modify
                        existing operational data here.
                    </p>
                    <a href="/plm-record" class="btn btn-primary">Update Records</a>
                </div>
                <div class="card-footer text-body-secondary"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>