<script>
    // ── Dumptruck trip recording ──────────────────────────────────────────────
    // Form and trip list are server-rendered (partial/open-shift.php +
    // partial/activity-trips.php). This script only toggles, validates,
    // posts, and injects the re-rendered list.

    // Show the new-trip form
    $(document).on('click', '.btn-add-trip', function() {
        $(this).hide().closest('.card-body').find('.dt-trip-form').slideDown(150);
    });

    // Material chip picked → filter sub-material options from the hidden pool
    $(document).on('change', '.dt-mat-cat input', function() {
        const $form = $(this).closest('.dt-trip-form');
        $form.find('.dt-mat-required').addClass('d-none');
        filterSubMat($form, $(this).val());
    });

    function filterSubMat($form, catIdx) {
        const $sel     = $form.find('.dt-submat');
        const current  = $sel.val();
        let html       = '<option value="">No sub material</option>';
        $form.find('.dt-submat-all option[data-material="' + catIdx + '"]').each(function() {
            html += this.outerHTML;
        });
        $sel.html(html).prop('disabled', false);
        if (current && $sel.find('option[value="' + current + '"]').length) $sel.val(current);
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

        $form.find('.dt-loader').val(String($last.data('loader') || ''));
        $form.find('.dt-mat-cat input[value="' + $last.data('mat-cat') + '"]')
             .prop('checked', true).trigger('change');
        $form.find('.dt-submat').val(String($last.data('sub-mat') || ''));
        $form.find('.dt-from').val(String($last.data('from') || '')).trigger('change');
        $form.find('.dt-from-note').val($last.data('from-note') || '');
        $form.find('.dt-dest').val(String($last.data('dest') || ''));
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
        $form.find('.dt-from').toggleClass('is-invalid', !from);
        $form.find('.dt-dest').toggleClass('is-invalid', !dest);
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
