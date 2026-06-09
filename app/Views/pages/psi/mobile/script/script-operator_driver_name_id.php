<script>
    $(document).ready(function() {
        let manpowerData = [];

        // 1. Fetch the data from the controller
        $.ajax({
            url: '<?= base_url("/operator-driver/operator-driver-name-id") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                manpowerData = response;
                let options = '';

                // 2. Populate the datalist
                response.forEach(function(mp) {
                    // We use name as the value
                    options += `<option value="${mp.name}" data-id="${mp.employee_id}"></option>`;
                });
                $('#mpListOptions').html(options);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });

        // 3. Live search / Auto-fill logic
        $('#mpSearch').on('input', function() {
            let selectedName = $(this).val();
            // Find the object in our data array that matches the typed name
            let match = manpowerData.find(item => item.name === selectedName);

            if (match) {
                // Populate the fields
                $('#opDrID').val(match.employee_id);
                $('#opDrIdx').val(match.idx);
            } else {
                // Clear if the input does not match a valid name
                $('#opDrID').val('');
                $('#opDrIdx').val('');
            }
        });
    });
</script>