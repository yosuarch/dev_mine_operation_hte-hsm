<script>
    $(document).ready(function() {
        const equipTypeCombo = initCombobox({
            comboId: 'equipTypeCombo',
            inputId: 'equipTypeSearch',
            dropdownId: 'equipTypeDropdown',
            clearId: 'equipTypeClearBtn',
            key: t => t.idx,
            label: t => t.abrvtn,
            matches: (t, query) => t.abrvtn.toLowerCase().includes(query),
            emptyHtml: '<div class="combo-empty">No matching equipment type</div>',
            renderOption: function(t, query, isSelected, i) {
                return `
                    <div class="combo-option${isSelected ? ' active' : ''}" role="option" data-i="${i}"
                         aria-selected="${isSelected}">
                        <div>${comboHighlight(t.abrvtn, query)}</div>
                        ${isSelected ? '<i class="fas fa-check combo-check"></i>' : ''}
                    </div>`;
            },
            onSelect: function(t) {
                $('#equipType').val(t.idx).trigger('change');
            },
            onClear: function() {
                $('#equipType').val('').trigger('change');
            }
        });

        $.ajax({
            url: '<?= base_url("/operator-driver/unique-equipment-type") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Options are server-provided as raw rows — combo builds its own list
                equipTypeCombo.setItems(response.data);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
                equipTypeCombo.setPlaceholder('Failed to load equipment types');
            }
        });
    });
</script>
