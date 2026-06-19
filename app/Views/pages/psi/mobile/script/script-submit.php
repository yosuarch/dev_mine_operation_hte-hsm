<script>
    $(document).ready(function() {

        $('#btnSubmitPSI').on('click', function() {
            // Clear previous error states
            $('.is-invalid').removeClass('is-invalid');
            // Reset any "Required" warning badges from previous submit attempt
            $('.badge.text-bg-warning[id^="status-"]')
                .attr('class', 'position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill')
                .attr('style', 'font-size:0.6rem;background:var(--bs-secondary-bg);color:var(--bs-secondary-color);')
                .text('pending');

            let valid = true;

            // Validate: operator selected
            const opDrIdx = $('#opDrIdx').val();
            const opDrName = $('#mpSearch').val().trim();
            if (!opDrIdx) {
                $('#mpSearch').addClass('is-invalid');
                valid = false;
            }

            // Validate: equipment selected and checklist loaded
            const equipTypeLabel = $('#equipType option:selected').text();
            const equipIdx = $('#equipID').val();
            const equipIDLabel = $('#equipID option:selected').text();
            const totalItems = $('[id^="good-"]').length;
            if (!equipIdx || totalItems === 0) {
                $('#equipID').addClass('is-invalid');
                valid = false;
            }

            // Validate: every checklist item must have an explicit selection
            $('[id^="good-"]').each(function() {
                const idx = this.id.replace('good-', '');
                if (!$(`input[name="psi-item-${idx}"]:checked`).length) {
                    $('#status-' + idx)
                        .attr('class', 'position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill text-bg-warning')
                        .removeAttr('style')
                        .html('<i class="fas fa-circle-exclamation" style="font-size:0.55rem;"></i> Required');
                    valid = false;
                }
            });

            // Validate: not-normal items must have a note
            const abnormalItems = [];
            $('[id^="bad-"]').each(function() {
                if (!$(this).prop('checked')) return;

                const idx = this.id.replace('bad-', '');
                const $ta = $('#note-' + idx);
                const note = $ta.val().trim();
                const checkPart = $ta.data('check-part') || '';

                if (!note) {
                    $ta.addClass('is-invalid');
                    valid = false;
                } else {
                    abnormalItems.push({ idx, check_part: checkPart, note });
                }
            });

            if (!valid) {
                // Scroll to whichever error is highest on the page
                const $firstInvalid    = $('.is-invalid').first();
                const $firstUnanswered = $('.text-bg-warning[id^="status-"]').first().closest('.psi-item-card');

                let $scrollTarget = null;
                if ($firstInvalid.length && $firstUnanswered.length) {
                    $scrollTarget = $firstInvalid.offset().top < $firstUnanswered.offset().top
                        ? $firstInvalid : $firstUnanswered;
                } else {
                    $scrollTarget = $firstInvalid.length ? $firstInvalid : $firstUnanswered;
                }

                if ($scrollTarget && $scrollTarget.length) {
                    $('html, body').animate({ scrollTop: $scrollTarget.offset().top - 120 }, 300);
                }
                return;
            }

            // Build summary modal body
            const normalCount  = totalItems - abnormalItems.length;
            const abnormalCount = abnormalItems.length;

            let summaryHtml = `
                <div class="mb-4">
                    <p class="section-label mb-1">OPERATOR / DRIVER</p>
                    <p class="fw-semibold mb-0 fs-5">${opDrName}</p>
                </div>
                <div class="mb-4">
                    <p class="section-label mb-1">EQUIPMENT</p>
                    <p class="fw-semibold mb-0 fs-5">${equipIDLabel}</p>
                    <small class="text-muted">${equipTypeLabel}</small>
                </div>
                <div class="mb-4">
                    <p class="section-label mb-2">CHECKLIST SUMMARY</p>
                    <div class="d-flex gap-3">
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                            ${normalCount} Normal
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6">
                            ${abnormalCount} Not-Normal
                        </span>
                    </div>
                </div>`;

            if (abnormalCount === 0) {
                summaryHtml += `
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                        All checklist items are normal. Good to go!
                    </div>`;
            } else {
                summaryHtml += `<p class="section-label mb-2">NOT-NORMAL ITEMS</p>`;
                abnormalItems.forEach(function(item) {
                    summaryHtml += `
                        <div class="border-start border-4 border-danger ps-3 mb-3">
                            <p class="fw-semibold mb-1" style="font-size:0.95rem;">${item.check_part}</p>
                            <p class="text-muted mb-0 small fst-italic">"${item.note}"</p>
                        </div>`;
                });
            }

            $('#summaryBody').html(summaryHtml);

            // Store payload for final submit
            window._psiPayload = { opDrIdx, equipIdx, abnormalItems };

            bootstrap.Modal.getOrCreateInstance(document.getElementById('summaryModal')).show();
        });

        $('#btnConfirmSubmit').on('click', function() {
            const $btn = $(this).prop('disabled', true).text('Submitting...');
            const csrfToken = $('[name="csrf_test_name"]').val();

            $.ajax({
                url: '<?= base_url("/operator-driver/submit-psi") ?>',
                method: 'POST',
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: JSON.stringify(window._psiPayload),
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('summaryModal')).hide();
                    // TODO: redirect or show success screen
                    console.log('Submitted:', response);
                },
                error: function(xhr) {
                    console.error('Submit error:', xhr.responseText);
                    $btn.prop('disabled', false).text('Confirm & Submit');
                }
            });
        });

    });
</script>
