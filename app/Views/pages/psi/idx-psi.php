<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content'); ?>
<!-- the body on here -->
<h2>Welcome aboard</h2>
<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Facilis quaerat hic in veritatis fugiat. Ab, libero? Amet numquam blanditiis temporibus aperiam. Maiores nesciunt quae perferendis.</p>
<br>
<div class="col-lg-12 p-0 m-0">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
        Import Record
    </button>
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

<!-- modal -->
<?= $this->section('modal'); ?>
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Pre-Start Inspection Import</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="psiForm" method="post" action="/upload-psi-record" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Select the Pre-Start Inspection (P2H) File</label>
                                <input class="form-control" type="file" id="formFile" name="psiRecording">
                            </div>
                            <p><small>only excel file less than 5mb can be upload</small></p>
                            <button type="button" id="previewPSIFile" class="btn btn-outline-info">Preview</button>
                        </div>
                        <div class="col-lg-8">
                            <p>Lorem ipsum dolor sit amet.</p>
                        </div>
                        <div class="col-lg-12">
                            <!-- preview table -->
                            <table id="filePreview" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Equipment ID</th>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Operator Name</th>
                                        <th>HM Start</th>
                                        <th>HM End</th>
                                        <th>Checking Item</th>
                                        <th>Checking Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- body from json -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Submit</button>
                </div>
            </form>
        </div>
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
                    width: '200px',
                    render: function(data) {
                        // Replace underscores with spaces, remove other special characters, and convert to uppercase
                        return data.replace(/_/g, ' ').replace(/[^a-zA-Z0-9 ]/g, '').toUpperCase();
                    }
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
                    width: '120px',
                    render: function(data) {
                        // Transform the content to UPPER CASE
                        return data.replace(/_/g, ' ').toUpperCase();
                    }
                },
                { // Danger Code
                    targets: 10,
                    width: '70px'
                },
                { // Note
                    targets: 11,
                    width: '200px',
                    render: function(data) {
                        // Transform the content to UPPER CASE
                        return data.replace(/_/g, ' ').toUpperCase();
                    }
                },
            ]
        });

        $('#previewPSIFile').click(function() {
            const fileInput = $('#formFile')[0];
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select a file first');
                return;
            }

            // Create FormData and send via AJAX
            const formData = new FormData();
            formData.append('psiRecording', file);

            $.ajax({
                url: '/preview-psi-record',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Populate the preview table
                        let rows = '';
                        response.data.forEach(row => {
                            rows += `<tr>
                            <td>${row.equipment_id}</td>
                            <td>${row.date}</td>
                            <td>${row.shift}</td>
                            <td>${row.operator_name}</td>
                            <td>${row.hourmeter_start}</td>
                            <td>${row.hourmeter_end}</td>
                            <td>${row.checking_part}</td>
                            <td>${row.checking_note}</td>
                        </tr>`;
                        });
                        $('#filePreview tbody').html(rows);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error previewing file');
                }
            });
        });
    });
</script>
<?= $this->endSection(); ?>