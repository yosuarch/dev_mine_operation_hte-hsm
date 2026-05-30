<script>
    $(document).ready(function() {
        $.getJSON('<?= base_url("/ajax-chart/freq-danger-code") ?>', function(response) {
            // 1. Ambil list tanggal unik (Sumbu X)
            const dates = [...new Set(response.map(item => item.date))];

            // 2. Ambil list kode bahaya unik (Untuk membuat masing-masing bar)
            const dangerCodes = [...new Set(response.map(item => item.danger_code))];

            // 3. Buat dataset secara dinamis
            const datasets = dangerCodes.map(code => {
                return {
                    label: 'Danger Code: ' + code,
                    backgroundColor: getDangerColor(code),
                    // Kita petakan setiap tanggal ke frekuensi yang sesuai
                    data: dates.map(date => {
                        // Cari data untuk tanggal ini dan kode ini
                        const record = response.find(item => item.date === date && item.danger_code === code);
                        return record ? parseInt(record.frequency) : 0;
                    })
                };
            });

            // 4. Render Chart
            const ctx = document.getElementById('dangerFreqChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dates,
                    datasets: datasets // Sekarang datasets berisi array dari semua kode yang ada
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });

            function getDangerColor(code) {
                const colors = {
                    'AA': '#8B0000', // Dark Red - Sangat Berbahaya
                    'A': '#FF0000', // Red      - Berbahaya
                    'B': '#FF6347', // Tomato   - Sedang
                    'C': '#FFA07A' // Light Salmon - Sangat Ringan
                };
                return colors[code] || '#cccccc';
            }
        });
    })
</script>