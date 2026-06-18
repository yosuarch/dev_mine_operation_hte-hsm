<script>
    $(document).ready(function() {

        $(document).on('change', '[id^="bad-"]', function() {
            const idx = this.id.replace('bad-', '');
            $('#note-wrap-' + idx).show();
        });

        $(document).on('change', '[id^="good-"]', function() {
            const idx = this.id.replace('good-', '');
            const $wrap = $('#note-wrap-' + idx);
            $wrap.hide().find('textarea').val('').css('height', 'auto');
        });

        $(document).on('input', 'textarea[id^="note-"]', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        $('#equipID').on('change', function() {
            const equipIdx = $(this).val();
            const whereIndex = $(this).find('option:selected').data('where-index');

            if (!equipIdx || whereIndex === undefined) {
                return;
            }

            $.ajax({
                url: '<?= base_url("/operator-driver/psi-fetch-form") ?>',
                method: 'GET',
                data: { equip_idx: whereIndex },
                dataType: 'json',
                success: function(response) {
                    const $container = $('#psiFormItems');

                    if (!response.length) {
                        $container.html('<p class="text-muted mb-0"><small>No checklist found for this equipment.</small></p>');
                        return;
                    }

                    function hazardClasses(code) {
                        if (code === 'AA') return { border: 'border-danger', bg: 'bg-danger' };
                        if (code === 'A')  return { border: 'border-warning', bg: 'bg-warning' };
                        return                      { border: 'border-success', bg: 'bg-success' };
                    }

                    // group items by spot_position preserving order
                    const groups = {};
                    const groupOrder = [];
                    response.forEach(function(item) {
                        const pos = item.spot_position || 'GENERAL';
                        if (!groups[pos]) {
                            groups[pos] = [];
                            groupOrder.push(pos);
                        }
                        groups[pos].push(item);
                    });

                    let html = '';
                    groupOrder.forEach(function(position) {
                        html += `
                            <p class="text-uppercase fw-bold mb-2 mt-3" style="font-size:0.7rem;letter-spacing:0.1em;color:#6c757d;">
                                ${position}
                            </p>`;
                        groups[position].forEach(function(item) {
                            const cls = hazardClasses(item.hazard_code);
                            html += `
                                <div class="d-flex flex-column p-3 mb-3 rounded border-start border-4 ${cls.border} ${cls.bg} bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <span class="fw-semibold flex-grow-1" style="font-size:1rem;">${item.check_part}</span>
                                        <div class="d-flex gap-2 flex-shrink-0">
                                            <input type="radio" class="btn-check" name="psi-item-${item.idx}" id="good-${item.idx}" autocomplete="off" checked>
                                            <label class="btn btn-outline-success px-3 py-2" for="good-${item.idx}" style="font-size:0.9rem;">normal</label>
                                            <input type="radio" class="btn-check" name="psi-item-${item.idx}" id="bad-${item.idx}" autocomplete="off">
                                            <label class="btn btn-outline-danger px-3 py-2" for="bad-${item.idx}" style="font-size:0.9rem;">not-normal</label>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-2">${item.hazard_code} &mdash; ${item.hazard_code_description}</small>
                                    <div id="note-wrap-${item.idx}" class="mt-2" style="display:none;">
                                        <textarea class="form-control" id="note-${item.idx}" name="psi-note-${item.idx}" placeholder="Describe the issue..." rows="1" style="font-size:1rem;resize:none;overflow:hidden;"></textarea>
                                    </div>
                                </div>`;
                        });
                    });

                    $container.html(html);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        });
    });
</script>
