<?= $this->extend('layouts/main_layout') ?>

<?php
/**
 * @var string   $pageTitle
 * @var array[]  $rows
 * @var array[]  $rawMap
 * @var array[]  $checkParts
 * @var array[]  $spotPositions
 * @var array[]  $dangerCodes
 * @var array[]  $equipTypes
 */

// Merge display data + FK values into one JS-safe array
$jsRows = array_map(function ($r) use ($rawMap) {
    $raw = $rawMap[$r['idx']] ?? null;
    return [
        'idx'            => (int) $r['idx'],
        'abbr'           => $r['abbr']           ?? '',
        'check_part_idn' => $r['check_part_idn'] ?? '',
        'check_part_en'  => $r['check_part_en']  ?? '',
        'spot_position'  => $r['spot_position']  ?? '',
        'danger_tag'     => $r['danger_tag']     ?? '',
        'checking_part'  => $raw ? (int) $raw['checking_part']  : 0,
        'spot'           => $raw ? (int) $raw['spot']           : 0,
        'danger_tag_fk'  => $raw ? (int) $raw['danger_tag']     : 0,
        'equipment_type' => $raw ? (int) $raw['equipment_type'] : 0,
    ];
}, $rows);
?>

<?= $this->section('content') ?>

<?= csrf_field() ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">Checklist Configuration</h4>
            <p class="text-muted small mb-0">P2H checklist items per equipment type — <code>view_equipment_observed_list</code></p>
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
                    <thead class="table-light" id="tableHead">
                        <tr>
                            <th class="ps-4 sortable" data-col="abbr" style="min-width:90px;cursor:pointer;">
                                Type <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th style="min-width:160px;">Check Part (IDN)</th>
                            <th class="d-none d-md-table-cell" style="min-width:160px;">Check Part (EN)</th>
                            <th class="sortable" data-col="spot_position" style="min-width:100px;cursor:pointer;">
                                Spot <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="sortable" data-col="danger_tag" style="min-width:90px;cursor:pointer;">
                                Danger Tag <span class="sort-icon text-muted ms-1"></span>
                            </th>
                            <th class="text-center pe-4" style="min-width:70px;">Edit</th>
                        </tr>
                    </thead>
                    <tbody id="observedTbody"></tbody>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="editModalLabel">Edit Checklist Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">

                <input type="hidden" id="editIdx">

                <div class="mb-3">
                    <label for="editEquipType" class="form-label fw-semibold">Equipment Type</label>
                    <select id="editEquipType" class="form-select">
                        <?php foreach ($equipTypes as $et): ?>
                        <option value="<?= $et['idx'] ?>">
                            <?= esc($et['abbreviation'] ?: $et['code']) ?> — <?= esc($et['code']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="editCheckPart" class="form-label fw-semibold">Checking Part</label>
                    <select id="editCheckPart" class="form-select">
                        <?php foreach ($checkParts as $cp): ?>
                        <option value="<?= $cp['idx'] ?>">
                            <?= esc($cp['checking_part_idn']) ?> / <?= esc($cp['checking_part']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="editSpot" class="form-label fw-semibold">Spot Position</label>
                    <select id="editSpot" class="form-select">
                        <?php foreach ($spotPositions as $sp): ?>
                        <option value="<?= $sp['idx'] ?>"><?= esc($sp['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-1">
                    <label for="editDangerTag" class="form-label fw-semibold">Danger Tag</label>
                    <select id="editDangerTag" class="form-select">
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="addModalLabel">Add Checklist Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">

                <div class="mb-3">
                    <label for="addEquipType" class="form-label fw-semibold">Equipment Type</label>
                    <select id="addEquipType" class="form-select">
                        <?php foreach ($equipTypes as $et): ?>
                        <option value="<?= $et['idx'] ?>">
                            <?= esc($et['abbreviation'] ?: $et['code']) ?> — <?= esc($et['code']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="addCheckPart" class="form-label fw-semibold">Checking Part</label>
                    <select id="addCheckPart" class="form-select">
                        <?php foreach ($checkParts as $cp): ?>
                        <option value="<?= $cp['idx'] ?>">
                            <?= esc($cp['checking_part_idn']) ?> / <?= esc($cp['checking_part']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="addSpot" class="form-label fw-semibold">Spot Position</label>
                    <select id="addSpot" class="form-select">
                        <?php foreach ($spotPositions as $sp): ?>
                        <option value="<?= $sp['idx'] ?>"><?= esc($sp['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-1">
                    <label for="addDangerTag" class="form-label fw-semibold">Danger Tag</label>
                    <select id="addDangerTag" class="form-select">
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

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <input type="hidden" id="deleteIdx">
                <p class="mb-1">Delete this checklist item?</p>
                <p class="small text-muted mb-0" id="deleteLabel"></p>
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
    const DATA = <?= json_encode(array_values($jsRows)) ?>;

    // ── State ─────────────────────────────────────────────────────
    let sortCol        = 'abbr';
    let sortDir        = 'asc';
    let filterQ        = '';
    let page           = 1;
    let pageSize       = 20;
    const collapsedGroups = new Set();

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
            rows = rows.filter(r =>
                r.abbr.toLowerCase().includes(q)           ||
                r.check_part_idn.toLowerCase().includes(q) ||
                r.check_part_en.toLowerCase().includes(q)  ||
                r.spot_position.toLowerCase().includes(q)  ||
                r.danger_tag.toLowerCase().includes(q)
            );
        }

        rows = [...rows].sort(function (a, b) {
            const va = (a[sortCol] || '').toLowerCase();
            const vb = (b[sortCol] || '').toLowerCase();
            return sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });

        return rows;
    }

    // ── Render ────────────────────────────────────────────────────
    function render() {
        const all    = getRows();
        const total  = all.length;
        const ps     = pageSize || total;
        const pages  = ps ? Math.ceil(total / ps) : 1;
        if (page > pages) page = Math.max(1, pages);

        const slice  = ps ? all.slice((page - 1) * ps, page * ps) : all;

        // Group counts from full filtered set (not just current page slice)
        const groupCounts = {};
        if (sortCol === 'abbr') {
            all.forEach(function (r) {
                groupCounts[r.abbr] = (groupCounts[r.abbr] || 0) + 1;
            });
        }

        let html     = '';
        let prevAbbr = null;

        slice.forEach(function (r) {
            // Group header only when primary sort is type
            if (sortCol === 'abbr' && r.abbr !== prevAbbr) {
                prevAbbr = r.abbr;
                const isCollapsed = collapsedGroups.has(r.abbr);
                const chevron     = isCollapsed ? 'fa-chevron-right' : 'fa-chevron-down';
                const count       = groupCounts[r.abbr] || 0;
                html += `<tr class="table-secondary" style="cursor:pointer;" data-collapse-group="${esc(r.abbr)}">
                    <td colspan="6" class="ps-4 py-2 user-select-none">
                        <i class="fas ${chevron} me-2 small text-muted"></i>
                        <span class="fw-bold small text-uppercase" style="letter-spacing:.05em;">${esc(r.abbr)}</span>
                        <span class="text-muted small ms-2">(${count})</span>
                    </td>
                </tr>`;
            }
            const hidden = (sortCol === 'abbr' && collapsedGroups.has(r.abbr)) ? ' style="display:none;"' : '';
            html += `<tr${hidden}>
                <td class="ps-4 text-muted small">${esc(r.abbr)}</td>
                <td class="fw-semibold">${esc(r.check_part_idn)}</td>
                <td class="text-muted small d-none d-md-table-cell">${esc(r.check_part_en)}</td>
                <td><span class="badge bg-info-subtle text-info border border-info-subtle">${esc(r.spot_position)}</span></td>
                <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">${esc(r.danger_tag)}</span></td>
                <td class="text-center pe-4">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-edit" data-idx="${r.idx}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                data-idx="${r.idx}" data-abbr="${esc(r.abbr)}" data-label="${esc(r.check_part_idn)}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        if (!slice.length) {
            html = '<tr><td colspan="6" class="text-center text-muted py-5">No matching rows.</td></tr>';
        }

        document.getElementById('observedTbody').innerHTML = html;

        // Sort icons
        document.querySelectorAll('.sortable .sort-icon').forEach(function (el) {
            const col = el.closest('[data-col]').dataset.col;
            el.innerHTML = col === sortCol
                ? (sortDir === 'asc' ? '↑' : '↓')
                : '<span style="opacity:.3;">↕</span>';
        });

        // Pagination info
        const infoEl = document.getElementById('paginationInfo');
        if (infoEl) {
            if (!total) {
                infoEl.textContent = 'No results';
            } else if (!pageSize) {
                infoEl.textContent = 'Showing all ' + total + ' rows';
            } else {
                const s = (page - 1) * ps + 1;
                const e = Math.min(page * ps, total);
                infoEl.textContent = 'Showing ' + s + '–' + e + ' of ' + total + ' rows';
            }
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

    // ── Event: collapse group header ─────────────────────────────
    document.getElementById('observedTbody').addEventListener('click', function (e) {
        const header = e.target.closest('[data-collapse-group]');
        if (!header) return;
        const group = header.dataset.collapseGroup;
        if (collapsedGroups.has(group)) {
            collapsedGroups.delete(group);
        } else {
            collapsedGroups.add(group);
        }
        render();
    });

    // ── Event: open delete modal ─────────────────────────────────
    document.getElementById('observedTbody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;
        document.getElementById('deleteIdx').value = btn.dataset.idx;
        document.getElementById('deleteLabel').textContent =
            btn.dataset.abbr + ' — ' + btn.dataset.label;
        document.getElementById('deleteError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
    });

    // ── Event: confirm delete ─────────────────────────────────────
    document.getElementById('btnDeleteConfirm').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Deleting…';

        const idx = document.getElementById('deleteIdx').value;

        fetch('<?= base_url('/equipment-observed-list/delete/') ?>' + idx, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({}),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                // Remove from local DATA array and re-render (no full reload needed)
                const i = DATA.findIndex(function (x) { return x.idx === parseInt(idx, 10); });
                if (i !== -1) DATA.splice(i, 1);
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                render();
            } else {
                document.getElementById('deleteError').textContent = res.message || 'Delete failed.';
                document.getElementById('deleteError').classList.remove('d-none');
            }
        })
        .catch(function () {
            document.getElementById('deleteError').textContent = 'Network error.';
            document.getElementById('deleteError').classList.remove('d-none');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Delete';
        });
    });

    // ── Event: confirm add ────────────────────────────────────────
    document.getElementById('btnAddConfirm').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Adding…';

        fetch('<?= base_url('/equipment-observed-list/store') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                checking_part:  parseInt(document.getElementById('addCheckPart').value, 10),
                spot:           parseInt(document.getElementById('addSpot').value, 10),
                danger_tag:     parseInt(document.getElementById('addDangerTag').value, 10),
                equipment_type: parseInt(document.getElementById('addEquipType').value, 10),
            }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
                location.reload();
            } else {
                document.getElementById('addError').textContent = res.message || 'Add failed.';
                document.getElementById('addError').classList.remove('d-none');
            }
        })
        .catch(function () {
            document.getElementById('addError').textContent = 'Network error.';
            document.getElementById('addError').classList.remove('d-none');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Add';
        });
    });

    // ── Event: open edit modal ────────────────────────────────────
    document.getElementById('observedTbody').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit');
        if (!btn) return;
        const idx = parseInt(btn.dataset.idx, 10);
        const r   = DATA.find(function (x) { return x.idx === idx; });
        if (!r) return;

        document.getElementById('editIdx').value = r.idx;
        document.getElementById('editEquipType').value  = r.equipment_type;
        document.getElementById('editCheckPart').value  = r.checking_part;
        document.getElementById('editSpot').value        = r.spot;
        document.getElementById('editDangerTag').value   = r.danger_tag_fk;
        document.getElementById('editError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
    });

    // ── Event: save edit ──────────────────────────────────────────
    document.getElementById('btnEditConfirm').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        const idx = document.getElementById('editIdx').value;

        fetch('<?= base_url('/equipment-observed-list/update/') ?>' + idx, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                checking_part:  parseInt(document.getElementById('editCheckPart').value, 10),
                spot:           parseInt(document.getElementById('editSpot').value, 10),
                danger_tag:     parseInt(document.getElementById('editDangerTag').value, 10),
                equipment_type: parseInt(document.getElementById('editEquipType').value, 10),
            }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            refreshCsrf(res.csrf_hash);
            if (res.status === 'ok') {
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                location.reload();
            } else {
                document.getElementById('editError').textContent = res.message || 'Save failed.';
                document.getElementById('editError').classList.remove('d-none');
            }
        })
        .catch(function () {
            document.getElementById('editError').textContent = 'Network error.';
            document.getElementById('editError').classList.remove('d-none');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Save';
        });
    });

    // ── Initial render ────────────────────────────────────────────
    render();

})();
</script>
<?= $this->endSection() ?>
