<?php
/**
 * Server-rendered OPEN SHIFT section — returned by GET /operator-driver/check-open-shift
 * and injected into #openShiftSection by script-operator_driver_name_id.
 *
 * @var array $openShifts   Sessions from Redis, each with: employee_id, equipment_idx,
 *                          equipment_label, date, shift, hourmeter_start,
 *                          is_dumptruck (bool), activities (hauling trips, dumptruck only)
 * @var array|null $formLookups  Select options for the new-trip form (loaders,
 *                          matCats, subMats, sources) — null when no dumptruck shift
 */
$multiple = count($openShifts) > 1;
?>

<?php if ($multiple): ?>
    <!-- Equipment picker — operator has 2+ open shifts -->
    <div class="card border-0 shadow-sm open-shift-picker">
        <div class="card-body p-4">
            <p class="section-label mb-1">OPEN SHIFTS</p>
            <h5 class="card-title-main mb-1">Select Equipment to Close</h5>
            <p class="text-muted mb-4" style="font-size:0.9rem;">
                You have multiple open shifts. Pick the equipment you finished using.
            </p>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($openShifts as $i => $s): ?>
                    <button type="button"
                            class="btn btn-outline-primary text-start py-3 px-4 open-shift-pick-btn"
                            data-target="osShift-<?= $i ?>">
                        <div class="fw-bold"><?= esc($s['equipment_label']) ?></div>
                        <div class="text-muted small">
                            <?= esc($s['date']) ?> &middot; <?= $s['shift'] == 1 ? 'Day' : 'Night' ?> shift
                            &middot; HM Start: <?= esc($s['hourmeter_start']) ?>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($openShifts as $i => $s): ?>
<!-- Per-shift detail: activity card + HM End form, hidden behind the picker when multiple -->
<div class="open-shift-detail d-flex flex-column gap-4" id="osShift-<?= $i ?>"
     data-employee-id="<?= esc($s['employee_id'], 'attr') ?>"
     data-equipment-idx="<?= esc($s['equipment_idx'], 'attr') ?>"
     <?= $multiple ? 'style="display:none;"' : '' ?>>

    <?php if (!empty($s['is_dumptruck'])): ?>
        <!-- Hauling activity for this shift (dumptruck types only) -->
        <div class="card border-0 shadow-sm dt-activity-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <p class="section-label mb-0">SHIFT ACTIVITY</p>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle dt-trip-count"
                          style="font-size:0.65rem;">
                        <?= count($s['activities']) ?> trip<?= count($s['activities']) === 1 ? '' : 's' ?>
                    </span>
                </div>
                <h5 class="card-title-main mb-3">Hauling Record</h5>

                <div class="d-flex flex-column gap-2 dt-trip-list"
                     style="max-height:320px;overflow-y:auto;overscroll-behavior:contain;">
                    <?= view('pages/psi/mobile/partial/activity-trips', ['activities' => $s['activities']]) ?>
                </div>

                <button type="button" class="btn btn-outline-primary w-100 mt-3 py-2 fw-semibold btn-add-trip">
                    <i class="fas fa-plus me-2"></i>Add Trip
                </button>

                <!-- New trip form (server-rendered, toggled by Add Trip) -->
                <div class="dt-trip-form mt-3 pt-3 border-top" style="display:none;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold">New Trip</span>
                        <button type="button" class="btn btn-link btn-sm text-secondary p-0 btn-same-last"
                                <?= empty($s['activities']) ? 'style="display:none;"' : '' ?>>
                            <i class="fas fa-clone me-1"></i>Same as last trip
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Time</label>
                        <input type="time" class="form-control form-control-lg dt-time"
                               value="<?= (new DateTime('now', new DateTimeZone('Asia/Jayapura')))->format('H:i') ?>">
                        <div class="invalid-feedback">Time is required.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">
                            Loader <span class="text-muted fw-normal">(leave empty if unknown)</span>
                        </label>
                        <div class="combo position-relative dt-loader-combo">
                            <span class="combo-search-icon"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control form-control-lg combo-input dt-loader-search"
                                   placeholder="Unknown / no loader" autocomplete="off"
                                   role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list">
                            <button type="button" class="combo-clear dt-loader-clear" aria-label="Clear selection"
                                    tabindex="-1" style="display:none;">
                                <i class="fas fa-xmark"></i>
                            </button>
                            <span class="combo-chevron"><i class="fas fa-chevron-down"></i></span>
                            <div class="combo-menu dt-loader-dropdown" role="listbox" aria-label="Loader"
                                 style="display:none;">
                                <div class="combo-list"></div>
                            </div>
                        </div>
                        <input type="hidden" class="dt-loader" value="">
                        <!-- Data pool for the combo above (never shown) -->
                        <select class="dt-loader-pool d-none" tabindex="-1" aria-hidden="true">
                            <?php foreach ($formLookups['loaders'] ?? [] as $l): ?>
                                <option value="<?= esc($l['idx'], 'attr') ?>"><?= esc($l['equipment_id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Material</label>
                        <div class="d-flex flex-wrap gap-2 dt-mat-cat">
                            <?php foreach ($formLookups['matCats'] ?? [] as $mc): ?>
                                <input type="radio" class="btn-check" name="dtMatCat-<?= $i ?>"
                                       id="dtMatCat-<?= $i ?>-<?= $mc['idx'] ?>" value="<?= esc($mc['idx'], 'attr') ?>"
                                       autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm px-3"
                                       for="dtMatCat-<?= $i ?>-<?= $mc['idx'] ?>">
                                    <?= esc(ucwords($mc['material'])) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-danger small mt-1 d-none dt-mat-required">Please select a material.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">
                            Sub Material <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <div class="combo position-relative dt-submat-combo">
                            <span class="combo-search-icon"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control form-control-lg combo-input dt-submat-search"
                                   placeholder="Select material first" autocomplete="off" disabled
                                   role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list">
                            <button type="button" class="combo-clear dt-submat-clear" aria-label="Clear selection"
                                    tabindex="-1" style="display:none;">
                                <i class="fas fa-xmark"></i>
                            </button>
                            <span class="combo-chevron"><i class="fas fa-chevron-down"></i></span>
                            <div class="combo-menu dt-submat-dropdown" role="listbox" aria-label="Sub material"
                                 style="display:none;">
                                <div class="combo-list"></div>
                            </div>
                        </div>
                        <input type="hidden" class="dt-submat" value="">
                        <!-- Full option pool for the cascading filter (never shown) -->
                        <select class="dt-submat-all d-none" tabindex="-1" aria-hidden="true">
                            <?php foreach ($formLookups['subMats'] ?? [] as $sm): ?>
                                <option value="<?= esc($sm['idx'], 'attr') ?>"
                                        data-material="<?= esc($sm['material'], 'attr') ?>">
                                    <?= esc(ucwords(str_replace('_', ' ', $sm['sub_material']))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">
                            Material Note <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea class="form-control dt-material-note" rows="2"
                                  placeholder="Anything about the material..."
                                  style="font-size:0.95rem;resize:none;"></textarea>
                    </div>

                    <!-- Shared data pool for both Loaded From and Dumping Area combos (never shown) -->
                    <select class="dt-source-pool d-none" tabindex="-1" aria-hidden="true">
                        <?php foreach ($formLookups['sources'] ?? [] as $src): ?>
                            <option value="<?= esc($src['idx'], 'attr') ?>">
                                <?= esc(ucwords(str_replace('_', ' ', $src['source']))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Loaded From</label>
                        <div class="combo position-relative dt-from-combo">
                            <span class="combo-search-icon"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control form-control-lg combo-input dt-from-search"
                                   placeholder="Select loading area" autocomplete="off"
                                   role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list">
                            <button type="button" class="combo-clear dt-from-clear" aria-label="Clear selection"
                                    tabindex="-1" style="display:none;">
                                <i class="fas fa-xmark"></i>
                            </button>
                            <span class="combo-chevron"><i class="fas fa-chevron-down"></i></span>
                            <div class="combo-menu dt-from-dropdown" role="listbox" aria-label="Loading area"
                                 style="display:none;">
                                <div class="combo-list"></div>
                            </div>
                            <div class="invalid-feedback">Loading area is required.</div>
                        </div>
                        <input type="hidden" class="dt-from" value="">
                        <input type="text" class="form-control mt-2 dt-from-note" maxlength="64"
                               placeholder="Loading area note (optional)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">Dumping Area</label>
                        <div class="combo position-relative dt-dest-combo">
                            <span class="combo-search-icon"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control form-control-lg combo-input dt-dest-search"
                                   placeholder="Select dumping area" autocomplete="off"
                                   role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list">
                            <button type="button" class="combo-clear dt-dest-clear" aria-label="Clear selection"
                                    tabindex="-1" style="display:none;">
                                <i class="fas fa-xmark"></i>
                            </button>
                            <span class="combo-chevron"><i class="fas fa-chevron-down"></i></span>
                            <div class="combo-menu dt-dest-dropdown" role="listbox" aria-label="Dumping area"
                                 style="display:none;">
                                <div class="combo-list"></div>
                            </div>
                            <div class="invalid-feedback">Dumping area is required.</div>
                        </div>
                        <input type="hidden" class="dt-dest" value="">
                        <div class="text-warning small mt-1 d-none dt-same-warning">
                            <i class="fas fa-triangle-exclamation me-1"></i>Loading and dumping area are the same.
                        </div>
                        <input type="text" class="form-control mt-2 dt-dest-note" maxlength="64"
                               placeholder="Dumping area note (optional)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small mb-1">
                            Driver Note <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea class="form-control dt-driver-note" rows="2"
                                  placeholder="Anything else about this trip..."
                                  style="font-size:0.95rem;resize:none;"></textarea>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold btn-save-trip">
                        Save Trip
                    </button>
                    <div class="alert alert-danger mt-2 mb-0 small d-none dt-trip-error"></div>
                    <div class="text-success small fw-semibold mt-2 text-center d-none dt-trip-saved">
                        <i class="fas fa-circle-check me-1"></i>Trip saved
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- HM End form -->
    <div class="card border-0 shadow-sm open-shift-hm-form"
         data-employee-id="<?= esc($s['employee_id']) ?>"
         data-equipment-idx="<?= esc($s['equipment_idx']) ?>"
         data-hm-start="<?= esc($s['hourmeter_start']) ?>">
        <div class="card-body p-4">
            <p class="section-label mb-1">END OF SHIFT</p>
            <h5 class="card-title-main mb-3">Close Your Shift</h5>

            <div class="mb-4 p-3 rounded-3" style="background:var(--bs-secondary-bg);font-size:0.9rem;">
                <div class="d-flex flex-column gap-1">
                    <div>
                        <span class="text-muted" style="font-size:0.75rem;">EQUIPMENT</span>
                        <div class="fw-semibold"><?= esc($s['equipment_label']) ?></div>
                    </div>
                    <div class="d-flex gap-4 mt-1">
                        <div>
                            <span class="text-muted" style="font-size:0.75rem;">DATE</span>
                            <div class="fw-semibold"><?= esc($s['date']) ?></div>
                        </div>
                        <div>
                            <span class="text-muted" style="font-size:0.75rem;">SHIFT</span>
                            <div class="fw-semibold"><?= $s['shift'] == 1 ? 'Day' : 'Night' ?></div>
                        </div>
                        <div>
                            <span class="text-muted" style="font-size:0.75rem;">HM START</span>
                            <div class="fw-semibold"><?= esc($s['hourmeter_start']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold fs-6">Hour-Meter End</label>
                <input type="number" class="form-control form-control-lg hm-end-input"
                       min="0" step="0.1" placeholder="e.g. 4789.0">
                <div class="invalid-feedback">
                    Must be greater than the starting hour-meter (<?= esc($s['hourmeter_start']) ?>).
                </div>
            </div>

            <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold fs-5 btn-submit-hm-end">
                Close Shift
            </button>
            <div class="alert alert-danger mt-3 d-none small mb-0 hm-end-error"></div>
        </div>
    </div>

</div>
<?php endforeach; ?>

<!-- Escape hatch -->
<div class="text-center">
    <button type="button" id="btnStartNewPsiInstead" class="btn btn-link text-secondary small">
        Start a new P2H instead
    </button>
</div>
