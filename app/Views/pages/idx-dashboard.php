<?= $this->extend('layouts/main_layout') ?>

<?php
/**
 * @var string   $pageTitle
 * @var string   $today
 * @var string   $todayLabel
 * @var string[] $weekDates
 * @var string[] $dateLabels
 * @var int      $todayWh
 * @var int      $todayPsi
 * @var int      $todayEquip
 * @var int[]    $whTrend
 * @var int[]    $psiTrend
 * @var array[]  $equipList
 * @var array[]  $whGrid
 */
?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <p class="text-muted small mb-0"><?= esc($todayLabel) ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-subtle text-success border border-success-subtle" id="liveIndicator">
                <i class="fas fa-circle me-1" style="font-size:.45rem;vertical-align:middle;"></i>Live
            </span>
            <div class="text-muted mt-1" style="font-size:.72rem;" id="lastUpdatedText">Auto-refreshes every 30s</div>
        </div>
    </div>

    <!-- ── KPI Cards ─────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-file-alt text-primary" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" id="kpiWh"><?= $todayWh ?></div>
                        <div class="text-muted small mt-1">P2H Submitted Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-triangle-exclamation text-warning" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" id="kpiPsi"><?= $todayPsi ?></div>
                        <div class="text-muted small mt-1">P2H with Issues Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 flex-shrink-0">
                        <i class="fas fa-truck text-success" style="font-size:1.4rem;width:24px;text-align:center;"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1" id="kpiEquip"><?= $todayEquip ?></div>
                        <div class="text-muted small mt-1">Active Equipment Today</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Charts ────────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-1">P2H Submissions</h6>
                    <p class="text-muted small mb-3">Total P2H forms submitted per active day</p>
                    <canvas id="chartWh" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-1">P2H with Issues</h6>
                    <p class="text-muted small mb-3">Submissions with at least one not-normal item per active day</p>
                    <canvas id="chartPsi" height="180"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Equipment Activity Grid ───────────────────────────────── -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-semibold mb-1">Equipment Activity</h6>
                    <p class="text-muted small mb-0">P2H submissions per equipment — last 7 active days</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small mb-0 flex-shrink-0">Rows per page:</label>
                    <select id="pageSizeSelect" class="form-select form-select-sm" style="width:70px;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" style="min-width:600px;">
                    <thead class="table-light" id="equipTableHead">
                        <tr>
                            <th class="fw-semibold" style="min-width:90px;">Equipment</th>
                            <?php foreach ($weekDates as $d): ?>
                            <th class="text-center fw-semibold <?= $d === $today ? 'table-primary' : '' ?>"
                                style="min-width:72px;">
                                <?= date('D', strtotime($d)) ?><br>
                                <span class="fw-normal" style="font-size:.75rem;"><?= date('d M', strtotime($d)) ?></span>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="equipTableBody">
                        <?php if (empty($equipList)): ?>
                        <tr id="equipEmptyRow">
                            <td colspan="<?= count($weekDates) + 1 ?>" class="text-center text-muted py-4">
                                No equipment activity found.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($equipList as $eq): ?>
                        <tr class="equip-row">
                            <td class="fw-semibold"><?= esc($eq['label']) ?></td>
                            <?php foreach ($weekDates as $d):
                                $cnt = $whGrid[$eq['idx']][$d] ?? 0;
                            ?>
                            <td class="text-center <?= $d === $today ? 'table-primary' : '' ?>">
                                <?php if ($cnt > 0): ?>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle"
                                      style="min-width:28px;"><?= $cnt ?></span>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
                <div id="equipPaginationInfo" class="text-muted small"></div>
                <nav><ul class="pagination pagination-sm mb-0" id="equipPagination"></ul></nav>
            </div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('main-js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
(function () {

    // ── Helpers ───────────────────────────────────────────────────
    const isDark  = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridClr = () => isDark() ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
    const tickClr = () => isDark() ? '#94a3b8' : '#6c757d';

    function makeOpts() {
        return {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { grid: { color: gridClr() }, ticks: { color: tickClr(), font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { color: tickClr(), precision: 0, font: { size: 11 } }, grid: { color: gridClr() } },
            },
        };
    }

    // ── Charts (initial render) ───────────────────────────────────
    const chartWh = new Chart(document.getElementById('chartWh'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($dateLabels) ?>,
            datasets: [{ label: 'P2H Submitted', data: <?= json_encode($whTrend) ?>,
                backgroundColor: 'rgba(59,130,246,.75)', borderColor: 'rgba(59,130,246,1)',
                borderWidth: 1, borderRadius: 4 }],
        },
        options: makeOpts(),
    });

    const chartPsi = new Chart(document.getElementById('chartPsi'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($dateLabels) ?>,
            datasets: [{ label: 'P2H with Issues', data: <?= json_encode($psiTrend) ?>,
                backgroundColor: 'rgba(234,179,8,.70)', borderColor: 'rgba(234,179,8,1)',
                borderWidth: 1, borderRadius: 4 }],
        },
        options: makeOpts(),
    });

    new MutationObserver(function () {
        [chartWh, chartPsi].forEach(function (ch) {
            ch.options.scales.x.grid.color  = gridClr();
            ch.options.scales.x.ticks.color = tickClr();
            ch.options.scales.y.grid.color  = gridClr();
            ch.options.scales.y.ticks.color = tickClr();
            ch.update();
        });
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

    // ── Pagination ────────────────────────────────────────────────
    let currentPage = 1;
    let pageSize    = 10;

    function renderPage() {
        const rows  = document.querySelectorAll('#equipTableBody .equip-row');
        const info  = document.getElementById('equipPaginationInfo');
        const pgUl  = document.getElementById('equipPagination');
        const total = rows.length;

        if (!total) {
            if (info)  info.textContent  = '';
            if (pgUl)  pgUl.innerHTML    = '';
            return;
        }

        const pages = Math.ceil(total / pageSize);
        if (currentPage > pages) currentPage = pages;

        const start = (currentPage - 1) * pageSize;
        const end   = Math.min(start + pageSize, total);

        rows.forEach(function (row, i) {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });

        if (info) info.textContent = 'Showing ' + (start + 1) + '–' + end + ' of ' + total + ' equipment';

        if (!pgUl) return;
        pgUl.innerHTML = '';

        function mkLi(label, page, disabled, active) {
            var el = document.createElement('li');
            el.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            el.innerHTML = '<a class="page-link" href="#">' + label + '</a>';
            if (!disabled && !active) {
                el.querySelector('a').addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage = page;
                    renderPage();
                });
            }
            return el;
        }

        pgUl.appendChild(mkLi('&laquo;', currentPage - 1, currentPage === 1,     false));
        for (var p = 1; p <= pages; p++) pgUl.appendChild(mkLi(p, p, false, p === currentPage));
        pgUl.appendChild(mkLi('&raquo;', currentPage + 1, currentPage === pages, false));
    }

    document.getElementById('pageSizeSelect').addEventListener('change', function () {
        pageSize    = parseInt(this.value, 10);
        currentPage = 1;
        renderPage();
    });

    renderPage();

    // ── Table rebuild from AJAX data ──────────────────────────────
    function rebuildTable(data) {
        // Rebuild header
        var thead = document.getElementById('equipTableHead');
        var headRow = '<tr><th class="fw-semibold" style="min-width:90px;">Equipment</th>';
        data.weekDates.forEach(function (d, i) {
            var isToday = d === data.today;
            var dayName = data.dateLabels[i].split(' ')[1]
                ? data.dateLabels[i]
                : data.dateLabels[i];
            headRow += '<th class="text-center fw-semibold' + (isToday ? ' table-primary' : '') +
                '" style="min-width:72px;">' + formatDayName(d) + '<br>' +
                '<span class="fw-normal" style="font-size:.75rem;">' + data.dateLabels[i] + '</span></th>';
        });
        headRow += '</tr>';
        thead.innerHTML = headRow;

        // Rebuild body
        var tbody = document.getElementById('equipTableBody');
        if (!data.equipList || !data.equipList.length) {
            tbody.innerHTML = '<tr id="equipEmptyRow"><td colspan="' + (data.weekDates.length + 1) +
                '" class="text-center text-muted py-4">No equipment activity found.</td></tr>';
            renderPage();
            return;
        }

        var html = '';
        data.equipList.forEach(function (eq) {
            html += '<tr class="equip-row"><td class="fw-semibold">' + escHtml(eq.label) + '</td>';
            data.weekDates.forEach(function (d) {
                var isToday = d === data.today;
                var grid    = data.whGrid[eq.idx] || {};
                var cnt     = grid[d] || 0;
                html += '<td class="text-center' + (isToday ? ' table-primary' : '') + '">';
                if (cnt > 0) {
                    html += '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="min-width:28px;">' + cnt + '</span>';
                } else {
                    html += '<span class="text-muted" style="font-size:.8rem;">—</span>';
                }
                html += '</td>';
            });
            html += '</tr>';
        });
        tbody.innerHTML = html;

        currentPage = 1;
        renderPage();
    }

    // ── Utilities ─────────────────────────────────────────────────
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function formatDayName(dateStr) {
        var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        return days[new Date(dateStr + 'T00:00:00').getDay()];
    }

    // ── Live refresh ──────────────────────────────────────────────
    var refreshTimer   = null;
    var lastRefreshed  = null;
    var tickTimer      = null;
    var indicator      = document.getElementById('liveIndicator');
    var lastUpdatedTxt = document.getElementById('lastUpdatedText');

    function tickLastUpdated() {
        if (!lastRefreshed || !lastUpdatedTxt) return;
        var sec = Math.round((Date.now() - lastRefreshed) / 1000);
        lastUpdatedTxt.textContent = 'Updated ' + sec + 's ago';
    }

    function refresh() {
        fetch('<?= base_url('/ajax/dashboard-data') ?>')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                // KPI cards
                document.getElementById('kpiWh').textContent    = data.todayWh;
                document.getElementById('kpiPsi').textContent   = data.todayPsi;
                document.getElementById('kpiEquip').textContent = data.todayEquip;

                // Charts
                chartWh.data.labels              = data.dateLabels;
                chartWh.data.datasets[0].data    = data.whTrend;
                chartWh.update();

                chartPsi.data.labels             = data.dateLabels;
                chartPsi.data.datasets[0].data   = data.psiTrend;
                chartPsi.update();

                // Equipment table
                rebuildTable(data);

                // Indicator
                lastRefreshed = Date.now();
                if (indicator) {
                    indicator.className = 'badge bg-success-subtle text-success border border-success-subtle';
                }
                if (tickTimer) clearInterval(tickTimer);
                tickTimer = setInterval(tickLastUpdated, 1000);
                tickLastUpdated();
            })
            .catch(function () {
                if (indicator) {
                    indicator.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                }
                if (lastUpdatedTxt) lastUpdatedTxt.textContent = 'Refresh failed';
            });
    }

    refreshTimer = setInterval(refresh, 30000);

})();
</script>
<?= $this->endSection() ?>
