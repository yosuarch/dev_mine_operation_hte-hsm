<?php
/**
 * Trip list of the SHIFT ACTIVITY card — rendered inside partial/open-shift.php
 * and re-rendered by POST /operator-driver/submit-activity and /undo-activity
 * (injected into .dt-trip-list by script-activity).
 *
 * data-* attributes carry raw FK values so "Same as last trip" can prefill the
 * form without another request.
 *
 * @var array $activities  Rows from fetchDumptruckActivities()
 */

$prettify = static fn(?string $v): string => ($v === null || $v === '')
    ? '&mdash;'
    : esc(ucwords(str_replace('_', ' ', $v)));
?>
<?php if (empty($activities)): ?>
    <p class="text-muted small mb-0">No hauling activity recorded this shift.</p>
<?php else: ?>
    <?php foreach ($activities as $a): ?>
        <div class="p-3 rounded-3 dt-trip-row" style="background:var(--bs-secondary-bg);font-size:0.9rem;"
             data-trip-idx="<?= esc($a['idx'], 'attr') ?>"
             data-loader="<?= esc($a['loader_idx'] ?? '', 'attr') ?>"
             data-mat-cat="<?= esc($a['mat_cat_idx'] ?? '', 'attr') ?>"
             data-sub-mat="<?= esc($a['sub_mat_idx'] ?? '', 'attr') ?>"
             data-from="<?= esc($a['from_idx'] ?? '', 'attr') ?>"
             data-from-note="<?= esc($a['mat_source_note'] ?? '', 'attr') ?>"
             data-dest="<?= esc($a['dest_idx'] ?? '', 'attr') ?>"
             data-dest-note="<?= esc($a['mat_dest_note'] ?? '', 'attr') ?>"
             data-material-note="<?= esc($a['material_note'] ?? '', 'attr') ?>"
             data-driver-note="<?= esc($a['driver_note'] ?? '', 'attr') ?>">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold" style="font-size:0.8rem;">
                    <i class="far fa-clock me-1"></i><?= esc(substr($a['time'], 0, 5)) ?>
                    <?php if (!empty($a['loader_label'])): ?>
                        <span class="text-muted fw-normal ms-2" style="font-size:0.7rem;">
                            <i class="fas fa-truck-loading me-1"></i><?= esc($a['loader_label']) ?>
                        </span>
                    <?php endif; ?>
                </span>
                <span class="d-flex align-items-center gap-3">
                    <span class="text-muted text-end" style="font-size:0.7rem;">
                        <?= $prettify($a['mat_category']) ?> &middot; <?= $prettify($a['sub_material']) ?>
                    </span>
                    <button type="button" class="btn btn-link text-danger p-0 btn-undo-trip"
                            style="font-size:0.7rem;" title="Remove this trip">
                        <i class="fas fa-rotate-left me-1"></i>Undo
                    </button>
                </span>
            </div>

            <div class="d-flex flex-column gap-1">
                <div>
                    <span class="text-muted" style="font-size:0.7rem;">FROM</span>
                    <div class="fw-semibold">
                        <?= $prettify($a['mat_source']) ?>
                        <?php if (!empty($a['mat_source_note'])): ?>
                            <span class="text-muted fw-normal small">(<?= esc($a['mat_source_note']) ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <span class="text-muted" style="font-size:0.7rem;">
                        <i class="fas fa-arrow-down me-1" style="font-size:0.6rem;"></i>DUMPING AREA
                    </span>
                    <div class="fw-semibold">
                        <?= $prettify($a['mat_destination']) ?>
                        <?php if (!empty($a['mat_dest_note'])): ?>
                            <span class="text-muted fw-normal small">(<?= esc($a['mat_dest_note']) ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($a['material_note'])): ?>
                <div class="text-muted small mt-2">
                    <span style="font-size:0.7rem;">MATERIAL NOTE</span>
                    <div class="fst-italic"><?= esc($a['material_note']) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($a['driver_note'])): ?>
                <div class="text-muted small mt-2 pt-2 border-top border-opacity-25">
                    <span style="font-size:0.7rem;">DRIVER NOTE</span>
                    <div class="fst-italic">&ldquo;<?= esc($a['driver_note']) ?>&rdquo;</div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
