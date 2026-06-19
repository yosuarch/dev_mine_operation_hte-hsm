<script>
    $(document).ready(function() {
        let manpowerData = [];

        // 1. Fetch manpower list
        $.ajax({
            url: '<?= base_url("/operator-driver/operator-driver-name-id") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                manpowerData = response;
                let options = '';
                response.forEach(function(mp) {
                    options += `<option value="${mp.name}" data-id="${mp.employee_id}"></option>`;
                });
                $('#mpListOptions').html(options);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });

        // 2. On name match — auto-fill fields then check for open shifts
        $('#mpSearch').on('input', function() {
            const selectedName = $(this).val();
            const match = manpowerData.find(item => item.name === selectedName);

            if (match) {
                $('#opDrID').val(match.employee_id);
                $('#opDrIdx').val(match.idx);
                checkOpenShifts(match.employee_id);
            } else {
                $('#opDrID').val('');
                $('#opDrIdx').val('');
                showNormalForm();
            }
        });
    });

    // ── Open-shift detection ──────────────────────────────────────────────────

    function checkOpenShifts(employeeId) {
        $.ajax({
            url: '<?= base_url("/operator-driver/check-open-shift") ?>',
            method: 'GET',
            data: { employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_hash) $('[name="csrf_test_name"]').val(response.csrf_hash);

                if (!response.openShifts || response.openShifts.length === 0) {
                    showNormalForm();
                    return;
                }

                showOpenShiftMode(response.openShifts);
            },
            error: function() {
                // On check failure, fall back to normal form silently
                showNormalForm();
            }
        });
    }

    function showOpenShiftMode(openShifts) {
        // Hide normal P2H flow
        $('#vehicleCard, #checklistCard, #submitCard, #psiProgressWrap').hide();
        $('#openShiftSection').show();

        if (openShifts.length === 1) {
            // Only one open shift — skip picker
            loadHmEndForm(openShifts[0]);
        } else {
            // Multiple — show equipment picker
            $('#openShiftPicker').show();
            $('#openShiftHmForm').hide();

            let html = '';
            openShifts.forEach(function(s) {
                const shiftLabel = s.shift === 1 ? 'Day' : 'Night';
                html += `
                    <button type="button"
                            class="btn btn-outline-primary text-start py-3 px-4 open-shift-pick-btn"
                            data-session='${JSON.stringify(s)}'>
                        <div class="fw-bold">${s.equipment_label}</div>
                        <div class="text-muted small">${s.date} &middot; ${shiftLabel} shift &middot; HM Start: ${s.hourmeter_start}</div>
                    </button>`;
            });
            $('#openShiftPickerList').html(html);
        }
    }

    function showNormalForm() {
        $('#openShiftSection').hide();
        $('#vehicleCard, #checklistCard, #submitCard').show();
    }

    // Equipment picker tap
    $(document).on('click', '.open-shift-pick-btn', function() {
        const session = $(this).data('session');
        $('#openShiftPicker').hide();
        loadHmEndForm(session);
    });

    // Escape hatch — start new P2H instead
    $(document).on('click', '#btnStartNewPsiInstead', function() {
        showNormalForm();
    });
</script>
