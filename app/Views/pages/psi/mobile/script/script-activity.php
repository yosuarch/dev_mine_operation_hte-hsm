<script>
    // ── Dumptruck trip recording ──────────────────────────────────────────────
    // Form and trip list are server-rendered (partial/open-shift.php +
    // partial/activity-trips.php). This script only toggles, validates,
    // posts, and injects the re-rendered list.

    // Show the new-trip form
    $(document).on('click', '.btn-add-trip', function() {
        $(this).hide().closest('.card-body').find('.dt-trip-form').slideDown(150);
    });

    // ── Combos for loader / sub-material / loading area / dumping area ────────
    // Delegated (see script-combobox.php) because a trip form can be re-rendered
    // per open shift and injected via AJAX — a single bound instance won't do.

    const loaderCombo = initDelegatedCombobox({
        comboClass: 'dt-loader-combo', inputClass: 'dt-loader-search',
        dropdownClass: 'dt-loader-dropdown', clearClass: 'dt-loader-clear', valueClass: 'dt-loader',
        key: l => l.idx, label: l => l.label,
        matches: (l, query) => l.label.toLowerCase().includes(query),
        emptyHtml: '<div class="combo-empty">No matching loader</div>',
        renderOption: renderPlainComboOption
    });

    const subMatCombo = initDelegatedCombobox({
        comboClass: 'dt-submat-combo', inputClass: 'dt-submat-search',
        dropdownClass: 'dt-submat-dropdown', clearClass: 'dt-submat-clear', valueClass: 'dt-submat',
        key: m => m.idx, label: m => m.label,
        matches: (m, query) => m.label.toLowerCase().includes(query),
        emptyHtml: '<div class="combo-empty">No matching sub material</div>',
        renderOption: renderPlainComboOption
    });

    const fromCombo = initDelegatedCombobox({
        comboClass: 'dt-from-combo', inputClass: 'dt-from-search',
        dropdownClass: 'dt-from-dropdown', clearClass: 'dt-from-clear', valueClass: 'dt-from',
        key: s => s.idx, label: s => s.label,
        matches: (s, query) => s.label.toLowerCase().includes(query),
        emptyHtml: '<div class="combo-empty">No matching area</div>',
        renderOption: renderPlainComboOption
    });

    const destCombo = initDelegatedCombobox({
        comboClass: 'dt-dest-combo', inputClass: 'dt-dest-search',
        dropdownClass: 'dt-dest-dropdown', clearClass: 'dt-dest-clear', valueClass: 'dt-dest',
        key: s => s.idx, label: s => s.label,
        matches: (s, query) => s.label.toLowerCase().includes(query),
        emptyHtml: '<div class="combo-empty">No matching area</div>',
        renderOption: renderPlainComboOption
    });

    function renderPlainComboOption(it, query, isSelected, i) {
        return `
            <div class="combo-option${isSelected ? ' active' : ''}" role="option" data-i="${i}"
                 aria-selected="${isSelected}">
                <div>${comboHighlight(it.label, query)}</div>
                ${isSelected ? '<i class="fas fa-check combo-check"></i>' : ''}
            </div>`;
    }

    // Seed the loader/from/dest combos of every trip form under $root from their
    // server-rendered data pools. Called once per injection of partial/open-shift.
    function initDumptruckActivityCombos($root) {
        $root.find('.dt-trip-form').each(function() {
            const $form = $(this);
            loaderCombo.setItems($form.find('.dt-loader-combo'), comboItemsFromPool($form.find('.dt-loader-pool')));

            const sources = comboItemsFromPool($form.find('.dt-source-pool'));
            fromCombo.setItems($form.find('.dt-from-combo'), sources);
            destCombo.setItems($form.find('.dt-dest-combo'), sources);

            // Sub material stays empty/disabled until a material category is picked
            subMatCombo.setItems($form.find('.dt-submat-combo'), []);
        });
    }

    // Material chip picked → filter sub-material options from the hidden pool
    $(document).on('change', '.dt-mat-cat input', function() {
        const $form = $(this).closest('.dt-trip-form');
        $form.find('.dt-mat-required').addClass('d-none');
        filterSubMat($form, $(this).val());
    });

    function filterSubMat($form, catIdx) {
        const $combo  = $form.find('.dt-submat-combo');
        const current = $form.find('.dt-submat').val();

        const items = $form.find('.dt-submat-all option[data-material="' + catIdx + '"]')
            .map(function() { return { idx: this.value, label: $(this).text().trim() }; }).get();

        subMatCombo.setItems($combo, items);
        subMatCombo.setDisabled($combo, false);

        if (current && items.some(it => String(it.idx) === String(current))) {
            subMatCombo.selectByKey($combo, current);
        } else {
            subMatCombo.clear($combo);
        }
    }

    // Same loading & dumping area — warn, don't block
    $(document).on('change', '.dt-from, .dt-dest', function() {
        const $form = $(this).closest('.dt-trip-form');
        const from  = $form.find('.dt-from').val();
        const dest  = $form.find('.dt-dest').val();
        $form.find('.dt-same-warning').toggleClass('d-none', !(from && dest && from === dest));
    });

    // Prefill from the most recent trip (list is ordered by time ascending)
    $(document).on('click', '.btn-same-last', function() {
        const $form = $(this).closest('.dt-trip-form');
        const $last = $(this).closest('.card-body').find('.dt-trip-row').last();
        if (!$last.length) return;

        loaderCombo.selectByKey($form.find('.dt-loader-combo'), $last.data('loader'));

        $form.find('.dt-mat-cat input[value="' + $last.data('mat-cat') + '"]')
             .prop('checked', true).trigger('change');
        subMatCombo.selectByKey($form.find('.dt-submat-combo'), $last.data('sub-mat'));

        fromCombo.selectByKey($form.find('.dt-from-combo'), $last.data('from'));
        $form.find('.dt-from-note').val($last.data('from-note') || '');
        destCombo.selectByKey($form.find('.dt-dest-combo'), $last.data('dest'));
        $form.find('.dt-dest-note').val($last.data('dest-note') || '');

        $form.find('.dt-material-note').val($last.data('material-note') || '');
        $form.find('.dt-driver-note').val($last.data('driver-note') || '');
    });

    // ── Save trip ─────────────────────────────────────────────────────────────

    $(document).on('click', '.btn-save-trip', function() {
        const $btn   = $(this);
        const $form  = $btn.closest('.dt-trip-form');
        const $card  = $btn.closest('.dt-activity-card');
        const $wrap  = $btn.closest('.open-shift-detail');

        // Required: time, material, from, dest
        let valid = true;
        const time   = $form.find('.dt-time').val();
        const matCat = $form.find('.dt-mat-cat input:checked').val();
        const from   = $form.find('.dt-from').val();
        const dest   = $form.find('.dt-dest').val();

        $form.find('.dt-time').toggleClass('is-invalid', !time);
        $form.find('.dt-mat-required').toggleClass('d-none', !!matCat);
        $form.find('.dt-from-search').toggleClass('is-invalid', !from);
        $form.find('.dt-dest-search').toggleClass('is-invalid', !dest);
        if (!time || !matCat || !from || !dest) return;

        $form.find('.dt-trip-error').addClass('d-none');
        $form.find('.dt-trip-saved').addClass('d-none');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...');

        $.ajax({
            url:         '<?= base_url("/operator-driver/submit-activity") ?>',
            method:      'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': $('[name="csrf_test_name"]').val() },
            data:        JSON.stringify({
                employee_id:   $wrap.data('employee-id'),
                equipment_idx: $wrap.data('equipment-idx'),
                time:          time,
                loader_idx:    $form.find('.dt-loader').val() || null,
                mat_cat:       matCat,
                sub_mat:       $form.find('.dt-submat').val() || null,
                material_note: $form.find('.dt-material-note').val().trim(),
                from_idx:      from,
                from_note:     $form.find('.dt-from-note').val().trim(),
                dest_idx:      dest,
                dest_note:     $form.find('.dt-dest-note').val().trim(),
                driver_note:   $form.find('.dt-driver-note').val().trim(),
            }),
            success: function(resp) {
                if (resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);

                refreshTripList($card, resp);

                // Keep values for the next (likely identical) cycle:
                // only reset the time to now and show a brief confirmation
                const now = new Date();
                $form.find('.dt-time').val(
                    String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0')
                );
                $form.find('.btn-same-last').show();
                $btn.prop('disabled', false).text('Save Trip');
                const $saved = $form.find('.dt-trip-saved').removeClass('d-none');
                setTimeout(function() { $saved.addClass('d-none'); }, 2500);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Save Trip');
                showTripError($form, xhr);
            }
        });
    });

    // ── Undo a trip (available while the shift is still open) ─────────────────

    $(document).on('click', '.btn-undo-trip', function() {
        if (!confirm('Remove this trip?')) return;

        const $row  = $(this).closest('.dt-trip-row');
        const $card = $(this).closest('.dt-activity-card');
        const $wrap = $(this).closest('.open-shift-detail');
        const $form = $card.find('.dt-trip-form');

        $.ajax({
            url:         '<?= base_url("/operator-driver/undo-activity") ?>',
            method:      'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': $('[name="csrf_test_name"]').val() },
            data:        JSON.stringify({
                employee_id:   $wrap.data('employee-id'),
                equipment_idx: $wrap.data('equipment-idx'),
                trip_idx:      $row.data('trip-idx'),
            }),
            success: function(resp) {
                if (resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);
                refreshTripList($card, resp);
                if (!resp.count) $form.find('.btn-same-last').hide();
            },
            error: function(xhr) {
                showTripError($form, xhr);
            }
        });
    });

    // ── Shared helpers ────────────────────────────────────────────────────────

    function refreshTripList($card, resp) {
        $card.find('.dt-trip-list').html(resp.html);
        $card.find('.dt-trip-count').text(resp.count + (resp.count === 1 ? ' trip' : ' trips'));
    }

    function showTripError($form, xhr) {
        let msg = 'Request failed. Please try again.';
        try {
            const resp = JSON.parse(xhr.responseText);
            if (resp && resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);
            if (resp && resp.message) msg = resp.message;
        } catch (e) {}
        $form.find('.dt-trip-error').removeClass('d-none').text(msg);
    }
</script>
