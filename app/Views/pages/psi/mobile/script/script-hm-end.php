<script>
    // Populated by showOpenShiftMode / loadHmEndForm — shared state for the submit handler
    window._activeOpenShift = null;

    function loadHmEndForm(session) {
        window._activeOpenShift = session;

        const shiftLabel = session.shift === 1 ? 'Day' : 'Night';

        $('#openShiftSummary').html(`
            <div class="d-flex flex-column gap-1">
                <div><span class="text-muted" style="font-size:0.75rem;">EQUIPMENT</span>
                     <div class="fw-semibold">${session.equipment_label}</div></div>
                <div class="d-flex gap-4 mt-1">
                    <div><span class="text-muted" style="font-size:0.75rem;">DATE</span>
                         <div class="fw-semibold">${session.date}</div></div>
                    <div><span class="text-muted" style="font-size:0.75rem;">SHIFT</span>
                         <div class="fw-semibold">${shiftLabel}</div></div>
                    <div><span class="text-muted" style="font-size:0.75rem;">HM START</span>
                         <div class="fw-semibold">${session.hourmeter_start}</div></div>
                </div>
            </div>
        `);

        $('#hmEndInput').val('').removeClass('is-invalid');
        $('#hmEndError').addClass('d-none').text('');
        $('#btnSubmitHmEnd').prop('disabled', false).text('Close Shift');
        $('#openShiftHmForm').show();
    }

    // ── Submit HM End ─────────────────────────────────────────────────────────

    $(document).on('click', '#btnSubmitHmEnd', function() {
        if (!window._activeOpenShift) return;

        const $btn = $(this);
        const hmEndVal = parseFloat($('#hmEndInput').val());
        const hmStart  = parseFloat(window._activeOpenShift.hourmeter_start);

        // Client-side validation
        if (isNaN(hmEndVal) || hmEndVal < 0) {
            $('#hmEndInput').addClass('is-invalid');
            return;
        }
        if (hmEndVal <= hmStart) {
            $('#hmEndInput').addClass('is-invalid');
            $('#hmEndError')
                .removeClass('d-none')
                .text(`Hour-meter end must be greater than start (${hmStart}).`);
            return;
        }

        $('#hmEndInput').removeClass('is-invalid');
        $('#hmEndError').addClass('d-none');
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Closing...');

        const csrfToken = $('[name="csrf_test_name"]').val();

        $.ajax({
            url:         '<?= base_url("/operator-driver/submit-hm-end") ?>',
            method:      'POST',
            contentType: 'application/json',
            headers:     { 'X-CSRF-TOKEN': csrfToken },
            data:        JSON.stringify({
                employee_id:  window._activeOpenShift.employee_id,
                equipment_idx: window._activeOpenShift.equipment_idx,
                hourmeter_end: hmEndVal,
            }),
            success: function(response) {
                if (response.csrf_hash) $('[name="csrf_test_name"]').val(response.csrf_hash);

                window._activeOpenShift = null;

                $('body').append(`
                    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                         style="background:rgba(0,0,0,0.65);z-index:2000;backdrop-filter:blur(4px);"
                         id="hmEndSuccessOverlay">
                        <div class="rounded-4 shadow-lg p-5 text-center mx-3"
                             style="max-width:360px;background:var(--bs-body-bg);">
                            <div class="text-success mb-3">
                                <i class="fas fa-circle-check" style="font-size:3.5rem;"></i>
                            </div>
                            <h4 class="fw-bold mb-2">Shift Closed!</h4>
                            <p class="text-muted mb-4 small">
                                Hour-meter reading recorded.<br>Have a safe rest!
                            </p>
                            <button class="btn btn-primary btn-lg px-5 fw-bold"
                                    onclick="location.reload()">
                                Done
                            </button>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Close Shift');
                try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp && resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);
                    const msg = resp.message || 'Submission failed. Please try again.';
                    $('#hmEndError').removeClass('d-none').text(msg);
                } catch(e) {
                    $('#hmEndError').removeClass('d-none').text('Submission failed. Please try again.');
                }
            }
        });
    });
</script>
