<script>
    $(document).ready(function() {

        function updateProgress() {
            const total    = $('[id^="good-"]').length;
            const reviewed = $('.psi-item-card.state-normal, .psi-item-card.state-abnormal').length;
            const pct      = total > 0 ? Math.round((reviewed / total) * 100) : 0;

            $('#psiProgressLabel').text(reviewed + ' / ' + total + ' reviewed');
            $('#psiProgressBar')
                .css('width', pct + '%')
                .attr('aria-valuenow', pct);

            if (reviewed === total && total > 0) {
                $('#psiProgressBar').removeClass('bg-primary').addClass('bg-success');
            } else {
                $('#psiProgressBar').removeClass('bg-success').addClass('bg-primary');
            }
        }

        function updateItemState(idx, state) {
            const $card  = $('#good-' + idx).closest('.psi-item-card');
            const $badge = $('#status-' + idx);

            $card.removeClass('state-normal state-abnormal');

            if (state === 'normal') {
                $card.addClass('state-normal');
                $badge.attr('class', 'position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill text-bg-success')
                      .removeAttr('style')
                      .html('<i class="fas fa-check" style="font-size:0.55rem;"></i> Normal');
            } else {
                $card.addClass('state-abnormal');
                $badge.attr('class', 'position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill text-bg-danger')
                      .removeAttr('style')
                      .html('<i class="fas fa-triangle-exclamation" style="font-size:0.55rem;"></i> Not-Normal');
            }

            updateProgress();
        }

        $(document).on('change', '[id^="good-"]', function() {
            const idx = this.id.replace('good-', '');
            $('#note-wrap-' + idx).hide();
            $('#note-' + idx).val('').css('height', 'auto').removeClass('is-invalid');
            updateItemState(idx, 'normal');
        });

        $(document).on('change', '[id^="bad-"]', function() {
            const idx = this.id.replace('bad-', '');
            $('#note-wrap-' + idx).show();
            updateItemState(idx, 'abnormal');
        });

        $(document).on('input', 'textarea[id^="note-"]', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
            if ($(this).val().trim()) $(this).removeClass('is-invalid');
        });

        $('#equipID').on('change', function() {
            const equipIdx   = $(this).val();
            const whereIndex = $(this).find('option:selected').data('where-index');

            $('#psiProgressWrap').hide();
            $('#psiProgressLabel').text('0 / 0 reviewed');
            $('#psiProgressBar').css('width', '0%').removeClass('bg-success').addClass('bg-primary');

            if (!equipIdx || whereIndex === undefined) {
                $('#psiFormItems').html('<p class="text-muted mb-0 small">Select an equipment above to load the checklist.</p>');
                return;
            }

            $('#psiFormItems').html(`
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                    <p class="text-muted mb-0 mt-2 small">Loading checklist...</p>
                </div>`);

            $.ajax({
                url: '<?= base_url("/operator-driver/psi-fetch-form") ?>',
                method: 'GET',
                data: { equip_idx: whereIndex },
                dataType: 'json',
                success: function(response) {
                    const $container = $('#psiFormItems');

                    if (!response.length) {
                        $container.html('<p class="text-muted mb-0 small">No checklist found for this equipment.</p>');
                        return;
                    }

                    function hazardBorder(code) {
                        if (code === 'AA') return 'border-danger';
                        if (code === 'A')  return 'border-warning';
                        return 'border-success';
                    }

                    const groups = {};
                    const groupOrder = [];
                    response.forEach(function(item) {
                        const pos = item.spot_position || 'GENERAL';
                        if (!groups[pos]) { groups[pos] = []; groupOrder.push(pos); }
                        groups[pos].push(item);
                    });

                    let html = '';
                    groupOrder.forEach(function(position) {
                        html += `
                            <div class="d-flex align-items-center justify-content-between mt-4 mb-2">
                                <span class="text-uppercase fw-bold"
                                      style="font-size:0.68rem;letter-spacing:0.12em;color:var(--bs-secondary-color);">
                                    ${position}
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
                                      style="font-size:0.6rem;">
                                    ${groups[position].length} items
                                </span>
                            </div>`;

                        groups[position].forEach(function(item) {
                            const border = hazardBorder(item.hazard_code);
                            html += `
                                <div class="psi-item-card position-relative d-flex flex-column p-3 mb-2 rounded-3 border-start border-3 ${border}">

                                    <span id="status-${item.idx}"
                                          class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill"
                                          style="font-size:0.6rem;background:var(--bs-secondary-bg);color:var(--bs-secondary-color);">
                                        pending
                                    </span>

                                    <p class="fw-semibold pe-4 mb-3 lh-sm" style="font-size:1rem;">${item.check_part}</p>

                                    <div class="btn-group w-100 mb-2">
                                        <input type="radio" class="btn-check" name="psi-item-${item.idx}"
                                               id="good-${item.idx}" autocomplete="off">
                                        <label class="btn btn-outline-success py-2 fw-semibold"
                                               for="good-${item.idx}" style="font-size:0.9rem;">
                                            ✓&nbsp; Normal
                                        </label>
                                        <input type="radio" class="btn-check" name="psi-item-${item.idx}"
                                               id="bad-${item.idx}" autocomplete="off">
                                        <label class="btn btn-outline-danger py-2 fw-semibold"
                                               for="bad-${item.idx}" style="font-size:0.9rem;">
                                            ⚠&nbsp; Not-Normal
                                        </label>
                                    </div>

                                    <small class="text-muted" style="font-size:0.7rem;">
                                        ${item.hazard_code} &mdash; ${item.hazard_code_description}
                                    </small>

                                    <div id="note-wrap-${item.idx}" class="mt-3 pt-2 border-top border-danger border-opacity-25"
                                         style="display:none;">
                                        <small class="text-danger fw-bold d-block mb-1" style="font-size:0.72rem;">
                                            &#9888; Note is required for not-normal items
                                        </small>
                                        <textarea
                                            class="form-control"
                                            id="note-${item.idx}"
                                            name="psi-note-${item.idx}"
                                            data-check-part="${item.check_part}"
                                            placeholder="Required — describe the issue..."
                                            rows="2"
                                            style="font-size:0.95rem;resize:none;overflow:hidden;"></textarea>
                                        <div class="invalid-feedback">Please describe the issue before submitting.</div>
                                    </div>
                                </div>`;
                        });
                    });

                    $container.html(html);
                    $('#psiProgressWrap').show();
                    updateProgress();
                },
                error: function() {
                    $('#psiFormItems').html('<p class="text-danger mb-0 small">Failed to load checklist. Please try again.</p>');
                }
            });
        });
    });
</script>
