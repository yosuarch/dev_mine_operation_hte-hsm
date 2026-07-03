<?php
/**
 * Server-rendered P2H checklist — returned by GET /operator-driver/psi-fetch-form
 * and injected into #psiFormItems by script-psi_form.
 *
 * Markup contract (relied on by script-psi_form and script-submit):
 *   #status-{idx}, #good-{idx}, #bad-{idx}, #note-wrap-{idx}, #note-{idx},
 *   name="psi-item-{idx}", textarea data-check-part, .psi-item-card
 *
 * @var array $items  Rows from view_psi_empty_from:
 *                    idx, check_part, hazard_code, hazard_code_description, spot_position
 */

if (empty($items)): ?>
    <p class="text-muted mb-0 small">No checklist found for this equipment.</p>
    <?php return; ?>
<?php endif;

$groups = [];
foreach ($items as $item) {
    $pos = $item['spot_position'] ?: 'GENERAL';
    $groups[$pos][] = $item;
}

$borderFor = static fn(?string $code): string => match ($code) {
    'AA'    => 'border-danger',
    'A'     => 'border-warning',
    default => 'border-success',
};
?>

<?php foreach ($groups as $position => $groupItems): ?>
    <div class="d-flex align-items-center justify-content-between mt-4 mb-2">
        <span class="text-uppercase fw-bold"
              style="font-size:0.68rem;letter-spacing:0.12em;color:var(--bs-secondary-color);">
            <?= esc($position) ?>
        </span>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
              style="font-size:0.6rem;">
            <?= count($groupItems) ?> items
        </span>
    </div>

    <?php foreach ($groupItems as $item): $idx = (int) $item['idx']; ?>
        <div class="psi-item-card position-relative d-flex flex-column p-3 mb-2 rounded-3 border-start border-3 <?= $borderFor($item['hazard_code']) ?>">

            <span id="status-<?= $idx ?>"
                  class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill"
                  style="font-size:0.6rem;background:var(--bs-secondary-bg);color:var(--bs-secondary-color);">
                pending
            </span>

            <p class="fw-semibold pe-4 mb-3 lh-sm" style="font-size:1rem;"><?= esc($item['check_part']) ?></p>

            <div class="btn-group w-100 mb-2">
                <input type="radio" class="btn-check" name="psi-item-<?= $idx ?>"
                       id="good-<?= $idx ?>" autocomplete="off">
                <label class="btn btn-outline-success py-2 fw-semibold"
                       for="good-<?= $idx ?>" style="font-size:0.9rem;">
                    &#10003;&nbsp; Normal
                </label>
                <input type="radio" class="btn-check" name="psi-item-<?= $idx ?>"
                       id="bad-<?= $idx ?>" autocomplete="off">
                <label class="btn btn-outline-danger py-2 fw-semibold"
                       for="bad-<?= $idx ?>" style="font-size:0.9rem;">
                    &#9888;&nbsp; Not-Normal
                </label>
            </div>

            <small class="text-muted" style="font-size:0.7rem;">
                <?= esc($item['hazard_code']) ?> &mdash; <?= esc($item['hazard_code_description']) ?>
            </small>

            <div id="note-wrap-<?= $idx ?>" class="mt-3 pt-2 border-top border-danger border-opacity-25"
                 style="display:none;">
                <small class="text-danger fw-bold d-block mb-1" style="font-size:0.72rem;">
                    &#9888; Note is required for not-normal items
                </small>
                <textarea
                    class="form-control"
                    id="note-<?= $idx ?>"
                    name="psi-note-<?= $idx ?>"
                    data-check-part="<?= esc($item['check_part'], 'attr') ?>"
                    placeholder="Required — describe the issue..."
                    rows="2"
                    style="font-size:0.95rem;resize:none;overflow:hidden;"></textarea>
                <div class="invalid-feedback">Please describe the issue before submitting.</div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
