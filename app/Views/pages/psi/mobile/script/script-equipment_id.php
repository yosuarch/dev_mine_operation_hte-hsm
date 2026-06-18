<script>
    $(document).ready(function() {
        $('#equipType').on('change', function() {
            const typeIdx = $(this).val();
            const $equipID = $('#equipID');

            $equipID.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!typeIdx) {
                $equipID.html('<option value="">Select Equipment ID</option>').prop('disabled', false);
                return;
            }

            $.ajax({
                url: '<?= base_url("/operator-driver/equipment-id") ?>',
                method: 'GET',
                data: { type_idx: typeIdx },
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Select Equipment ID</option>';
                    response.forEach(function(item) {
                        options += `<option value="${item.idx}">${item.equipment_id}</option>`;
                    });
                    $equipID.html(options).prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                    $equipID.html('<option value="">Error loading data</option>').prop('disabled', false);
                }
            });
        });
    });
</script>
