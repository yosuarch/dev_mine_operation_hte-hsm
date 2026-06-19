<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('link'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    // Pre-paint: apply stored color mode before CSS renders to prevent flash
    (function() {
        var m = localStorage.getItem('mine_ops_color_mode') || 'system';
        var d = document.documentElement;
        if (m === 'dark') d.setAttribute('data-bs-theme', 'dark');
        else if (m === 'light') d.setAttribute('data-bs-theme', 'light');
        else d.setAttribute('data-bs-theme', window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    })();
</script>
<?= $this->endSection(); ?>

<?= $this->section('pageStyles'); ?>
<style>
    html,
    body {
        overflow: -moz-scrollbars-none;
        -ms-overflow-style: none;
        scrollbar-width: none;
        overflow-y: scroll;
    }

    /* ── Top bar ─────────────────────────────────────────── */
    .page-top-bar {
        height: 48px;
        position: sticky;
        top: 0;
        z-index: 30;
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color-translucent);
    }

    /* ── Typography ──────────────────────────────────────── */
    .section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: var(--bs-secondary-color);
    }

    .card-title-main {
        font-size: 1.3rem;
        font-weight: 700;
    }

    /* ── Inputs ──────────────────────────────────────────── */
    .form-control-lg,
    .form-select-lg {
        font-size: 1.05rem;
        min-height: 3.2rem;
    }

    input[readonly] {
        background-color: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }

    /* ── P2H checklist item cards ────────────────────────── */
    .psi-item-card {
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
        background: var(--bs-body-bg);
    }

    .psi-item-card.state-normal {
        background-color: rgba(25, 135, 84, 0.06) !important;
    }

    .psi-item-card.state-abnormal {
        background-color: rgba(220, 53, 69, 0.09) !important;
    }

    [data-bs-theme="dark"] .psi-item-card.state-normal {
        background-color: rgba(25, 135, 84, 0.14) !important;
    }

    [data-bs-theme="dark"] .psi-item-card.state-abnormal {
        background-color: rgba(220, 53, 69, 0.17) !important;
    }

    /* ── Fixed bottom progress bar ───────────────────────── */
    .psi-progress-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: var(--bs-body-bg);
        border-top: 1px solid var(--bs-border-color-translucent);
        padding: 10px 16px 14px;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
    }

    [data-bs-theme="dark"] .psi-progress-fixed {
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.3);
    }

    /* Push content above the fixed bar so submit button isn't hidden */
    .psi-bottom-spacer {
        height: 72px;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?= csrf_field() ?>
<script>
    const _SERVER_DATE = '<?= (new DateTime('now', new DateTimeZone('Asia/Jayapura')))->format('Y-m-d') ?>';
    const _SERVER_HOUR = <?= (new DateTime('now', new DateTimeZone('Asia/Jayapura')))->format('G') ?>;
</script>

<!-- Top Bar -->
<div class="page-top-bar d-flex align-items-center justify-content-between px-3 px-md-4">
    <span class="fw-bold" style="font-size:0.85rem;letter-spacing:0.06em;color:var(--bs-body-color);">
        MINE-OPS &middot; <span class="text-primary">P2H Form</span>
    </span>
    <button type="button" id="themeToggleBtn"
        class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
        style="width:36px;height:36px;background:var(--bs-secondary-bg);color:var(--bs-body-color);border:1px solid var(--bs-border-color-translucent);"
        title="Follow system" aria-label="Follow system">
        <i id="themeToggleIcon" class="fas fa-circle-half-stroke" style="font-size:0.9rem;pointer-events:none;"></i>
    </button>
</div>

<div class="container-fluid py-3">
    <div class="row p-0 justify-content-center">
        <div class="col-12 col-md-8 col-lg-5 d-flex flex-column gap-4">

            <!-- IDENTITY -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="section-label mb-1">IDENTITY</p>
                    <h5 class="card-title-main mb-4">Operator and Driver</h5>

                    <div class="mb-4">
                        <label for="mpSearch" class="form-label fw-semibold fs-6">Full Name</label>
                        <input type="text" class="form-control form-control-lg" id="mpSearch" list="mpListOptions"
                            placeholder="Type your name..." autocomplete="off">
                        <datalist id="mpListOptions"></datalist>
                        <div class="invalid-feedback">Please select a valid operator / driver name.</div>
                    </div>

                    <div>
                        <label for="opDrID" class="form-label fw-semibold fs-6">Employee ID</label>
                        <input type="text" class="form-control form-control-lg" id="opDrID" name="opDrID"
                            readonly placeholder="Auto-filled after name selection">
                    </div>

                    <input type="hidden" id="opDrIdx" name="opDrIdx">
                </div>
            </div>

            <!-- OPEN SHIFT SECTION (shown when Redis session detected) -->
            <div id="openShiftSection" style="display:none;">

                <!-- Equipment picker — shown when operator has 2+ open shifts -->
                <div id="openShiftPicker" class="card border-0 shadow-sm" style="display:none;">
                    <div class="card-body p-4">
                        <p class="section-label mb-1">OPEN SHIFTS</p>
                        <h5 class="card-title-main mb-1">Select Equipment to Close</h5>
                        <p class="text-muted mb-4" style="font-size:0.9rem;">
                            You have multiple open shifts. Pick the equipment you finished using.
                        </p>
                        <div id="openShiftPickerList" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>

                <!-- HM End form — shown once equipment is determined -->
                <div id="openShiftHmForm" class="card border-0 shadow-sm" style="display:none;">
                    <div class="card-body p-4">
                        <p class="section-label mb-1">END OF SHIFT</p>
                        <h5 class="card-title-main mb-3">Close Your Shift</h5>

                        <!-- Read-only session summary -->
                        <div id="openShiftSummary" class="mb-4 p-3 rounded-3"
                             style="background:var(--bs-secondary-bg);font-size:0.9rem;"></div>

                        <div class="mb-4">
                            <label for="hmEndInput" class="form-label fw-semibold fs-6">Hour-Meter End</label>
                            <input type="number" id="hmEndInput" class="form-control form-control-lg"
                                   min="0" step="0.1" placeholder="e.g. 4789.0">
                            <div class="invalid-feedback">Must be greater than the starting hour-meter.</div>
                        </div>

                        <button type="button" id="btnSubmitHmEnd"
                                class="btn btn-success btn-lg w-100 py-3 fw-bold fs-5">
                            Close Shift
                        </button>
                        <div id="hmEndError" class="alert alert-danger mt-3 d-none small mb-0"></div>
                    </div>
                </div>

                <!-- Escape hatch -->
                <div class="text-center">
                    <button type="button" id="btnStartNewPsiInstead"
                            class="btn btn-link text-secondary small">
                        Start a new P2H instead
                    </button>
                </div>
            </div>

            <!-- VEHICLE DETAILS -->
            <div id="vehicleCard" class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="section-label mb-1">VEHICLE DETAILS</p>
                    <h5 class="card-title-main mb-1">Equipment Assignment</h5>
                    <p class="text-muted mb-4" style="font-size:0.9rem;">Select the equipment you will operate this shift.</p>

                    <div class="mb-4">
                        <label for="equipType" class="form-label fw-semibold fs-6">Equipment Type</label>
                        <select class="form-select form-select-lg" id="equipType" name="equipType">
                            <option selected value="">Select Equipment Type</option>
                        </select>
                    </div>

                    <div>
                        <label for="equipID" class="form-label fw-semibold fs-6">Equipment ID</label>
                        <select class="form-select form-select-lg" id="equipID" name="equipID">
                            <option value="">Select Equipment ID</option>
                        </select>
                        <div class="invalid-feedback">Please select an equipment to load the checklist.</div>
                    </div>
                </div>
            </div>

            <!-- P2H CHECKLIST -->
            <div id="checklistCard" class="card border-0 shadow-sm">
                <div class="card-body p-4 pb-2">
                    <p class="section-label mb-1">PRE-START INSPECTION</p>
                    <h5 class="card-title-main mb-1">P2H Checklist</h5>
                    <p class="text-muted mb-0" style="font-size:0.9rem;">
                        Inspect each item and mark its condition.
                        Items marked <strong>Not-Normal</strong> require a written note.
                    </p>
                </div>
                <div class="card-body px-3 pb-4 pt-2" id="psiFormItems">
                    <p class="text-muted mb-0 small">Select an equipment above to load the checklist.</p>
                </div>
            </div>

            <!-- SUBMIT -->
            <div id="submitCard" class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="card-title-main mb-1">Ready to Start Shift?</h5>
                    <p class="text-muted mb-4" style="font-size:0.9rem;">
                        Make sure all checklist items are reviewed before submitting.
                    </p>
                    <button type="button" id="btnSubmitPSI" class="btn btn-primary btn-lg w-100 py-3 fw-bold fs-5">
                        Submit and Proceed
                    </button>
                </div>
            </div>

            <!-- Spacer so submit card isn't hidden behind the fixed progress bar -->
            <div class="psi-bottom-spacer"></div>

        </div>
    </div>
</div>

<!-- Fixed Bottom Progress Bar (appears after checklist loads) -->
<div id="psiProgressWrap" class="psi-progress-fixed" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="section-label mb-0">P2H PROGRESS</span>
        <span id="psiProgressLabel" class="fw-bold" style="font-size:0.78rem;color:var(--bs-body-color);">
            0 / 0 reviewed
        </span>
    </div>
    <div class="progress rounded-pill" style="height:6px;">
        <div id="psiProgressBar" class="progress-bar bg-primary" role="progressbar"
            style="width:0%;transition:width 0.4s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="section-label mb-0">REVIEW BEFORE SUBMITTING</p>
                    <h5 class="modal-title fw-bold" id="summaryModalLabel">Submission Summary</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">

                <!-- SHIFT DETAILS — auto-populated by JS, user can override -->
                <div class="mb-4 pb-4 border-bottom">
                    <p class="section-label mb-3">SHIFT DETAILS</p>
                    <div class="mb-3">
                        <label for="psiDate" class="form-label fw-semibold">Date</label>
                        <input type="date" id="psiDate" class="form-control form-control-lg">
                        <div class="invalid-feedback">Date is required.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Shift</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="psiShift" id="shiftDay" value="1" autocomplete="off">
                            <label class="btn btn-outline-warning flex-fill py-3" for="shiftDay">
                                <i class="fas fa-sun me-2"></i>Day
                            </label>
                            <input type="radio" class="btn-check" name="psiShift" id="shiftNight" value="2" autocomplete="off">
                            <label class="btn btn-outline-primary flex-fill py-3" for="shiftNight">
                                <i class="fas fa-moon me-2"></i>Night
                            </label>
                        </div>
                        <div class="text-danger small mt-1 d-none" id="shiftRequiredMsg">Please select a shift.</div>
                    </div>
                    <div>
                        <label for="psiHourMeter" class="form-label fw-semibold">Hour-Meter Start</label>
                        <input type="number" id="psiHourMeter" class="form-control form-control-lg"
                               min="0" step="0.1" placeholder="e.g. 4521.5">
                        <div class="invalid-feedback">Please enter the hour-meter start reading.</div>
                    </div>
                </div>

                <!-- DYNAMIC SUMMARY (operator, equipment, checklist) -->
                <div id="summaryBody">
                    <!-- filled dynamically by script-submit.php -->
                </div>

            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                    Back to Edit
                </button>
                <button type="button" id="btnConfirmSubmit" class="btn btn-primary flex-fill fw-bold">
                    Confirm &amp; Submit
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<?= $this->include('pages/psi/mobile/script/script-operator_driver_name_id'); ?>
<?= $this->include('pages/psi/mobile/script/script-equipment_type_unique'); ?>
<?= $this->include('pages/psi/mobile/script/script-equipment_id'); ?>
<?= $this->include('pages/psi/mobile/script/script-psi_form'); ?>
<?= $this->include('pages/psi/mobile/script/script-submit'); ?>
<?= $this->include('pages/psi/mobile/script/script-hm-end'); ?>
<?= $this->include('pages/psi/mobile/script/script-color-mode'); ?>
<?= $this->endSection(); ?>