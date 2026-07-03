<script>
    $(document).ready(function() {
        $.ajax({
            url: '<?= base_url("/operator-driver/unique-equipment-type") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Options are server-rendered — just inject
                $('#equipType').html(response.html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });
    });
</script>
