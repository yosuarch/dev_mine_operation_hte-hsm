<script>
    // ── Submit HM End ─────────────────────────────────────────────────────────
    // The form, summary, and session data (data-* attributes) are all
    // server-rendered by partial/open-shift.php — this handler only validates,
    // posts, and toggles visibility.

    $(document).on('click', '.btn-submit-hm-end', function() {
        const $btn   = $(this);
        const $card  = $btn.closest('.open-shift-hm-form');
        const $input = $card.find('.hm-end-input');
        const $error = $card.find('.hm-end-error');

        const hmStart  = parseFloat($card.data('hm-start'));
        const hmEndVal = parseFloat($input.val());

        // Client-side validation (server re-validates)
        if (isNaN(hmEndVal) || hmEndVal < 0 || hmEndVal <= hmStart) {
            $input.addClass('is-invalid');
            return;
        }

        $input.removeClass('is-invalid');
        $error.addClass('d-none');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Closing...');

        const csrfToken = $('[name="csrf_test_name"]').val();

        $.ajax({
            url:         '<?= base_url("/operator-driver/submit-hm-end") ?>',
            method:      'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': csrfToken },
            data:        JSON.stringify({
                employee_id:   $card.data('employee-id'),
                equipment_idx: $card.data('equipment-idx'),
                hourmeter_end: hmEndVal,
            }),
            success: function(response) {
                if (response.csrf_hash) $('[name="csrf_test_name"]').val(response.csrf_hash);
                $('#hmEndSuccessOverlay').css('display', 'flex');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Close Shift');
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp && resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);
                    const msg = resp.message || 'Submission failed. Please try again.';
                    $error.removeClass('d-none').text(msg);
                } catch(e) {
                    $error.removeClass('d-none').text('Submission failed. Please try again.');
                }
            }
        });
    });
</script>
