<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <meta http-equiv="refresh" content="5">
</head>

<body class="bg-light">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-primary"><i class="fas fa-satellite-dish"></i> Dashboard Monitoring Kamar</h2>
                <p class="text-muted mb-0">Monitoring Dht22 Dht11 dan Kualitas Udara</p>
            </div>

            <div class="d-flex flex-column align-items-end">
                <span class="badge bg-success p-2 mb-2"><i class="fas fa-clock"></i> Live Update (5s)</span>

                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="setUnit('C')" id="btn-C">°C</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('R')" id="btn-R">°R</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('F')" id="btn-F">°F</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('K')" id="btn-K">K</button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            @php $last = $data->last(); @endphp

            @if ($last)
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3 shadow h-100">
                        <div class="card-header bg-primary border-0 fw-bold">
                            <i class="fas fa-temperature-high"></i> DHT22 (Utama)
                        </div>
                        <div class="card-body text-center">
                            <h1 class="display-4 fw-bold">
                                <span id="card-t22" data-val="{{ $last->temp22 }}">{{ $last->temp22 }}</span><span class="unit-label">°C</span>
                            </h1>
                            <p class="fs-5 mb-0"><i class="fas fa-tint"></i> Lembap: {{ $last->hum22 }}%</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3 shadow h-100">
                        <div class="card-header bg-info border-0 fw-bold text-dark">
                            <i class="fas fa-thermometer-half"></i> DHT11 (Sekunder)
                        </div>
                        <div class="card-body text-center text-dark">
                            <h1 class="display-4 fw-bold">
                                <span id="card-t11" data-val="{{ $last->temp11 }}">{{ $last->temp11 }}</span><span class="unit-label">°C</span>
                            </h1>
                            <p class="fs-5 mb-0"><i class="fas fa-tint"></i> Lembap: {{ $last->hum11 }}%</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white {{ $last->ppm > 300 ? 'bg-danger' : ($last->ppm > 100 ? 'bg-warning' : 'bg-success') }} mb-3 shadow h-100">
                        <div class="card-header border-0 fw-bold">
                            <i class="fas fa-wind"></i> Kualitas Udara
                        </div>
                        <div class="card-body text-center">
                            <h1 class="display-4 fw-bold">{{ $last->ppm }} <span class="fs-6">PPM</span></h1>
                            <p class="fs-5 mb-0">
                                Status:
                                {{ $last->ppm > 300 ? '☠️ BAHAYA' : ($last->ppm > 100 ? '⚠️ WASPADA' : '✅ AMAN') }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-12">
                    <div class="alert alert-warning">Menunggu Data...</div>
                </div>
            @endif
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line"></i> Grafik Data</span>
                        
                        <div class="btn-group" role="group" id="chart-filters">
                            <button id="filter-all" class="btn btn-sm btn-outline-secondary active" onclick="filterChart('all')">Semua</button>
                            <button id="filter-dht22" class="btn btn-sm btn-outline-primary" onclick="filterChart('dht22')">DHT22</button>
                            <button id="filter-dht11" class="btn btn-sm btn-outline-info" onclick="filterChart('dht11')">DHT11</button>
                            <button id="filter-mq135" class="btn btn-sm btn-outline-danger" onclick="filterChart('mq135')">MQ135</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="sensorChart" style="height: 350px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white fw-bold"><i class="fas fa-list"></i> 10 Data Terakhir</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>DHT22</th>
                                        <th>DHT11</th>
                                        <th>Gas (PPM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data->sortByDesc('id')->take(10) as $d)
                                        <tr>
                                            <td>{{ $d->created_at->format('H:i:s') }}</td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <span class="temp-data" data-val="{{ $d->temp22 }}">{{ $d->temp22 }}</span>
                                                    <span class="unit-label">°C</span>
                                                </span>
                                                <span class="badge bg-secondary">{{ $d->hum22 }}%</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    <span class="temp-data" data-val="{{ $d->temp11 }}">{{ $d->temp11 }}</span>
                                                    <span class="unit-label">°C</span>
                                                </span>
                                                <span class="badge bg-secondary">{{ $d->hum11 }}%</span>
                                            </td>
                                            <td class="fw-bold {{ $d->ppm > 300 ? 'text-danger' : 'text-success' }}">
                                                {{ $d->ppm }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end p-2">
                        <a href="{{ url('/export-excel') }}" class="btn btn-success btn-sm shadow-sm">
                            <i class="fas fa-file-excel"></i> Download Laporan (.xlsx)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // === 1. PERSIAPAN DATA ===
        const labels = {!! json_encode($data->pluck('created_at')->map(fn($d) => $d->format('H:i:s'))) !!};
        const rawT22 = {!! json_encode($data->pluck('temp22')) !!};
        const rawT11 = {!! json_encode($data->pluck('temp11')) !!};
        const gasData = {!! json_encode($data->pluck('ppm')) !!};

        const ctx = document.getElementById('sensorChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Suhu DHT22',
                        data: [...rawT22],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Suhu DHT11',
                        data: [...rawT11],
                        borderColor: '#0dcaf0',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Gas MQ135 (PPM)',
                        data: gasData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Suhu (°C)' } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Gas (PPM)' } }
                }
            }
        });

        // === 2. FUNGSI KONVERSI SUHU ===
        function convertTemp(celsius, unit) {
            let val = parseFloat(celsius);
            if (unit === 'R') return (val * 0.8).toFixed(1);
            if (unit === 'F') return ((val * 9 / 5) + 32).toFixed(1);
            if (unit === 'K') return (val + 273.15).toFixed(1);
            return val.toFixed(1);
        }

        // === 3. FUNGSI GANTI SATUAN (PERSISTENT) ===
        function setUnit(unit) {
            localStorage.setItem('tempUnit', unit); // Simpan pilihan

            let label = "°C";
            if (unit === 'R') label = "°R";
            if (unit === 'F') label = "°F";
            if (unit === 'K') label = "K";

            document.querySelectorAll('.unit-label').forEach(el => el.innerText = label);

            const card22 = document.getElementById('card-t22');
            const card11 = document.getElementById('card-t11');
            if (card22) card22.innerText = convertTemp(card22.getAttribute('data-val'), unit);
            if (card11) card11.innerText = convertTemp(card11.getAttribute('data-val'), unit);

            document.querySelectorAll('.temp-data').forEach(el => {
                el.innerText = convertTemp(el.getAttribute('data-val'), unit);
            });

            const newData22 = rawT22.map(val => convertTemp(val, unit));
            const newData11 = rawT11.map(val => convertTemp(val, unit));

            myChart.data.datasets[0].data = newData22;
            myChart.data.datasets[1].data = newData11;
            myChart.options.scales.y.title.text = `Suhu (${label})`;
            myChart.update();

            document.querySelectorAll('.btn-group .btn-outline-primary').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`btn-${unit}`).classList.add('active');
        }

        // === 4. FUNGSI FILTER GRAFIK (PERSISTENT) ===
        function filterChart(type) {
            // Simpan pilihan filter ke Local Storage
            localStorage.setItem('chartFilter', type);

            // Reset semua tombol filter (hapus class active)
            const buttons = document.querySelectorAll('#chart-filters button');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Aktifkan tombol yang dipilih
            document.getElementById(`filter-${type}`).classList.add('active');

            // Logika Show/Hide Dataset
            // Index: 0=DHT22, 1=DHT11, 2=MQ135
            if (type === 'all') {
                myChart.show(0); myChart.show(1); myChart.show(2);
            } else if (type === 'dht22') {
                myChart.show(0); myChart.hide(1); myChart.hide(2);
            } else if (type === 'dht11') {
                myChart.hide(0); myChart.show(1); myChart.hide(2);
            } else if (type === 'mq135') {
                myChart.hide(0); myChart.hide(1); myChart.show(2);
            }
        }

        // === 5. JALANKAN SAAT LOAD (INIT) ===
        window.onload = function() {
            // A. Load Satuan Suhu
            const savedUnit = localStorage.getItem('tempUnit') || 'C';
            setUnit(savedUnit);

            // B. Load Filter Grafik
            const savedFilter = localStorage.getItem('chartFilter') || 'all';
            filterChart(savedFilter);
        };
    </script>

</body>
</html>