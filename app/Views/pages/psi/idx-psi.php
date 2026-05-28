<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('pageStyles'); ?>
<!-- DataTables Bootstrap5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- date range picker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?= $this->endSection(); ?>


<?= $this->section('content'); ?>
<?php if (session()->has('inserted')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Import Selesai!</strong>
        <ul>
            <li>Data Berhasil Disimpan: <?= session('inserted') ?></li>
            <li>Data Dilewati (Skipped): <?= session('skipped') ?></li>
        </ul>

        <?php if (!empty(session('errors'))): ?>
            <hr>
            <p class="mb-0">Detail Error:</p>
            <div style="max-height: 200px; overflow-y: auto;">
                <ul class="list-unstyled">
                    <?php foreach (session('errors') as $error): ?>
                        <li><small><?= esc($error) ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<!-- the body on here -->
<h2>Welcome aboard</h2>
<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Facilis quaerat hic in veritatis fugiat. Ab, libero? Amet numquam blanditiis temporibus aperiam. Maiores nesciunt quae perferendis.</p>
<br>
<div class="col-lg-12 p-0 m-0">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
        Import Record
    </button>
    <button type="button" id="generateReport" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#generateReportModal">
        Generate Report
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

<!-- reporting the PSI -->
<div class="modal fade" id="generateReportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="generateReportModalLabel">Pre-Start Inspection Import</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-2 border-end">
                        <div class="mb-3">
                            <label class="form-label">Select Date Range</label>
                            <input type="text" id="datePicker" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-10 border-end">
                        <p>choose equipment</p>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. At corporis reprehenderit consequuntur voluptatem atque officiis ex nostrum veritatis! Sunt, perspiciatis vel! Temporibus omnis suscipit ratione doloribus reprehenderit quidem dicta ut nisi veniam fugiat dolore eligendi, corrupti dolorum harum beatae autem inventore recusandae molestiae praesentium esse nostrum vero distinctio, dignissimos illo. Impedit saepe itaque autem ratione quibusdam praesentium rem! Sequi obcaecati eos omnis corrupti consequuntur quis laudantium. Atque tenetur culpa doloribus consequuntur sequi recusandae. Ea, blanditiis.</p>
                    </div>
                    <br>
                    <div class="col-lg-4 border-start">
                        <p><small>Lorem ipsum dolor sit amet consectetur adipisicing elit. Illo omnis rem possimus vitae minus blanditiis distinctio, quibusdam cupiditate laboriosam enim unde laborum ex temporibus dolores recusandae excepturi, a id voluptate?</small></p>
                    </div>
                    <div class="col-lg-8">
                        <p><small>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nam tempora facilis laboriosam quod animi doloremque vel modi reprehenderit placeat, eos perspiciatis est impedit maiores. Provident fuga itaque illo deleniti modi?</small></p>
                    </div>
                    <div class="col-lg-8">
                        <p><small>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Modi magni sequi eveniet autem at delectus iste alias fugit neque aliquam minima quas illum laudantium odit, molestiae illo dolore eligendi nesciunt!</small></p>
                    </div>
                    <div class="col-lg-4">
                        <p><small>Lorem ipsum dolor sit amet consectetur adipisicing elit. Illo omnis rem possimus vitae minus blanditiis distinctio, quibusdam cupiditate laboriosam enim unde laborum ex temporibus dolores recusandae excepturi, a id voluptate?</small></p>
                    </div>
                    <div class="col-lg-12">
                        <p><small>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Dolorum, aliquam doloremque ad libero dolore facilis itaque aperiam hic animi modi labore autem esse et, voluptate eveniet molestias voluptatem nam? Nulla modi incidunt labore, ad vitae facilis voluptas tempore. Quasi enim ducimus vel maxime quam explicabo voluptatum rem, deleniti porro id accusamus dolorem qui consequatur sit unde. Natus, quod doloribus? Quo, tempore deleniti iusto aliquam consequuntur illo pariatur autem accusamus veniam atque voluptates labore amet reprehenderit laudantium. Necessitatibus doloribus aliquam, voluptatum delectus sunt harum praesentium sint tempora quod cupiditate provident veniam. Similique quas eius sint temporibus qui adipisci, pariatur mollitia minima sequi eos repellat error, ab labore ullam laborum ducimus aut atque doloribus porro id iste maxime. Ipsa ullam id incidunt soluta veniam. Praesentium voluptatem qui, dolore cumque saepe nemo hic placeat quibusdam amet odio, maiores eligendi soluta sapiente similique! Odio, quasi! Debitis, laudantium quaerat. Ab eius pariatur dicta animi unde cum illum tempora modi a consequatur numquam accusamus neque sint nulla, dolor obcaecati dolore quaerat. Hic perspiciatis quo necessitatibus consectetur officia deleniti porro nisi expedita, recusandae velit optio, animi eum nam excepturi autem quaerat! Pariatur eaque laboriosam ullam, itaque eum unde numquam dicta consequatur consequuntur deserunt quisquam, fugit repellendus tempora rerum nulla? Sed hic earum, debitis, dolorem accusantium eum cumque eius perferendis sint incidunt assumenda aliquam dicta quos recusandae culpa tenetur beatae praesentium necessitatibus! Eaque nostrum delectus unde qui aut corrupti explicabo expedita in cupiditate, similique alias reprehenderit voluptatibus sit commodi minima molestias! Perspiciatis nihil accusantium quos, temporibus facere deserunt. Facere expedita id autem excepturi, enim tempora, odio voluptatum aliquid ea doloribus consequuntur tenetur neque quae nemo temporibus dolor consequatur corporis quo beatae quisquam. Sequi, sapiente. Debitis quae fugit, possimus sint facilis veritatis exercitationem cumque harum pariatur quasi sapiente suscipit ad aspernatur, doloremque obcaecati vel. Exercitationem voluptatibus perferendis cum veritatis.</small></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning">Submit</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('main-js'); ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables Core JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap5 JS -->
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- moment.js -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- daterange picker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>pt>
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

        $('#datePicker').daterangepicker({
            opens: 'right'
        });
    });
</script>
<?= $this->endSection(); ?>