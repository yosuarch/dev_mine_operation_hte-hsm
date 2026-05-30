<script>
    $(document).ready(function() {
        $.ajax({
            url: '/ajax-common-table/psi-by-equipment-type',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                let tbody = $('#byEquipmentType tbody');
                tbody.empty(); // Clear existing rows

                // Loop through the data and build the rows
                $.each(response, function(index, item) {
                    let row = `<tr>
                    <td>${item.class}</td>script-issue-summary-by-equipment-type
                    <td>${item.inside ?? 0}</td>
                    <td>${item.outside ?? 0}</td>
                    <td>${item.safety_device ?? 0}</td>
                </tr>`;
                    tbody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#byEquipmentType tbody').html('<tr><td colspan="4">Error loading data.</td></tr>');
            }
        });
    });
</script>