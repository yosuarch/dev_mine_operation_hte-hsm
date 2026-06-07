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
                    <p class="card-text">Please input your name and employee ID to proceed.</p>
                </div>
                <div class="card-body pt-0">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="opDrName" placeholder="John Doe" name="opDrName">
                        <label for="opDrName">Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="opDrPass" placeholder="Employee ID" name="opDrPass">
                        <label for="opDrPass">Employee ID</label>
                    </div>
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
                            <option selected>Select Equipment Type</option>
                            <option value="1">Excavator</option>
                            <option value="2">ADT</option>
                            <option value="3">Dumptruck</option>
                            <option value="4">Motor Grader</option>
                            <option value="5">Compactor</option>
                            <option value="6">Water Truck</option>
                            <option value="7">Wheel Loader</option>
                        </select>
                        <label for="equipType">Equipment Type</label>
                    </div>
                    <div class="form-floating mb-2">
                        <select class="form-select" id="equipID" aria-label="Floating label select example" name="equipID">
                            <option selected>Select Equipment ID</option>
                            <option value="1">HAVV0001</option>
                            <option value="2">HAVV0002</option>
                            <option value="3">HEXC0001</option>
                            <option value="4">HEXK0001</option>
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
                <div class="card-body pt-0">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="opDrName" placeholder="John Doe" name="opDrName">
                        <label for="opDrName">Name</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="opDrPass" placeholder="Employee ID" name="opDrPass">
                        <label for="opDrPass">Employee ID</label>
                    </div>
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
<?= $this->include('pages/psi/mobile/script/script-operator_driver'); ?>
<?= $this->endSection(); ?>