<script>
    $(document).ready(function() {
        $.ajax({
            url: '<?= base_url("/operator-driver/unique-equipment-type") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                let options = '<option selected value="">Select Equipment Type</option>';
                response.forEach(function(item) {
                    options += `<option value="${item.idx}">${item.abrvtn}</option>`;
                });
                $('#equipType').html(options);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });
    });
</script>
