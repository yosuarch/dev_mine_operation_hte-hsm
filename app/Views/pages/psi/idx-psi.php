<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('pageStyles'); ?>
<!-- DataTables Bootstrap5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- date range picker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- chart.js -->

<?= $this->endSection(); ?>


<?= $this->section('content'); ?>
<?php if (session()->has('inserted')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Import is Done!</strong>
        <ul>
            <li>Successfull Insert: <?= session('inserted') ?></li>
            <li>Skipped data: <?= session('skipped') ?></li>
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
<p><small>Use this module to import Pre-Start Inspection (P2H) records. Upon successful submission, automated notifications will be dispatched to the designated departments to ensure timely follow-up of any reported discrepancies.</small></p>
<div class="col-lg-12 p-0 m-0">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadPSIRecord">
        Import Record
    </button>
    <button type="button" id="generateReport" class="btn btn-warning disabled" data-bs-toggle="modal" data-bs-target="#generateReportModal">
        Generate Report
    </button>
    <div class="col-md-6">
        <div style="width: 80%; margin: 20px auto;">
            <canvas id="dangerFreqChart"></canvas>
        </div>
    </div>
    <h3>Pre-Start Inspection (P2H)</h3>
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
<?= $this->include('pages/psi/modal-report'); ?>
<?= $this->include('pages/psi/modal-upload'); ?>
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
<!-- chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        $.getJSON('<?= base_url("/ajax-chart/freq-danger-code") ?>', function(response) {
            // 1. Ambil list tanggal unik (Sumbu X)
            const dates = [...new Set(response.map(item => item.date))];

            // 2. Ambil list kode bahaya unik (Untuk membuat masing-masing bar)
            const dangerCodes = [...new Set(response.map(item => item.danger_code))];

            // 3. Buat dataset secara dinamis
            const datasets = dangerCodes.map(code => {
                return {
                    label: 'Danger Code: ' + code,
                    backgroundColor: getDangerColor(code),
                    // Kita petakan setiap tanggal ke frekuensi yang sesuai
                    data: dates.map(date => {
                        // Cari data untuk tanggal ini dan kode ini
                        const record = response.find(item => item.date === date && item.danger_code === code);
                        return record ? parseInt(record.frequency) : 0;
                    })
                };
            });

            // 4. Render Chart
            const ctx = document.getElementById('dangerFreqChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: datasets // Sekarang datasets berisi array dari semua kode yang ada
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });

            function getDangerColor(code) {
                const colors = {
                    'AA': '#8B0000', // Dark Red - Sangat Berbahaya
                    'A': '#FF0000', // Red      - Berbahaya
                    'B': '#FF6347', // Tomato   - Sedang
                    'C': '#FFA07A' // Light Salmon - Sangat Ringan
                };
                return colors[code] || '#cccccc';
            }
        });


        // datepicker
        $('#datePicker').daterangepicker({
            opens: 'right'
        });
    });
</script>
<?= $this->endSection(); ?>