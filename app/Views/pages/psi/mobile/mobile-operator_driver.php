<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('link'); ?>
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

    .section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: #6c757d;
    }

    .card-title-main {
        font-size: 1.3rem;
        font-weight: 700;
    }

    /* Larger touch targets for selects and inputs */
    .form-control-lg,
    .form-select-lg {
        font-size: 1.05rem;
        min-height: 3.2rem;
    }

    /* Readonly input: visually distinct but readable */
    input[readonly] {
        background-color: #f8f9fa;
        color: #495057;
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
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
                    </div>

                    <div>
                        <label for="opDrID" class="form-label fw-semibold fs-6">Employee ID</label>
                        <input type="text" class="form-control form-control-lg" id="opDrID" name="opDrID"
                            readonly placeholder="Auto-filled after name selection">
                    </div>

                    <input type="hidden" id="opDrIdx" name="opDrIdx">
                </div>
            </div>

            <!-- VEHICLE DETAILS -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="section-label mb-1">VEHICLE DETAILS</p>
                    <h5 class="card-title-main mb-1">Equipment Assignment</h5>
                    <p class="text-muted mb-4">Select the equipment you will operate this shift.</p>

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
                    </div>
                </div>
            </div>

            <!-- P2H CHECKLIST -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 pb-2">
                    <p class="section-label mb-1">PRE-START INSPECTION</p>
                    <h5 class="card-title-main mb-1">P2H Checklist</h5>
                    <p class="text-muted mb-0">Inspect each item and mark its condition.</p>
                </div>
                <div class="card-body px-4 pb-4 pt-3" id="psiFormItems">
                    <p class="text-muted mb-0">Select an equipment above to load the checklist.</p>
                </div>
            </div>

            <!-- SUBMIT -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="card-title-main mb-1">Ready to Start Shift?</h5>
                    <p class="text-muted mb-4">Make sure all items above are completed.</p>
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold fs-5">
                        Submit and Proceed
                    </button>
                </div>
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
<?= $this->endSection(); ?>
