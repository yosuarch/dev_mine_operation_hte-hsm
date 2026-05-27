<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content'); ?>
<!-- the body on here -->
<h2>Welcome aboard</h2>
<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Facilis quaerat hic in veritatis fugiat. Ab, libero? Amet numquam blanditiis temporibus aperiam. Maiores nesciunt quae perferendis.</p>
<br>
<div class="col-lg-12 p-0 m-0">
    <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-primary">Upload</button>
        <button type="button" class="btn btn-primary">Download</button>
        <button type="button" class="btn btn-primary">Create Report</button>
    </div>
    <h3>raw recorded data</h3>
    <div style="overflow-x: auto;">
        <!-- recorded table -->
        <table id="prestartInspectionTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Employee ID</th>
                    <th>Gender</th>
                    <th>Equipment ID</th>
                    <th>Type</th>
                    <th>Model</th>
                    <th>HM-Start</th>
                    <th>HM-End</th>
                    <th>Check Item</th>
                    <th>Danger Code</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <!-- body from json -->
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('script'); ?>
<script>
    $(document).ready(function() {
        $('#prestartInspectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/ajax-datatable/prestartrecord',
            columnDefs: [{ // 
                    // Date
                    targets: 0,
                    width: '100px'
                },
                { // Name
                    targets: 1,
                    width: '150px'
                },
                { // Employee ID
                    targets: 2,
                    width: '100px'
                },
                { // Gender
                    targets: 3,
                    width: '80px'
                },
                { // Equipment ID
                    targets: 4,
                    width: '120px'
                },
                { // Type
                    targets: 5,
                    width: '200px'
                },
                { // Model
                    targets: 6,
                    width: '100px'
                },
                { // HM-Start
                    targets: 7,
                    width: '100px'
                },
                { // HM-End
                    targets: 8,
                    width: '100px'
                },
                { // Check Item
                    targets: 9,
                    width: '120px'
                },
                { // Danger Code
                    targets: 10,
                    width: '70px'
                },
                { // Note
                    targets: 11,
                    width: '200px'
                },
            ]
        });
    });
</script>
<?= $this->endSection(); ?>