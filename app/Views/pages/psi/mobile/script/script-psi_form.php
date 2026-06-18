<script>
    $(document).ready(function() {

        $(document).on('change', '[id^="bad-"]', function() {
            const idx = this.id.replace('bad-', '');
            $('#note-wrap-' + idx).show();
        });

        $(document).on('change', '[id^="good-"]', function() {
            const idx = this.id.replace('good-', '');
            $('#note-wrap-' + idx).hide().find('input').val('');
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
                        html += `<p class="text-muted text-uppercase mb-2 mt-3"><small><strong>${position}</strong></small></p>`;
                        groups[position].forEach(function(item) {
                            const cls = hazardClasses(item.hazard_code);
                            html += `
                                <div class="d-flex flex-column p-2 mb-2 rounded border-start border-3 ${cls.border} ${cls.bg} bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <h6 class="mb-0 fw-semibold flex-grow-1">${item.check_part}</h6>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <input type="radio" class="btn-check" name="psi-item-${item.idx}" id="good-${item.idx}" autocomplete="off" checked>
                                            <label class="btn btn-outline-success btn-sm py-0 px-2" for="good-${item.idx}">normal</label>
                                            <input type="radio" class="btn-check" name="psi-item-${item.idx}" id="bad-${item.idx}" autocomplete="off">
                                            <label class="btn btn-outline-danger btn-sm py-0 px-2" for="bad-${item.idx}">not-normal</label>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1">${item.hazard_code} &mdash; ${item.hazard_code_description}</small>
                                    <div id="note-wrap-${item.idx}" class="mt-2" style="display:none;">
                                        <input type="text" class="form-control form-control-sm" id="note-${item.idx}" name="psi-note-${item.idx}" placeholder="Describe the issue...">
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
