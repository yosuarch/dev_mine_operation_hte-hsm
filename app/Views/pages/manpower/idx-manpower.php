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

<?= csrf_field() ?>

<div class="mb-4 d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Manpower</h4>
        <p class="text-muted mb-0">Operator and driver roster.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnAddManpower">
        <i class="fas fa-user-plus me-1"></i> Add Manpower
    </button>
</div>

<!-- ── KPI Cards ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-users text-primary" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiTotal">&mdash;</div>
                    <div class="text-muted small mt-1">Total Manpower</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-person text-info" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiMan">&mdash;</div>
                    <div class="text-muted small mt-1">Male</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-danger bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-person-dress text-danger" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiWoman">&mdash;</div>
                    <div class="text-muted small mt-1">Female</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Manpower list ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="manPowerListTable" class="table table-striped table-hover table-bordered w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Phone Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Add / Edit Manpower Modal (shared) ───────────────────────── -->
<div class="modal fade" id="manpowerModal" tabindex="-1" aria-labelledby="manpowerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="manpowerModalLabel">Add Manpower</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">

                <input type="hidden" id="mpIdx">

                <div class="mb-3">
                    <label for="mpName" class="form-label fw-semibold">Name</label>
                    <input type="text" id="mpName" class="form-control" placeholder="e.g. Ahmad Ismail">
                    <div class="invalid-feedback">Name is required.</div>
                </div>

                <div class="mb-3">
                    <label for="mpEmployeeId" class="form-label fw-semibold">Employee ID</label>
                    <input type="text" id="mpEmployeeId" class="form-control" placeholder="e.g. 52241024" inputmode="numeric">
                    <div class="invalid-feedback">Employee ID is required and must be numeric.</div>
                </div>

                <div class="mb-3">
                    <label for="mpGender" class="form-label fw-semibold">Gender</label>
                    <select id="mpGender" class="form-select">
                        <option value="">Select gender</option>
                    </select>
                    <div class="invalid-feedback">Gender is required.</div>
                </div>

                <div class="mb-3">
                    <label for="mpRole" class="form-label fw-semibold">
                        Role <span class="text-muted fw-normal" id="mpRoleOptionalHint" style="display:none;">(optional on edit)</span>
                    </label>
                    <select id="mpRole" class="form-select">
                        <option value="">Select role</option>
                    </select>
                    <div class="invalid-feedback">Role is required.</div>
                </div>

                <div class="mb-3">
                    <label for="mpPhone1" class="form-label fw-semibold">
                        First Phone Number <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input type="text" id="mpPhone1" class="form-control" placeholder="e.g. 085256133242">
                </div>

                <div class="mb-3">
                    <label for="mpPhone2" class="form-label fw-semibold">
                        Second Phone Number <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input type="text" id="mpPhone2" class="form-control">
                </div>

                <div class="mb-1">
                    <label for="mpEmergency" class="form-label fw-semibold">
                        Emergency Contact Number <span class="text-muted fw-normal">(optional)</span>
                    </label>
                    <input type="text" id="mpEmergency" class="form-control">
                </div>

            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="mpError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnManpowerSave" class="btn btn-primary fw-bold px-4">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────── -->
<div class="modal fade" id="deleteManpowerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-triangle-exclamation text-warning mb-3" style="font-size:2rem;"></i>
                <p class="fw-semibold mb-1">Remove this manpower record?</p>
                <p class="text-muted small mb-0" id="deleteManpowerName"></p>
            </div>
            <div class="px-3 pb-2 d-none" id="deleteManpowerErrorWrap">
                <div class="alert alert-danger py-2 small mb-0" id="deleteManpowerError"></div>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnDeleteManpowerConfirm" class="btn btn-danger flex-fill fw-bold">Remove</button>
            </div>
        </div>
    </div>
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
    function mpEscAttr(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    $(document).ready(function() {

        const csrfToken = () => $('[name="csrf_test_name"]').val();
        function refreshCsrf(hash) { if (hash) $('[name="csrf_test_name"]').val(hash); }

        const table = $('#manPowerListTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '<?= base_url("/ajax-datatable/manpowerlist") ?>',
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
                { targets: 0, data: null, orderable: false, searchable: false, width: '5%',
                  render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                { targets: 1, data: 'name', width: '22%' },
                { targets: 2, data: 'employee_id', width: '14%' },
                { targets: 3, data: 'gender_label', width: '9%',
                  render: function(data) { return data ? (data.charAt(0).toUpperCase() + data.slice(1)) : '<span class="text-muted">&mdash;</span>'; } },
                { targets: 4, data: 'job_title', width: '17%',
                  render: function(data) { return data ? (data.charAt(0).toUpperCase() + data.slice(1)) : '<span class="text-muted">Unassigned</span>'; } },
                { targets: 5, data: 'phone_number', width: '16%',
                  render: function(data) { return data || '<span class="text-muted">&mdash;</span>'; } },
                {
                    targets: 6,
                    data: null,
                    width: '15%',
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (type !== 'display') return '';
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-warning btn-edit-manpower" title="Edit"
                                        data-idx="${row.idx}"
                                        data-name="${mpEscAttr(row.name)}"
                                        data-employee-id="${mpEscAttr(row.employee_id)}"
                                        data-gender-id="${row.gender_id ?? ''}"
                                        data-role-id="${row.role_id ?? ''}"
                                        data-phone1="${mpEscAttr(row.phone_number)}"
                                        data-phone2="${mpEscAttr(row.second_phone_number)}"
                                        data-emergency="${mpEscAttr(row.emergency_contact_number)}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-delete-manpower" title="Delete"
                                        data-idx="${row.idx}" data-name="${mpEscAttr(row.name)}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ]
        });

        // ── KPI cards + gender/role dropdown options ────────────────────────
        function loadKpiAndOptions() {
            $.ajax({
                url: '<?= base_url("/ajax/manpower-kpi") ?>',
                method: 'GET',
                dataType: 'json',
                success: function(resp) {
                    $('#kpiTotal').text(resp.kpi.total);
                    $('#kpiMan').text(resp.kpi.man);
                    $('#kpiWoman').text(resp.kpi.woman);

                    const currentGender = $('#mpGender').val();
                    const $gender = $('#mpGender').empty().append('<option value="">Select gender</option>');
                    (resp.gender || []).forEach(function(g) {
                        const label = g.gender.charAt(0).toUpperCase() + g.gender.slice(1);
                        $gender.append(`<option value="${g.idx}">${label}</option>`);
                    });
                    $gender.val(currentGender);

                    const currentRole = $('#mpRole').val();
                    const $role = $('#mpRole').empty().append('<option value="">Select role</option>');
                    (resp.roles || []).forEach(function(r) {
                        const label = r.job_title.charAt(0).toUpperCase() + r.job_title.slice(1)
                            + (r.abbreviation ? ' (' + r.abbreviation + ')' : '');
                        $role.append(`<option value="${r.idx}">${label}</option>`);
                    });
                    $role.val(currentRole);
                }
            });
        }
        loadKpiAndOptions();

        // ── Add ──────────────────────────────────────────────────────────
        $('#btnAddManpower').on('click', function() {
            $('#manpowerModalLabel').text('Add Manpower');
            $('#mpIdx').val('');
            $('#mpName, #mpEmployeeId, #mpPhone1, #mpPhone2, #mpEmergency').val('').removeClass('is-invalid');
            $('#mpGender, #mpRole').val('').removeClass('is-invalid');
            $('#mpRoleOptionalHint').hide();
            $('#mpError').addClass('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('manpowerModal')).show();
        });

        // ── Open edit ────────────────────────────────────────────────────
        $(document).on('click', '.btn-edit-manpower', function() {
            const $btn = $(this);
            $('#manpowerModalLabel').text('Edit Manpower');
            $('#mpIdx').val($btn.data('idx'));
            $('#mpName').val($btn.data('name')).removeClass('is-invalid');
            $('#mpEmployeeId').val($btn.data('employee-id')).removeClass('is-invalid');
            $('#mpGender').val($btn.data('gender-id')).removeClass('is-invalid');
            $('#mpRole').val($btn.data('role-id')).removeClass('is-invalid');
            $('#mpRoleOptionalHint').show();
            $('#mpPhone1').val($btn.data('phone1'));
            $('#mpPhone2').val($btn.data('phone2'));
            $('#mpEmergency').val($btn.data('emergency'));
            $('#mpError').addClass('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('manpowerModal')).show();
        });

        // ── Save (add or edit) ───────────────────────────────────────────
        $('#btnManpowerSave').on('click', function() {
            const $btn        = $(this).prop('disabled', true).text('Saving...');
            const idx         = $('#mpIdx').val();
            const name        = $('#mpName').val().trim();
            const employeeId  = $('#mpEmployeeId').val().trim();
            const gender      = $('#mpGender').val();
            const role        = $('#mpRole').val();
            const phone1      = $('#mpPhone1').val().trim();
            const phone2      = $('#mpPhone2').val().trim();
            const emergency   = $('#mpEmergency').val().trim();

            $('#mpName, #mpEmployeeId, #mpGender, #mpRole').removeClass('is-invalid');
            $('#mpError').addClass('d-none');

            let valid = true;
            if (!name)                          { $('#mpName').addClass('is-invalid');       valid = false; }
            if (!employeeId || !/^\d+$/.test(employeeId)) { $('#mpEmployeeId').addClass('is-invalid'); valid = false; }
            if (!gender)                        { $('#mpGender').addClass('is-invalid');     valid = false; }
            if (!idx && !role)                  { $('#mpRole').addClass('is-invalid');        valid = false; } // required on Add only
            if (!valid) { $btn.prop('disabled', false).text('Save'); return; }

            const payload = {
                name: name,
                employee_id: employeeId,
                gender: gender,
                role: role,
                first_phone_number: phone1,
                second_phone_number: phone2,
                emergency_contact_number: emergency
            };

            $.ajax({
                url: idx ? `<?= base_url('/manpower/update/') ?>${idx}` : '<?= base_url("/manpower/store") ?>',
                method: 'POST',
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
                data: JSON.stringify(payload),
                success: function(res) {
                    refreshCsrf(res.csrf_hash);
                    bootstrap.Modal.getInstance(document.getElementById('manpowerModal')).hide();
                    table.ajax.reload(null, false);
                    loadKpiAndOptions();
                },
                error: function(xhr) {
                    const resp = JSON.parse(xhr.responseText || '{}');
                    refreshCsrf(resp.csrf_hash);
                    $('#mpError').text(resp.message || 'Failed to save manpower record.').removeClass('d-none');
                },
                complete: function() { $btn.prop('disabled', false).text('Save'); }
            });
        });

        // ── Delete ───────────────────────────────────────────────────────
        let pendingDeleteIdx = null;

        $(document).on('click', '.btn-delete-manpower', function() {
            pendingDeleteIdx = $(this).data('idx');
            $('#deleteManpowerName').text($(this).data('name'));
            $('#deleteManpowerErrorWrap').addClass('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteManpowerModal')).show();
        });

        $('#btnDeleteManpowerConfirm').on('click', function() {
            if (!pendingDeleteIdx) return;
            const $btn = $(this).prop('disabled', true);

            $.ajax({
                url: `<?= base_url('/manpower/delete/') ?>${pendingDeleteIdx}`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
                success: function(res) {
                    refreshCsrf(res.csrf_hash);
                    bootstrap.Modal.getInstance(document.getElementById('deleteManpowerModal')).hide();
                    table.ajax.reload(null, false);
                    loadKpiAndOptions();
                },
                error: function(xhr) {
                    const resp = JSON.parse(xhr.responseText || '{}');
                    refreshCsrf(resp.csrf_hash);
                    $('#deleteManpowerError').text(resp.message || 'Failed to delete manpower record.');
                    $('#deleteManpowerErrorWrap').removeClass('d-none');
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        $('#deleteManpowerModal').on('hidden.bs.modal', function() { pendingDeleteIdx = null; });
    });
</script>
<?= $this->endSection(); ?>
