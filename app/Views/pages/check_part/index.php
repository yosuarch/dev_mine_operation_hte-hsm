<?= $this->extend('layouts/main_layout') ?>

<?php
/**
 * @var string  $pageTitle
 * @var array[] $rows
 * @var array[] $spotPositions
 * @var array[] $dangerCodes
 */
?>

<?= $this->section('content') ?>

<?= csrf_field() ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">Check Part Management</h4>
            <p class="text-muted small mb-0">P2H checklist category items — <code>psi_unique_observed_item</code></p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="search" id="tableFilter" class="form-control form-control-sm flex-grow-1"
                   placeholder="Filter…" style="min-width:120px; max-width:200px;" autocomplete="off">
            <select id="pageSizeSelect" class="form-select form-select-sm" style="width:75px;">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="0">All</option>
            </select>
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3"
                    data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i> Add New
            </button>
        </div>
    </div>

    <!-- Table card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 sortable" data-col="checking_part_idn" style="min-width:180px;cursor:pointer;">
                                Name (IDN) <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="d-none d-md-table-cell sortable" data-col="checking_part" style="min-width:180px;cursor:pointer;">
                                Name (EN) <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="sortable" data-col="spot_position" style="min-width:90px;cursor:pointer;">
                                Spot <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="sortable" data-col="danger_tag_code" style="min-width:90px;cursor:pointer;">
                                Danger Tag <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="d-none d-lg-table-cell" style="min-width:140px;">Danger Level</th>
                            <th class="text-center pe-4" style="min-width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="checkPartTbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
        <div id="paginationInfo" class="text-muted small"></div>
        <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
    </div>

</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="addModalLabel">Add Check Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-3">
                    <label for="addIdn" class="form-label fw-semibold">Name (IDN)</label>
                    <input type="text" id="addIdn" class="form-control" placeholder="e.g. Level Oli Mesin">
                </div>
                <div class="mb-3">
                    <label for="addEn" class="form-label fw-semibold">Name (EN)</label>
                    <input type="text" id="addEn" class="form-control" placeholder="e.g. Engine Oil Level">
                </div>
                <div class="mb-3">
                    <label for="addSpot" class="form-label fw-semibold">Spot Position <span class="text-muted fw-normal">(optional)</span></label>
                    <select id="addSpot" class="form-select">
                        <option value="0">— None —</option>
                        <?php foreach ($spotPositions as $sp): ?>
                        <option value="<?= $sp['idx'] ?>"><?= esc($sp['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1">
                    <label for="addDangerTag" class="form-label fw-semibold">Danger Tag <span class="text-danger">*</span></label>
                    <select id="addDangerTag" class="form-select">
                        <option value="0">— Select —</option>
                        <?php foreach ($dangerCodes as $dc): ?>
                        <option value="<?= $dc['idx'] ?>"><?= esc($dc['code']) ?> — <?= esc($dc['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="addError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnAddConfirm" class="btn btn-primary fw-bold px-4">Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="editModalLabel">Edit Check Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <input type="hidden" id="editIdx">
                <div class="mb-3">
                    <label for="editIdn" class="form-label fw-semibold">Name (IDN)</label>
                    <input type="text" id="editIdn" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="editEn" class="form-label fw-semibold">Name (EN)</label>
                    <input type="text" id="editEn" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="editSpot" class="form-label fw-semibold">Spot Position <span class="text-muted fw-normal">(optional)</span></label>
                    <select id="editSpot" class="form-select">
                        <option value="0">— None —</option>
                        <?php foreach ($spotPositions as $sp): ?>
                        <option value="<?= $sp['idx'] ?>"><?= esc($sp['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1">
                    <label for="editDangerTag" class="form-label fw-semibold">Danger Tag <span class="text-danger">*</span></label>
                    <select id="editDangerTag" class="form-select">
                        <option value="0">— Select —</option>
                        <?php foreach ($dangerCodes as $dc): ?>
                        <option value="<?= $dc['idx'] ?>"><?= esc($dc['code']) ?> — <?= esc($dc['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="editError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnEditConfirm" class="btn btn-primary fw-bold px-4">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">Delete Check Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <input type="hidden" id="deleteIdx">
                <p class="mb-1">Delete this check part?</p>
                <p class="small text-muted mb-0" id="deleteLabel"></p>
                <p class="small text-warning mt-2 mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Blocked if still used in Checklist Config.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="deleteError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnDeleteConfirm" class="btn btn-danger fw-bold px-4">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('main-js') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
(function () {

    // ── Data ──────────────────────────────────────────────────────
    const DATA = <?= json_encode(array_values(array_map(function ($r) {
        return [
            'idx'               => (int) $r['idx'],
            'checking_part_idn' => $r['checking_part_idn']  ?? '',
            'checking_part'     => $r['checking_part']       ?? '',
            'spot_fk'           => (int) ($r['spot_fk']      ?? 0),
            'danger_tag_fk'     => (int) ($r['danger_tag_fk'] ?? 0),
            'spot_position'     => $r['spot_position']       ?? '',
            'danger_tag_code'   => $r['danger_tag_code']     ?? '',
            'danger_level'      => $r['danger_level']        ?? '',
        ];
    }, $rows))) ?>;

    // ── State ─────────────────────────────────────────────────────
    let sortCol  = 'checking_part_idn';
    let sortDir  = 'asc';
    let filterQ  = '';
    let page     = 1;
    let pageSize = 20;

    // ── Helpers ───────────────────────────────────────────────────
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function csrfToken() { return $('[name="csrf_test_name"]').val(); }
    function refreshCsrf(h) { if (h) $('[name="csrf_test_name"]').val(h); }

    // ── Filter + sort pipeline ────────────────────────────────────
    function getRows() {
        let rows = DATA;

        if (filterQ) {
            const q = filterQ.toLowerCase();
            rows = rows.filter(function (r) {
                return r.checking_part_idn.toLowerCase().includes(q) ||
                       r.checking_part.toLowerCase().includes(q)     ||
                       r.spot_position.toLowerCase().includes(q)     ||
                       r.danger_tag_code.toLowerCase().includes(q)   ||
                       r.danger_level.toLowerCase().includes(q);
            });
        }

        return [...rows].sort(function (a, b) {
            const va = (a[sortCol] || '').toLowerCase();
            const vb = (b[sortCol] || '').toLowerCase();
            return sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });
    }

    // ── Render ────────────────────────────────────────────────────
    function render() {
        const all   = getRows();
        const total = all.length;
        const ps    = pageSize || total;
        const pages = ps ? Math.ceil(total / ps) : 1;
        if (page > pages) page = Math.max(1, pages);

        const slice = ps ? all.slice((page - 1) * ps, page * ps) : all;

        let html = '';
        slice.forEach(function (r) {
            const spotBadge = r.spot_position
                ? `<span class="badge bg-info-subtle text-info border border-info-subtle">${esc(r.spot_position)}</span>`
                : `<span class="text-muted small">—</span>`;
            html += `<tr>
                <td class="ps-4 fw-semibold">${esc(r.checking_part_idn)}</td>
                <td class="text-muted small d-none d-md-table-cell">${esc(r.checking_part)}</td>
                <td>${spotBadge}</td>
                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">${esc(r.danger_tag_code)}</span></td>
                <td class="text-muted small d-none d-lg-table-cell">${esc(r.danger_level)}</td>
                <td class="text-center pe-4">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-edit"
                                data-idx="${r.idx}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                data-idx="${r.idx}"
                                data-label="${esc(r.checking_part_idn)} / ${esc(r.checking_part)}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        if (!slice.length) {
            html = '<tr><td colspan="6" class="text-center text-muted py-5">No matching rows.</td></tr>';
        }

        document.getElementById('checkPartTbody').innerHTML = html;

        // Sort icons
        document.querySelectorAll('.sortable .sort-icon').forEach(function (el) {
            const col = el.closest('[data-col]').dataset.col;
            el.innerHTML = col === sortCol
                ? (sortDir === 'asc' ? '↑' : '↓')
                : '<span style="opacity:.3;">↕</span>';
        });

        // Pagination info
        const infoEl = document.getElementById('paginationInfo');
        if (!total) {
            infoEl.textContent = 'No results';
        } else if (!pageSize) {
            infoEl.textContent = 'Showing all ' + total + ' rows';
        } else {
            const s = (page - 1) * ps + 1;
            const e = Math.min(page * ps, total);
            infoEl.textContent = 'Showing ' + s + '–' + e + ' of ' + total + ' rows';
        }

        // Pagination controls
        const pgUl = document.getElementById('pagination');
        pgUl.innerHTML = '';
        if (pages <= 1) return;

        function mkLi(label, p, disabled, active) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            li.innerHTML = '<a class="page-link" href="#">' + label + '</a>';
            if (!disabled && !active) {
                li.querySelector('a').addEventListener('click', function (e) {
                    e.preventDefault();
                    page = p;
                    render();
                });
            }
            return li;
        }

        pgUl.appendChild(mkLi('&laquo;', page - 1, page === 1, false));
        for (var p2 = 1; p2 <= pages; p2++) {
            pgUl.appendChild(mkLi(p2, p2, false, p2 === page));
        }
        pgUl.appendChild(mkLi('&raquo;', page + 1, page === pages, false));
    }

    // ── Event: sort headers ───────────────────────────────────────
    document.querySelectorAll('.sortable').forEach(function (th) {
        th.addEventListener('click', function () {
            const col = this.dataset.col;
            if (sortCol === col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortCol = col;
                sortDir = 'asc';
            }
            page = 1;
            render();
        });
    });

    // ── Event: filter ─────────────────────────────────────────────
    document.getElementById('tableFilter').addEventListener('input', function () {
        filterQ = this.value.trim();
        page    = 1;
        render();
    });

    // ── Event: page size ──────────────────────────────────────────
    document.getElementById('pageSizeSelect').addEventListener('change', function () {
        pageSize = parseInt(this.value, 10);
        page     = 1;
        render();
    });

    // ── Event: open edit modal ────────────────────────────────────
    document.getElementById('checkPartTbody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit');
        if (!btn) return;
        const r = DATA.find(function (x) { return x.idx === parseInt(btn.dataset.idx, 10); });
        if (!r) return;
        document.getElementById('editIdx').value      = r.idx;
        document.getElementById('editIdn').value      = r.checking_part_idn;
        document.getElementById('editEn').value       = r.checking_part;
        document.getElementById('editSpot').value     = r.spot_fk;
        document.getElementById('editDangerTag').value = r.danger_tag_fk;
        document.getElementById('editError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
    });

    // ── Event: open delete modal ──────────────────────────────────
    document.getElementById('checkPartTbody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;
        document.getElementById('deleteIdx').value         = btn.dataset.idx;
        document.getElementById('deleteLabel').textContent = btn.dataset.label;
        document.getElementById('deleteError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
    });

    // ── Event: confirm add ────────────────────────────────────────
    document.getElementById('btnAddConfirm').addEventListener('click', function () {
        const btn   = this;
        const errEl = document.getElementById('addError');
        const idn   = document.getElementById('addIdn').value.trim();
        const en    = document.getElementById('addEn').value.trim();
        const spot  = parseInt(document.getElementById('addSpot').value, 10);
        const dtag  = parseInt(document.getElementById('addDangerTag').value, 10);

        if (!idn || !en || !dtag) {
            errEl.textContent = 'Name (IDN), Name (EN), and Danger Tag are required.';
            errEl.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Adding…';
        errEl.classList.add('d-none');

        fetch('<?= base_url('/check-part/store') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ checking_part_idn: idn, checking_part: en, spot: spot, danger_tag: dtag }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
                location.reload();
            } else {
                errEl.textContent = res.message || 'Add failed.';
                errEl.classList.remove('d-none');
            }
        })
        .catch(function () { errEl.textContent = 'Network error.'; errEl.classList.remove('d-none'); })
        .finally(function () { btn.disabled = false; btn.textContent = 'Add'; });
    });

    // ── Event: confirm edit ───────────────────────────────────────
    document.getElementById('btnEditConfirm').addEventListener('click', function () {
        const btn   = this;
        const errEl = document.getElementById('editError');
        const idx   = document.getElementById('editIdx').value;
        const idn   = document.getElementById('editIdn').value.trim();
        const en    = document.getElementById('editEn').value.trim();
        const spot  = parseInt(document.getElementById('editSpot').value, 10);
        const dtag  = parseInt(document.getElementById('editDangerTag').value, 10);

        if (!idn || !en || !dtag) {
            errEl.textContent = 'Name (IDN), Name (EN), and Danger Tag are required.';
            errEl.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Saving…';
        errEl.classList.add('d-none');

        fetch('<?= base_url('/check-part/update/') ?>' + idx, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ checking_part_idn: idn, checking_part: en, spot: spot, danger_tag: dtag }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                // Patch in-memory DATA and re-render without a full reload
                const r = DATA.find(function (x) { return x.idx === parseInt(idx, 10); });
                if (r) {
                    r.checking_part_idn = idn;
                    r.checking_part     = en;
                    r.spot_fk           = spot;
                    r.danger_tag_fk     = dtag;
                    // Update display values from the select option text
                    const sOpt = document.getElementById('editSpot').selectedOptions[0];
                    const dOpt = document.getElementById('editDangerTag').selectedOptions[0];
                    r.spot_position   = spot ? sOpt.textContent.trim() : '';
                    r.danger_tag_code = dOpt.textContent.split('—')[0].trim();
                    r.danger_level    = dOpt.textContent.split('—')[1] ? dOpt.textContent.split('—')[1].trim() : '';
                }
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                render();
            } else {
                errEl.textContent = res.message || 'Save failed.';
                errEl.classList.remove('d-none');
            }
        })
        .catch(function () { errEl.textContent = 'Network error.'; errEl.classList.remove('d-none'); })
        .finally(function () { btn.disabled = false; btn.textContent = 'Save'; });
    });

    // ── Event: confirm delete ─────────────────────────────────────
    document.getElementById('btnDeleteConfirm').addEventListener('click', function () {
        const btn   = this;
        const errEl = document.getElementById('deleteError');
        const idx   = document.getElementById('deleteIdx').value;

        btn.disabled = true;
        btn.textContent = 'Deleting…';
        errEl.classList.add('d-none');

        fetch('<?= base_url('/check-part/delete/') ?>' + idx, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({}),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                const i = DATA.findIndex(function (x) { return x.idx === parseInt(idx, 10); });
                if (i !== -1) DATA.splice(i, 1);
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                render();
            } else {
                errEl.textContent = res.message || 'Delete failed.';
                errEl.classList.remove('d-none');
            }
        })
        .catch(function () { errEl.textContent = 'Network error.'; errEl.classList.remove('d-none'); })
        .finally(function () { btn.disabled = false; btn.textContent = 'Delete'; });
    });

    // ── Initial render ────────────────────────────────────────────
    render();

})();
</script>
<?= $this->endSection() ?>
