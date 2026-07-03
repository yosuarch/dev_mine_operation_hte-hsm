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
                    // Checklist markup (incl. empty state) is server-rendered — just inject
                    $('#psiFormItems').html(response.html);
                    if (response.count > 0) {
                        $('#psiProgressWrap').show();
                        updateProgress();
                    }
                },
                error: function() {
                    $('#psiFormItems').html('<p class="text-danger mb-0 small">Failed to load checklist. Please try again.</p>');
                }
            });
        });
    });
</script>
