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

<div class="mb-4 d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Hauling Activity</h4>
        <p class="text-muted mb-0">Trip log recorded by dumptruck drivers via the operator-driver mobile form.</p>
    </div>
    <div class="text-end">
        <span class="badge rounded-pill" id="liveBadge" style="background:var(--bs-success);">
            <i class="fas fa-circle" style="font-size:0.5rem;"></i> Live
        </span>
        <div class="text-muted small mt-1" id="liveUpdatedAt">Updated just now</div>
    </div>
</div>

<!-- ── KPI Cards — trips today, split by hauler class (view_equipment_class) ── -->
<div class="row g-3 mb-3">

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-truck-front text-primary" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiTripsAdt">&mdash;</div>
                    <div class="text-muted small mt-1">ADT Trips Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-truck text-info" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiTripsDump20">&mdash;</div>
                    <div class="text-muted small mt-1">Dumptruck 20 Ton Trips Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-truck text-success" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiTripsDump40">&mdash;</div>
                    <div class="text-muted small mt-1">Dumptruck 40 Ton Trips Today</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-4">

    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-secondary bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-users text-secondary" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiHaulersToday">&mdash;</div>
                    <div class="text-muted small mt-1">Active Haulers Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                    <i class="fas fa-mound text-warning" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" id="kpiTopMaterial">&mdash;</div>
                    <div class="text-muted small mt-1">Top Material Today</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Filters ───────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Date From</label>
                <input type="date" id="filterDateFrom" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Date To</label>
                <input type="date" id="filterDateTo" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Shift</label>
                <select id="filterShift" class="form-select form-select-sm">
                    <option value="">All shifts</option>
                    <option value="day">Day</option>
                    <option value="night">Night</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hauler</label>
                <select id="filterHauler" class="form-select form-select-sm">
                    <option value="">All haulers</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Material</label>
                <select id="filterMaterial" class="form-select form-select-sm">
                    <option value="">All materials</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="button" id="btnApplyFilters" class="btn btn-primary btn-sm flex-fill">Apply</button>
                <button type="button" id="btnResetFilters" class="btn btn-outline-secondary btn-sm flex-fill">Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Trip log ──────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="haulingActivityTable" class="table table-striped table-hover table-bordered w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Shift</th>
                        <th>Hauler</th>
                        <th>Driver</th>
                        <th>Loader</th>
                        <th>Material</th>
                        <th>Loading Area</th>
                        <th>Dumping Area</th>
                        <th>Driver Note</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
    $(document).ready(function() {

        const table = $('#haulingActivityTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '<?= base_url("/ajax-datatable/haulingactivitylist") ?>',
                data: function(d) {
                    d.date_from    = $('#filterDateFrom').val();
                    d.date_to      = $('#filterDateTo').val();
                    d.shift        = $('#filterShift').val();
                    d.hauler_id    = $('#filterHauler').val();
                    d.mat_category = $('#filterMaterial').val();
                }
            },
            order: [[1, 'desc'], [2, 'desc']],
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
                    title: 'Hauling Activity'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Hauling Activity'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Hauling Activity'
                }
            ],
            columnDefs: [
                { targets: 0, data: null, orderable: false, searchable: false, width: '4%',
                  render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; } },
                { targets: 1, data: 'date', width: '9%' },
                { targets: 2, data: 'time', width: '7%' },
                { targets: 3, data: 'shift', width: '7%',
                  render: function(data, type) {
                      if (type !== 'display') return data;
                      return data === 'night'
                          ? '<i class="fas fa-moon text-primary me-1"></i>Night'
                          : '<i class="fas fa-sun text-warning me-1"></i>Day';
                  } },
                { targets: 4, data: 'hauler_id', width: '9%' },
                { targets: 5, data: 'mp_hauler', width: '13%',
                  render: function(data) { return data || '<span class="text-muted">&mdash;</span>'; } },
                { targets: 6, data: 'loader_id', width: '8%',
                  render: function(data) { return data || '<span class="text-muted">&mdash;</span>'; } },
                { targets: 7, data: null, width: '12%',
                  render: function(data, type, row) {
                      if (type !== 'display') return row.mat_category || '';
                      const sub = row.sub_material ? ' &middot; ' + row.sub_material : '';
                      return (row.mat_category || '&mdash;') + sub;
                  } },
                { targets: 8, data: null, width: '14%',
                  render: function(data, type, row) {
                      if (type !== 'display') return row.mat_source || '';
                      const note = row.mat_source_note ? ' <span class="text-muted small">(' + row.mat_source_note + ')</span>' : '';
                      return (row.mat_source || '&mdash;') + note;
                  } },
                { targets: 9, data: null, width: '14%',
                  render: function(data, type, row) {
                      if (type !== 'display') return row.mat_destination || '';
                      const note = row.mat_dest_note ? ' <span class="text-muted small">(' + row.mat_dest_note + ')</span>' : '';
                      return (row.mat_destination || '&mdash;') + note;
                  } },
                { targets: 10, data: 'driver_note', width: '13%',
                  render: function(data) { return data ? data : '<span class="text-muted">&mdash;</span>'; } }
            ]
        });

        $('#btnApplyFilters').on('click', function() { table.ajax.reload(); });
        $('#btnResetFilters').on('click', function() {
            $('#filterDateFrom, #filterDateTo, #filterShift, #filterHauler, #filterMaterial').val('');
            table.ajax.reload();
        });

        // ── KPI cards + filter dropdown options ────────────────────────────
        function loadKpiAndFilters() {
            $.ajax({
                url: '<?= base_url("/ajax/hauling-activity-kpi") ?>',
                method: 'GET',
                dataType: 'json',
                success: function(resp) {
                    $('#kpiTripsAdt').text(resp.kpi.tripsByClass.adt_40ton);
                    $('#kpiTripsDump20').text(resp.kpi.tripsByClass.dump_truck_20ton);
                    $('#kpiTripsDump40').text(resp.kpi.tripsByClass.dump_truck_40ton);
                    $('#kpiHaulersToday').text(resp.kpi.haulersToday);
                    $('#kpiTopMaterial').text(resp.kpi.topMaterial ? resp.kpi.topMaterial : '—');

                    // Preserve whatever the admin already picked while refreshing the option list
                    const currentHauler   = $('#filterHauler').val();
                    const currentMaterial = $('#filterMaterial').val();

                    const $hauler = $('#filterHauler').empty().append('<option value="">All haulers</option>');
                    (resp.filters.haulers || []).forEach(function(h) {
                        $hauler.append(`<option value="${h.hauler_id}">${h.hauler_id}${h.mp_hauler ? ' — ' + h.mp_hauler : ''}</option>`);
                    });
                    $hauler.val(currentHauler);

                    const $material = $('#filterMaterial').empty().append('<option value="">All materials</option>');
                    (resp.filters.materials || []).forEach(function(m) {
                        if (!m.mat_category) return;
                        $material.append(`<option value="${m.mat_category}">${m.mat_category}</option>`);
                    });
                    $material.val(currentMaterial);

                    lastSeenCount = resp.count;
                }
            });
        }

        // ── Auto-refresh — cheap row-count poll, full refresh only on change ─
        let lastSeenCount  = null;
        let secondsSinceRefresh = 0;

        function refreshAll() {
            table.ajax.reload(null, false); // false = keep current page/position
            loadKpiAndFilters();
            secondsSinceRefresh = 0;
            $('#liveUpdatedAt').text('Updated just now');
        }

        function pollForChanges() {
            $.ajax({
                url: '<?= base_url("/ajax/hauling-activity-count") ?>',
                method: 'GET',
                dataType: 'json',
                success: function(resp) {
                    $('#liveBadge').css('background', 'var(--bs-success)');
                    if (lastSeenCount !== null && resp.count !== lastSeenCount) {
                        refreshAll();
                    } else {
                        lastSeenCount = resp.count;
                    }
                },
                error: function() {
                    $('#liveBadge').css('background', 'var(--bs-danger)');
                }
            });
        }

        loadKpiAndFilters();
        setInterval(pollForChanges, 8000);

        // "Updated Xs ago" ticker
        setInterval(function() {
            secondsSinceRefresh++;
            if (secondsSinceRefresh > 2) {
                $('#liveUpdatedAt').text('Updated ' + secondsSinceRefresh + 's ago');
            }
        }, 1000);
    });
</script>
<?= $this->endSection(); ?>
