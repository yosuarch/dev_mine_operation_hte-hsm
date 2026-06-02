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
<?= $this->include('pages/psi/session-upload-notification'); ?>
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
    <div class="row">
        <div class="col-lg-6">
            <div style="width: 80%; margin: 20px auto;">
                <canvas id="dangerFreqChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- per-equipment type -->
            <?= $this->include('pages/psi/tabel-by-equipment-type'); ?>
        </div>
    </div>
    <h3>Daily Pre-Start Inspection (P2H) Record</h3>
    <div class="table-responsive">
        <!-- recorded table -->
        <?= $this->include('pages/psi/tabel-prestart-record'); ?>
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
<?= $this->include('pages/psi/script-chart-one-tabel'); ?>
<?= $this->include('pages/psi/script-prestart-recorded-tabel'); ?>
<?= $this->include('pages/psi/script-preview-tabel'); ?>
<?= $this->include('pages/psi/script-issue-summary-by-equipment-type'); ?>
<!-- in-pages -->
<script>
    $(document).ready(function() {
        // datepicker
        $('#datePicker').daterangepicker({
            opens: 'right'
        });
    });
</script>
<?= $this->endSection(); ?>