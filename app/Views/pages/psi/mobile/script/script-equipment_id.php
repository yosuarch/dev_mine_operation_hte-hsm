<script>
    $(document).ready(function() {
        const equipIDCombo = initCombobox({
            comboId: 'equipIDCombo',
            inputId: 'equipIDSearch',
            dropdownId: 'equipIDDropdown',
            clearId: 'equipIDClearBtn',
            key: u => u.idx,
            label: u => u.equipment_id,
            matches: (u, query) => u.equipment_id.toLowerCase().includes(query),
            emptyHtml: '<div class="combo-empty">No matching equipment ID</div>',
            renderOption: function(u, query, isSelected, i) {
                return `
                    <div class="combo-option${isSelected ? ' active' : ''}" role="option" data-i="${i}"
                         aria-selected="${isSelected}">
                        <div>${comboHighlight(u.equipment_id, query)}</div>
                        ${isSelected ? '<i class="fas fa-check combo-check"></i>' : ''}
                    </div>`;
            },
            onSelect: function(u) {
                $('#equipID').val(u.idx).data('where-index', u.where_index).trigger('change');
            },
            onClear: function() {
                $('#equipID').val('').data('where-index', null).trigger('change');
            }
        });

        $('#equipType').on('change', function() {
            const typeIdx = $(this).val();

            equipIDCombo.clear();
            equipIDCombo.setItems([]);
            equipIDCombo.setDisabled(true);

            if (!typeIdx) {
                equipIDCombo.setPlaceholder('Select Equipment Type first');
                return;
            }

            equipIDCombo.setPlaceholder('Loading...');

            $.ajax({
                url: '<?= base_url("/operator-driver/equipment-id") ?>',
                method: 'GET',
                data: { type_idx: typeIdx },
                dataType: 'json',
                success: function(response) {
                    // Options are server-provided as raw rows — combo builds its own list
                    equipIDCombo.setItems(response.data);
                    equipIDCombo.setDisabled(false);
                    equipIDCombo.setPlaceholder('Select Equipment ID');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                    equipIDCombo.setPlaceholder('Error loading data');
                }
            });
        });
    });
</script>
