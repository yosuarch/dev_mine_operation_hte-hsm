<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('pageStyles'); ?>
<!-- DataTables Bootstrap5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<!-- DataTables Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div class="table-responsive">
    <table id="manPowerListTable" class="table table-striped table-hover table-bordered w-100">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Employee ID</th>
                <th>Gender</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<?= $this->endSection(); ?>

<?= $this->section('main-js'); ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables Core JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap5 JS -->
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<!-- JSZip for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- PDFMake for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.0/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.0/vfs_fonts.min.js"></script>
<!-- DataTables Button HTML5 export -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#manPowerListTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '/ajax-datatable/manpowerlist',
            dom:
                "<'row align-items-center mb-2'" +
                    "<'col-12 col-sm-6'B>" +
                    "<'col-12 col-sm-6 mt-2 mt-sm-0'f>" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row align-items-center mt-2'" +
                    "<'col-12 col-sm-5'i>" +
                    "<'col-12 col-sm-7 mt-2 mt-sm-0'p>" +
                ">",
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Manpower List'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Manpower List'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Manpower List'
                }
            ],
            columnDefs: [
                { targets: 0, width: '5%',  responsivePriority: 3 },
                { targets: 1, width: '28%', responsivePriority: 1 },
                { targets: 2, width: '20%', responsivePriority: 2 },
                { targets: 3, width: '10%', responsivePriority: 5 },
                { targets: 4, width: '22%', responsivePriority: 4 },
                {
                    targets: 5,
                    width: '15%',
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false,
                    data: null,
                    render: function(data, type, row) {
                        if (type !== 'display') return '';
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info" onclick="viewManpower(${row.idx})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning" onclick="editManpower(${row.idx})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteManpower(${row.idx})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ]
        });
    });

    function viewManpower(idx) {
        alert('View manpower: ' + idx);
    }

    function editManpower(idx) {
        alert('Edit manpower: ' + idx);
    }

    function deleteManpower(idx) {
        if (confirm('Are you sure you want to delete this record?')) {
            alert('Delete manpower: ' + idx);
        }
    }
</script>
<?= $this->endSection(); ?>
