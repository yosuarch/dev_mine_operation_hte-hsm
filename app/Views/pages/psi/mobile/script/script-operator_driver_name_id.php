<script>
    $(document).ready(function() {
        let manpowerData = [];
        let filtered = [];
        let activeIdx = -1;
        let selectedId = null;

        const $combo = $('#mpCombo');
        const $input = $('#mpSearch');
        const $menu = $('#mpDropdown');
        const $list = $menu.find('.mp-combo-list');
        const $clear = $('#mpClearBtn');

        // 1. Fetch manpower list
        $.ajax({
            url: '<?= base_url("/operator-driver/operator-driver-name-id") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                manpowerData = response;
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });

        // ── Combobox helpers ──────────────────────────────────────────────

        function escHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        function highlight(text, query) {
            if (!query) return escHtml(text);
            const i = text.toLowerCase().indexOf(query);
            if (i === -1) return escHtml(text);
            return escHtml(text.slice(0, i)) +
                '<mark>' + escHtml(text.slice(i, i + query.length)) + '</mark>' +
                escHtml(text.slice(i + query.length));
        }

        function renderList(rawQuery) {
            const query = (rawQuery || '').trim().toLowerCase();
            filtered = !query ? manpowerData : manpowerData.filter(mp =>
                mp.name.toLowerCase().includes(query) ||
                String(mp.employee_id).toLowerCase().includes(query)
            );
            activeIdx = -1;

            if (!filtered.length) {
                $list.html('<div class="mp-combo-empty"><i class="fas fa-user-slash me-2"></i>No matching name found</div>');
                return;
            }

            let html = '';
            filtered.forEach(function(mp, i) {
                const isSelected = selectedId !== null && mp.employee_id === selectedId;
                html += `
                    <div class="mp-combo-option${isSelected ? ' active' : ''}" role="option" data-i="${i}"
                         aria-selected="${isSelected}">
                        <div>
                            <div class="mp-name">${highlight(mp.name, query)}</div>
                            <div class="mp-id">ID: ${escHtml(mp.employee_id)}</div>
                        </div>
                        ${isSelected ? '<i class="fas fa-check mp-check"></i>' : ''}
                    </div>`;
            });
            $list.html(html);
        }

        function openMenu() {
            renderList($input.val());
            $menu.show();
            $combo.addClass('open');
            $input.attr('aria-expanded', 'true');
        }

        function closeMenu() {
            $menu.hide();
            $combo.removeClass('open');
            $input.attr('aria-expanded', 'false');
        }

        // Fill fields then check for open shifts (same downstream flow as before)
        function applySelection(mp) {
            selectedId = mp.employee_id;
            $('#opDrID').val(mp.employee_id);
            $('#opDrIdx').val(mp.idx);
            checkOpenShifts(mp.employee_id);
        }

        function clearSelection() {
            selectedId = null;
            $('#opDrID').val('');
            $('#opDrIdx').val('');
            showNormalForm();
        }

        function selectOption(mp) {
            $input.val(mp.name).removeClass('is-invalid');
            $clear.show();
            applySelection(mp);
            closeMenu();
        }

        function setActive(idx) {
            activeIdx = idx;
            const $opts = $list.find('.mp-combo-option');
            $opts.removeClass('active');
            if (idx >= 0) {
                const el = $opts.eq(idx).addClass('active')[0];
                if (el) el.scrollIntoView({ block: 'nearest' });
            }
        }

        // ── Combobox events ───────────────────────────────────────────────

        $input.on('focus click', openMenu);

        $input.on('input', function() {
            const value = $(this).val();
            $clear.toggle(value.length > 0);

            // Keep old behavior: an exact typed name still counts as a selection
            const exact = manpowerData.find(mp => mp.name.toLowerCase() === value.trim().toLowerCase());
            if (exact) {
                $input.removeClass('is-invalid');
                applySelection(exact);
            } else if (selectedId !== null) {
                clearSelection();
            }
            openMenu();
        });

        $input.on('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!$menu.is(':visible')) return openMenu();
                setActive(Math.min(activeIdx + 1, filtered.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIdx - 1, 0));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && filtered[activeIdx]) selectOption(filtered[activeIdx]);
                else if (filtered.length === 1) selectOption(filtered[0]);
            } else if (e.key === 'Escape') {
                closeMenu();
            }
        });

        // mousedown (not click) so the input doesn't blur before selection registers
        $menu.on('mousedown', '.mp-combo-option', function(e) {
            e.preventDefault();
            const mp = filtered[$(this).data('i')];
            if (mp) selectOption(mp);
        });

        $clear.on('click', function() {
            $input.val('');
            $clear.hide();
            clearSelection();
            $input.trigger('focus');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#mpCombo').length) closeMenu();
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

                if (!response.count || !response.html) {
                    showNormalForm();
                    return;
                }

                // Section markup is fully server-rendered — just inject and toggle
                $('#vehicleCard, #checklistCard, #submitCard, #psiProgressWrap').hide();
                $('#openShiftSection').html(response.html).show();
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
