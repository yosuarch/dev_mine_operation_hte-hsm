<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('link'); ?>
<?= $this->endSection(); ?>

<?= $this->section('pageStyles'); ?>
<style>
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
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row mt-3 p-0 justify-content-center">
        <div class="col-12 col-md-8 col-lg-5 d-flex flex-column gap-3">

            <div class="card p-3">
                <div class="card-body">
                    <p class="card-text text-muted mb-1"><small>IDENTITY</small></p>
                    <h5 class="card-title">Operator and Driver</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="mpSearch" list="mpListOptions" placeholder="Type name..." autocomplete="off">
                        <label for="mpSearch">Search Name</label>
                        <datalist id="mpListOptions"></datalist>
                    </div>

                    <div class="form-floating">
                        <input type="text" class="form-control" id="opDrID" name="opDrID" readonly placeholder="Employee ID">
                        <label for="opDrID">Employee ID</label>
                    </div>

                    <input type="hidden" id="opDrIdx" name="opDrIdx">
                </div>
            </div>

            <div class="card p-3">
                <div class="card-body">
                    <p class="card-text text-muted mb-2"><small>VEHICLE DETAILS</small></p>
                    <h5 class="card-title">Equipment Assignment</h5>
                    <p class="card-text">Enter the equipment or vehicle number for this shift.</p>
                </div>
                <div class="card-body pt-0">
                    <div class="form-floating mb-2">
                        <select class="form-select" id="equipType" aria-label="Floating label select example" name="equipType">
                            <option selected value="">Select Equipment Type</option>
                        </select>
                        <label for="equipType">Equipment Type</label>
                    </div>
                    <div class="form-floating mb-2">
                        <select class="form-select" id="equipID" aria-label="Floating label select example" name="equipID">
                            <option value="">Select Equipment ID</option>
                        </select>
                        <label for="equipID">Equipment ID</label>
                    </div>
                </div>
            </div>

            <div class="card p-3">
                <div class="card-body">
                    <p class="card-text text-muted mb-1"><small>PRE-START INSPECTION</small></p>
                    <h5 class="card-title">P2H</h5>
                    <p class="card-text">Perform the unit inspection and make a record</p>
                </div>
                <div class="card-body pt-0" id="psiFormItems">
                    <p class="text-muted mb-0"><small>Select an equipment to load the inspection checklist.</small></p>
                </div>
            </div>

            <div class="card p-3">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Ready to Start Shift?</h5>
                    <button type="submit" class="btn btn-primary w-100 py-2">Submit and Proceed</button>
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