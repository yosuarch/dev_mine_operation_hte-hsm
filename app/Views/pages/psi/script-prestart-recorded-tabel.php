<script>
    $(document).ready(function() {
        $('#prestartInspectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/ajax-datatable/prestartrecord',
            columnDefs: [{ //
                    // Date
                    targets: 0,
                    width: '100px'
                },
                { // Name
                    targets: 1,
                    width: '150px'
                },
                { // Employee ID
                    targets: 2,
                    width: '100px'
                },
                { // Gender
                    targets: 3,
                    width: '80px'
                },
                { // Equipment ID
                    targets: 4,
                    width: '120px'
                },
                { // Type
                    targets: 5,
                    width: '200px',
                    render: function(data) {
                        // Replace underscores with spaces, remove other special characters, and convert to uppercase
                        return data.replace(/_/g, ' ').replace(/[^a-zA-Z0-9 ]/g, '').toUpperCase();
                    }
                },
                { // Model
                    targets: 6,
                    width: '100px'
                },
                { // HM-Start
                    targets: 7,
                    width: '100px'
                },
                { // HM-End
                    targets: 8,
                    width: '100px'
                },
                { // Check Item
                    targets: 9,
                    width: '120px',
                    render: function(data) {
                        // Transform the content to UPPER CASE
                        return data.replace(/_/g, ' ').toUpperCase();
                    }
                },
                { // Danger Code
                    targets: 10,
                    width: '70px'
                },
                { // Note
                    targets: 11,
                    width: '200px',
                    render: function(data) {
                        // Transform the content to UPPER CASE
                        return data.replace(/_/g, ' ').toUpperCase();
                    }
                },
            ]
        });
    })
</script>