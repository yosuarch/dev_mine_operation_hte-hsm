<script>
    $(document).ready(function() {
        $('#prestartInspectionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/ajax-datatable/prestartrecord',
            columns: [{
                    data: '0'
                }, // Index 0
                {
                    data: '1'
                }, // Index 1
                {
                    data: '2'
                }, // Index 2
                {
                    data: '3'
                }, // Index 3
                {
                    data: '4'
                }, // Index 4
                {
                    data: '5'
                }, // Index 5
                {
                    data: '6'
                } // Index 6
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            responsive: true,
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                    targets: 0,
                    className: 'text-nowrap'
                },
                {
                    // TARGET 4: Check Item (index 4)
                    targets: 4,
                    render: function(data, type, row) {
                        // 'data' is the value of index 4 (check_item)
                        // 'row' is the entire array for this row
                        const items = data ? data.split(';').filter(i => i.trim() !== '') : [];

                        // Change: Access the note column using its index (6)
                        const notes = row[6] ? row[6].split(';').filter(n => n.trim() !== '') : [];

                        if (items.length === 0) return '';

                        return `<ul class="list-unstyled mb-0 small">
                                ${items.map((item, idx) => `
                                    <li class="mb-2">
                                        <strong>${idx + 1}. ${item.replace(/_/g, ' ').trim().toUpperCase()}</strong>
                                        ${notes[idx] ? `<div class="text-muted ps-3 border-start border-2 mt-1">Note: ${notes[idx].trim()}</div>` : ''}
                                    </li>
                                `).join('')}
                            </ul>`;
                    }
                },
                {
                    // TARGET 5: Danger Code (index 5)
                    targets: 5,
                    render: function(data) {
                        if (!data) return '';
                        const code = data.toUpperCase().trim();
                        let bgColor = '#6c757d';
                        if (code === 'AA') bgColor = '#8B0000';
                        else if (code === 'A') bgColor = '#FF0000';
                        else if (code === 'B') bgColor = '#FF6347';
                        else if (code === 'C') bgColor = '#FFA07A';
                        return `<span class="badge" style="background-color: ${bgColor}; color: #ffffff; padding: 5px 10px;">${code}</span>`;
                    }
                },
                {
                    // TARGET 6: Note (index 6) - Hide it
                    targets: 6,
                    visible: false
                }
            ]
        });
    });
</script>