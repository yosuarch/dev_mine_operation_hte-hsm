<script>
    $(document).ready(function() {
        $('#prestartInspectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/ajax-datatable/prestartrecord',
            columnDefs: [{ //
                    // Date
                    targets: 0,
                    width: '10%'
                },
                { // Equipment ID
                    targets: 1,
                    width: '10%'
                },
                { // Type
                    targets: 2,
                    width: '10%',
                    render: function(data) {
                        // Replace underscores with spaces, remove other special characters, and convert to uppercase
                        return data.replace(/_/g, ' ').replace(/[^a-zA-Z0-9 ]/g, '').toUpperCase();
                    }
                },
                { // Model
                    targets: 3,
                    width: '10%'
                },
                { // Check Item
                    targets: 4,
                    width: '25%',
                    render: function(data) {
                        if (!data) return '';

                        return data.split(',')
                            .filter(item => item.trim() !== '') // Removes empty entries
                            // Use (index + 1) to start numbering from 1
                            .map((item, index) => (index + 1) + '. ' + item.replace(/_/g, ' ').trim().toUpperCase())
                            .join('<br>');
                    }
                },
                { // Danger Code
                    targets: 5,
                    width: '10%'
                },
                { // Note
                    targets: 6,
                    width: '25%',
                    render: function(data) {
                        if (!data) return '';

                        return data.split(',')
                            .filter(item => item.trim() !== '')
                            .map((item, index) => (index + 1) + '. ' + item.replace(/_/g, ' ').trim().toUpperCase())
                            .join('<br>');
                    }
                }
            ]
        });
    })
</script>