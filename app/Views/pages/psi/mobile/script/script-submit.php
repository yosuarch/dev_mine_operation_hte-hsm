<script>
    $(document).ready(function() {

        // ── Submit button: validate P2H form → open summary modal ────────
        $('#btnSubmitPSI').on('click', function() {

            // 1. Clear previous error states
            $('.is-invalid').removeClass('is-invalid');
            $('#mpSearchFeedback').hide();
            $('.badge.text-bg-warning[id^="status-"]')
                .attr('class', 'position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill')
                .attr('style', 'font-size:0.6rem;background:var(--bs-secondary-bg);color:var(--bs-secondary-color);')
                .text('pending');

            let valid = true;

            // 2. Validate operator selected
            const opDrIdx  = $('#opDrIdx').val();
            const opDrName = $('#mpSearch option:selected').text().trim();
            if (!opDrIdx) {
                $('#mpSearch').closest('.bootstrap-select').find('.dropdown-toggle').addClass('is-invalid');
                $('#mpSearchFeedback').show();
                valid = false;
            }

            // 3. Validate equipment selected and checklist loaded
            const equipTypeLabel = $('#equipType option:selected').text();
            const equipIdx       = $('#equipID').val();
            const equipIDLabel   = $('#equipID option:selected').text();
            const totalItems     = $('[id^="good-"]').length;
            if (!equipIdx || totalItems === 0) {
                $('#equipID').addClass('is-invalid');
                valid = false;
            }

            // 4. Every checklist item must have an explicit selection
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

            // 5. Not-normal items must have a note
            const abnormalItems = [];
            $('[id^="bad-"]').each(function() {
                if (!$(this).prop('checked')) return;
                const idx       = this.id.replace('bad-', '');
                const $ta       = $('#note-' + idx);
                const note      = $ta.val().trim();
                const checkPart = $ta.data('check-part') || '';
                if (!note) {
                    $ta.addClass('is-invalid');
                    valid = false;
                } else {
                    abnormalItems.push({ idx: parseInt(idx, 10), check_part: checkPart, note });
                }
            });

            // 6. Scroll to topmost error
            if (!valid) {
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

            // 7. Auto-populate modal shift fields from server time
            $('#psiDate').val(typeof _SERVER_DATE !== 'undefined' ? _SERVER_DATE : '').removeClass('is-invalid');
            const serverHour = typeof _SERVER_HOUR !== 'undefined' ? _SERVER_HOUR : new Date().getHours();
            if (serverHour >= 7 && serverHour < 19) {
                $('#shiftDay').prop('checked', true);
            } else {
                $('#shiftNight').prop('checked', true);
            }
            $('#psiHourMeter').val('').removeClass('is-invalid');
            $('#shiftRequiredMsg').addClass('d-none');

            // 8. Build dynamic summary body
            const normalCount   = totalItems - abnormalItems.length;
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

            // 9. Stash form state for the confirm handler
            window._psiFormState = {
                opDrIdx:      parseInt(opDrIdx, 10),
                opDrName:     opDrName,
                equipIdx:     parseInt(equipIdx, 10),
                abnormalItems: abnormalItems
            };

            bootstrap.Modal.getOrCreateInstance(document.getElementById('summaryModal')).show();
        });


        // ── Confirm button: validate modal fields → POST ─────────────────
        $('#btnConfirmSubmit').on('click', function() {
            let modalValid = true;

            // Validate date
            const psiDate = $('#psiDate').val();
            if (!psiDate) {
                $('#psiDate').addClass('is-invalid');
                modalValid = false;
            } else {
                $('#psiDate').removeClass('is-invalid');
            }

            // Validate shift
            const psiShift = $('input[name="psiShift"]:checked').val();
            if (!psiShift) {
                $('#shiftRequiredMsg').removeClass('d-none');
                modalValid = false;
            } else {
                $('#shiftRequiredMsg').addClass('d-none');
            }

            // Validate hour-meter
            const psiHourMeterVal = $('#psiHourMeter').val();
            if (psiHourMeterVal === '' || isNaN(parseFloat(psiHourMeterVal)) || parseFloat(psiHourMeterVal) < 0) {
                $('#psiHourMeter').addClass('is-invalid');
                modalValid = false;
            } else {
                $('#psiHourMeter').removeClass('is-invalid');
            }

            if (!modalValid) return;

            const $btn      = $(this).prop('disabled', true)
                                     .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting...');
            const csrfToken = $('[name="csrf_test_name"]').val();

            const payload = Object.assign({}, window._psiFormState, {
                date:          psiDate,
                shift:         parseInt(psiShift, 10),
                hourMeterStart: parseFloat(psiHourMeterVal)
            });

            $.ajax({
                url:         '<?= base_url("/operator-driver/submit-psi") ?>',
                method:      'POST',
                contentType: 'application/json',
                headers:     { 'X-CSRF-TOKEN': csrfToken },
                data:        JSON.stringify(payload),
                success: function(response) {
                    // Update CSRF token for any future requests
                    if (response.csrf_hash) $('[name="csrf_test_name"]').val(response.csrf_hash);

                    bootstrap.Modal.getInstance(document.getElementById('summaryModal')).hide();

                    $('body').append(`
                        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                             style="background:rgba(0,0,0,0.65);z-index:2000;backdrop-filter:blur(4px);" id="psiSuccessOverlay">
                            <div class="rounded-4 shadow-lg p-5 text-center mx-3" style="max-width:360px;background:var(--bs-body-bg);">
                                <div class="text-success mb-3">
                                    <i class="fas fa-circle-check" style="font-size:3.5rem;"></i>
                                </div>
                                <h4 class="fw-bold mb-2">P2H Submitted!</h4>
                                <p class="text-muted mb-4 small">
                                    Your inspection report has been recorded.<br>Have a safe shift!
                                </p>
                                <button class="btn btn-primary btn-lg px-5 fw-bold" onclick="location.reload()">
                                    Start New Form
                                </button>
                            </div>
                        </div>
                    `);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('Confirm &amp; Submit');

                    // Refresh CSRF token if the response carries a new one
                    try {
                        const resp = JSON.parse(xhr.responseText);
                        if (resp && resp.csrf_hash) $('[name="csrf_test_name"]').val(resp.csrf_hash);
                    } catch(e) {}

                    // Show error message below modal footer buttons
                    let msg = 'Submission failed. Please try again.';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                    if (!$('#psiSubmitErrorWrap').length) {
                        $('.modal-footer').before(
                            `<div class="px-3 pb-2" id="psiSubmitErrorWrap">
                                <div class="alert alert-danger py-2 small mb-0" id="psiSubmitError"></div>
                             </div>`
                        );
                    }
                    $('#psiSubmitError').text(msg);
                }
            });
        });

    });
</script>
