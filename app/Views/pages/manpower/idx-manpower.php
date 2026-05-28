<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content'); ?>
<!-- body -->
<div class="col-lg-8">
    <table id="manPowerListTable" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Number</th>
                <th>Name</th>
                <th>Employee ID</th>
                <th>Gender</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- body from json -->
        </tbody>
    </table>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<!-- DataTables Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<!-- JSZip for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- PDFMake for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.0/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.0/vfs_fonts.min.js"></script>
<!-- DataTables Button HTML5 export -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- javascript -->
<script>
    $(document).ready(function() {
        $('#manPowerListTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '/ajax-datatable/manpowerlist',
            columnDefs: [{
                    targets: 0,
                    width: '10%'
                },
                {
                    targets: 1,
                    width: '25%'
                },
                {
                    targets: 2,
                    width: '25%'
                },
                {
                    targets: 3,
                    width: '15%'
                },
                {
                    targets: 4,
                    width: '25%'
                },
                {
                    targets: 5,
                    width: '25%',
                    orderable: false,
                    searchable: false,
                    data: null,
                    render: function(data, type, row) {
                        if (type !== 'display') return '';
                        return `
                            <button class="btn btn-sm btn-info" onclick="viewManpower(${row.idx})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editManpower(${row.idx})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteManpower(${row.idx})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        `;
                    },

                }
            ],
            dom: 'Bfrtip',
            buttons: [{
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
            ]
        });
    });

    // Action functions

    function viewManpower(idx) {
        alert('View manpower: ' + idx);
        // TODO: Implement view modal
    }

    function editManpower(idx) {
        alert('Edit manpower: ' + idx);
        // TODO: Implement edit modal
    }

    function deleteManpower(idx) {
        if (confirm('Are you sure you want to delete this record?')) {
            alert('Delete manpower: ' + idx);
            // TODO: Implement delete via AJAX
        }
    }
</script>
<?= $this->endSection(); ?>