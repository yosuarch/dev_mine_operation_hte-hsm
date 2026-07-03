<script>
    $(document).ready(function() {
        $('#previewPSIFile').click(function() {
            const fileInput = $('#formFile')[0];
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select a file first');
                return;
            }

            // Create FormData and send via AJAX
            const formData = new FormData();
            formData.append('psiRecording', file);

            $.ajax({
                url: '/preview-psi-record',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Populate the preview table
                        let rows = '';
                        response.data.forEach(row => {
                            rows += `<tr>
                            <td>${row.equipment_id}</td>
                            <td>${row.date}</td>
                            <td>${row.shift}</td>
                            <td>${row.operator_name}</td>
                            <td>${row.hourmeter_start}</td>
                            <td>${row.hourmeter_end}</td>
                            <td>${row.checking_part}</td>
                            <td>${row.checking_note}</td>
                        </tr>`;
                        });
                        $('#filePreview tbody').html(rows);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error previewing file');
                }
            });
        });
    })
</script>