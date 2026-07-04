<script>
    $(document).ready(function() {
        const nameCombo = initCombobox({
            comboId: 'mpCombo',
            inputId: 'mpSearch',
            dropdownId: 'mpDropdown',
            clearId: 'mpClearBtn',
            key: mp => mp.employee_id,
            label: mp => mp.name,
            matches: (mp, query) => mp.name.toLowerCase().includes(query) ||
                                     String(mp.employee_id).toLowerCase().includes(query),
            emptyHtml: '<div class="combo-empty"><i class="fas fa-user-slash me-2"></i>No matching name found</div>',
            renderOption: function(mp, query, isSelected, i) {
                return `
                    <div class="combo-option${isSelected ? ' active' : ''}" role="option" data-i="${i}"
                         aria-selected="${isSelected}">
                        <div>
                            <div class="mp-name">${comboHighlight(mp.name, query)}</div>
                            <div class="mp-id">ID: ${comboEscHtml(mp.employee_id)}</div>
                        </div>
                        ${isSelected ? '<i class="fas fa-check combo-check"></i>' : ''}
                    </div>`;
            },
            // Fill fields then check for open shifts (same downstream flow as before)
            onSelect: function(mp) {
                $('#opDrID').val(mp.employee_id);
                $('#opDrIdx').val(mp.idx);
                checkOpenShifts(mp.employee_id);
            },
            onClear: function() {
                $('#opDrID').val('');
                $('#opDrIdx').val('');
                showNormalForm();
            }
        });

        // 1. Fetch manpower list
        $.ajax({
            url: '<?= base_url("/operator-driver/operator-driver-name-id") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                nameCombo.setItems(response);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });
    });

    // ── Open-shift detection ──────────────────────────────────────────────────

    function checkOpenShifts(employeeId) {
        // Hide the rest of the form and show a loading state while Redis is checked —
        // this can take a moment and was previously silent.
        $('#vehicleCard, #checklistCard, #submitCard, #psiProgressWrap').hide();
        $('#openShiftSection').html(`
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                    <p class="text-muted mb-0 mt-2 small">Checking your shift status...</p>
                </div>
            </div>
        `).show();

        $.ajax({
            url: '<?= base_url("/operator-driver/check-open-shift") ?>',
            method: 'GET',
            data: { employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_hash) $('[name="csrf_test_name"]').val(response.csrf_hash);

                if (!response.count || !response.html) {
                    showNormalForm();
                    return;
                }

                // Section markup is fully server-rendered — just inject and toggle
                const $section = $('#openShiftSection').html(response.html).show();
                initDumptruckActivityCombos($section);
            },
            error: function() {
                // On check failure, fall back to normal form silently
                showNormalForm();
            }
        });
    }

    function showNormalForm() {
        $('#openShiftSection').hide().empty();
        $('#vehicleCard, #checklistCard, #submitCard').show();
    }

    // Equipment picker tap — reveal the pre-rendered HM End form for that shift
    $(document).on('click', '.open-shift-pick-btn', function() {
        $('.open-shift-picker').hide();
        $('#' + $(this).data('target')).show();
    });

    // Escape hatch — start new P2H instead
    $(document).on('click', '#btnStartNewPsiInstead', function() {
        showNormalForm();
    });
</script>
