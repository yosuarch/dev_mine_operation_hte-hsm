<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content'); ?>
<!-- body -->
<div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-primary">Upload</button>
    <button type="button" class="btn btn-primary">Download</button>
</div>
<table id="manPowerListTable" class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Employee ID</th>
            <th>Gender</th>
            <th>Phone Number</th>
        </tr>
    </thead>
    <tbody>
        <!-- body from json -->
    </tbody>
</table>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- javascript -->
<script>
    // js will be in here
    $(document).ready(function() {
        ('#manPowerListTable').DataTable({
            processing: true,
            serverside: true,
            ajax: '/ajax-datatable/manpowerlist',
            columnDefs: [{
                    targets: 0,
                    width: '100px'
                },
                {
                    targets: 1,
                    width: '100px'
                },
                {
                    targets: 2,
                    width: '100px'
                },
                {
                    targets: 3,
                    width: '100px'
                }
            ]
        });
    })
</script>
<?= $this->endSection(); ?>