<script>
    $(document).ready(function() {
        $('#prestartInspectionTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '/ajax-datatable/prestartrecord',
            columns: [
                { data: '0' },
                { data: '1' },
                { data: '2' },
                { data: '3' },
                { data: '4' },
                { data: '5' },
                { data: '6' }
            ],
            dom:
                "<'row align-items-center mb-2'" +
                    "<'col-12 col-sm-6'l>" +
                    "<'col-12 col-sm-6 mt-2 mt-sm-0'f>" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row align-items-center mt-2'" +
                    "<'col-12 col-sm-5'i>" +
                    "<'col-12 col-sm-7 mt-2 mt-sm-0'p>" +
                ">",
            order: [[0, 'desc']],
            columnDefs: [
                {
                    targets: 0,
                    className: 'text-nowrap',
                    responsivePriority: 1
                },
                {
                    targets: 1,
                    responsivePriority: 1
                },
                {
                    targets: 2,
                    responsivePriority: 4
                },
                {
                    targets: 3,
                    responsivePriority: 5
                },
                {
                    targets: 4,
                    responsivePriority: 3,
                    render: function(data, type, row) {
                        const items = data ? data.split(';').filter(i => i.trim() !== '') : [];
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
                    targets: 5,
                    responsivePriority: 1,
                    render: function(data) {
                        if (!data) return '';
                        const code = data.toUpperCase().trim();
                        let bgColor = '#6c757d';
                        if (code === 'AA')      bgColor = '#8B0000';
                        else if (code === 'A')  bgColor = '#FF0000';
                        else if (code === 'B')  bgColor = '#FF6347';
                        else if (code === 'C')  bgColor = '#FFA07A';
                        return `<span class="badge" style="background-color:${bgColor};color:#fff;padding:5px 10px;">${code}</span>`;
                    }
                },
                {
                    targets: 6,
                    visible: false
                }
            ]
        });
    });
</script>
