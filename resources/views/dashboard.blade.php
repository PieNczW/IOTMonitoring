<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- LOGIKA UTAMA --}}
    @php
        date_default_timezone_set('Asia/Jakarta');
        $today = date('Y-m-d'); 

        // Live Mode AKTIF jika input filter kosong
        $isLiveMode = empty($weekInput) && empty($monthInput) && (empty($dateInput) || $dateInput == $today);
    @endphp

    {{-- Auto Refresh HANYA jika Live Mode --}}
    @if ($isLiveMode)
        <meta http-equiv="refresh" content="5">
    @endif
</head>

<body>

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold text-primary mb-0"><i class="fas fa-network-wired"></i> Monitoring Iot Kamar</h3>

                    @php
                        // === LOGIKA STATUS & WAKTU ===
                        $lastData = $data->sortBy('created_at')->last();
                        $isOnline = false;
                        $lastUpdateText = '-';

                        if ($lastData) {
                            $lastUpdate = \Carbon\Carbon::parse($lastData->created_at)->timezone('Asia/Jakarta');
                            $now = \Carbon\Carbon::now('Asia/Jakarta');

                            $diffInSeconds = $lastUpdate->diffInSeconds($now);
                            $isOnline = $diffInSeconds < 60;
                            $lastUpdateText = $lastUpdate->diffForHumans();
                        }
                    @endphp

                    {{-- TAMPILAN BADGE --}}
                    @if ($isLiveMode)
                        {{-- MODE LIVE --}}
                        @if ($isOnline)
                            <span class="badge bg-success rounded-pill small shadow-sm d-flex align-items-center">
                                <i class="fas fa-wifi me-1"></i> ONLINE
                                <span class="ms-2 ps-2 border-start border-white opacity-75"
                                    style="font-size: 0.85em;">5s</span>
                            </span>
                        @else
                            <span class="badge bg-danger rounded-pill small shadow-sm">
                                <i class="fas fa-wifi-slash me-1"></i> OFFLINE
                            </span>
                        @endif
                    @else
                        {{-- MODE HISTORY --}}
                        <span class="badge bg-secondary rounded-pill small shadow-sm">
                            <i class="fas fa-history me-1"></i> MODE HISTORY
                        </span>
                    @endif
                </div>

                <div class="mt-1">
                    <small class="text-muted d-block">Menampilkan Data: <strong>{{ $label }}</strong></small>

                    {{-- FITUR: Terakhir update CUMA DI LIVE MODE --}}
                    @if ($isLiveMode && $lastData)
                        <small class="text-primary fw-bold" style="font-size: 0.85rem;">
                            <i class="fas fa-clock fa-xs me-1"></i> Terakhir update: {{ $lastUpdateText }}
                        </small>
                    @endif
                </div>
            </div>

            {{-- MENU KANAN ATAS (THEME + USER DROPDOWN) --}}
            <div class="d-flex align-items-center gap-3">
                <div class="theme-toggle" onclick="toggleTheme()" title="Ganti Tema">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </div>
                
                {{-- DROPDOWN USER (PENGGANTI TOMBOL LOGOUT BIASA) --}}
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm rounded-pill px-3 py-2 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg text-primary"></i>
                        <span class="fw-bold small d-none d-md-block">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="min-width: 200px;">
                        <li><h6 class="dropdown-header text-muted text-uppercase small">Menu Pengguna</h6></li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-cog me-2 text-secondary w-20"></i> Profile
                            </a>
                        </li>

                        {{-- MENU CRUD USER (HANYA ADMIN) --}}
                        @if($isAdmin)
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('users.index') }}">
                                <i class="fas fa-users-cog me-2 text-primary w-20"></i> Kelola User
                            </a>
                        </li>
                        @endif

                        <li><hr class="dropdown-divider"></li>
                        
                        {{-- TOMBOL KELUAR (MERAH) --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger fw-bold py-2">
                                    <i class="fas fa-sign-out-alt me-2 w-20"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="control-bar mb-4">
            @if($isAdmin)
            <form action="{{ url('/dashboard') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-filter text-secondary me-2"></i>
                    <select name="period"
                        class="form-select form-select-sm border-secondary-subtle fw-bold text-secondary"
                        style="width: 130px;" onchange="this.form.submit()">
                        <option value="daily" {{ $period == 'daily' || $period == 'today' ? 'selected' : '' }}>Harian
                        </option>
                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                </div>

                @if ($period == 'daily' || $period == 'today')
                    <div class="input-group-custom" title="Pilih Tanggal">
                        <input type="date" name="date"
                            class="form-control form-control-sm border-secondary-subtle" value="{{ $dateInput }}"
                            onchange="this.form.submit()" style="max-width: 130px;">
                    </div>
                @endif

                @if ($period == 'weekly')
                    <div class="input-group-custom" title="Pilih Minggu">
                        <input type="week" name="week"
                            class="form-control form-control-sm border-secondary-subtle" value="{{ $weekInput }}"
                            onchange="this.form.submit()" style="max-width: 150px;">
                    </div>
                @endif

                @if ($period == 'monthly')
                    <div class="input-group-custom" title="Pilih Bulan">
                        <input type="month" name="month"
                            class="form-control form-control-sm border-secondary-subtle" value="{{ $monthInput }}"
                            onchange="this.form.submit()" style="max-width: 150px;">
                    </div>
                @endif

                @if (!$isLiveMode)
                    <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-danger rounded-circle ms-1"
                        title="Kembali ke Live Mode"><i class="fas fa-times"></i></a>
                @endif
            </form>
            @else
        <div class="alert alert-primary py-2 px-3 mb-0 d-flex align-items-center" style="max-width: fit-content;">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Mode Tamu:</strong> &nbsp;Anda hanya dapat melihat data Real-Time.
        </div>
        @endif

            <div class="d-flex align-items-center ms-auto">
                <span class="filter-label me-2 d-none d-md-block">Satuan:</span>
                <div class="btn-group btn-group-sm unit-btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="setUnit('C')"
                        id="btn-C">°C</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('R')"
                        id="btn-R">°R</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('F')"
                        id="btn-F">°F</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setUnit('K')"
                        id="btn-K">K</button>
                </div>
            </div>
        </div>

        @php $last = $data->last(); @endphp

        <div class="row mb-4 g-3">
            @if ($last)
                <div class="col-md-4">
                    <div class="card bg-primary text-white p-3 h-100"
                        style="background: linear-gradient(45deg, #0d6efd, #0a58ca);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 text-uppercase fw-bold mb-1">DHT22 (Utama)</h6>
                                <h1 class="display-4 fw-bold mb-0">
                                    <span id="card-t22"
                                        data-val="{{ $last->temp22 }}">{{ $last->temp22 }}</span><span
                                        class="fs-4 unit-label">°C</span>
                                </h1>
                            </div>
                            <i class="fas fa-temperature-high sensor-card-icon"></i>
                        </div>
                        <div class="mt-3 pt-3 border-top border-white-50">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-tint"></i> Kelembapan: {{ $last->hum22 }}%</span>
                                <span class="badge bg-white text-primary bg-opacity-75" title="Heat Index">
                                    <i class="fas fa-running"></i> Terasa: <span class="temp-data"
                                        data-val="{{ $stats['heat_index_22'] }}">{{ $stats['heat_index_22'] }}</span><span
                                        class="unit-label">°C</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-info text-white p-3 h-100"
                        style="background: linear-gradient(45deg, #0dcaf0, #0aa2c0);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 text-uppercase fw-bold mb-1">DHT11 (Sekunder)</h6>
                                <h1 class="display-4 fw-bold mb-0">
                                    <span id="card-t11"
                                        data-val="{{ $last->temp11 }}">{{ $last->temp11 }}</span><span
                                        class="fs-4 unit-label">°C</span>
                                </h1>
                            </div>
                            <i class="fas fa-thermometer-half sensor-card-icon"></i>
                        </div>
                        <div class="mt-3 pt-3 border-top border-white-50">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-tint"></i> Kelembapan: {{ $last->hum11 }}%</span>
                                <span class="badge bg-white text-info bg-opacity-75" title="Heat Index">
                                    <i class="fas fa-running"></i> Terasa: <span class="temp-data"
                                        data-val="{{ $stats['heat_index_11'] }}">{{ $stats['heat_index_11'] }}</span><span
                                        class="unit-label">°C</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white p-3 h-100"
                        style="background: {{ $last->ppm > 300 ? 'linear-gradient(45deg, #dc3545, #b02a37)' : ($last->ppm > 100 ? 'linear-gradient(45deg, #ffc107, #d39e00)' : 'linear-gradient(45deg, #198754, #146c43)') }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-white-50 text-uppercase fw-bold mb-1">Kualitas Udara</h6>
                                <h1 class="display-4 fw-bold mb-0">{{ $last->ppm }} <span
                                        class="fs-5">PPM</span></h1>
                            </div>
                            <i class="fas fa-wind sensor-card-icon"></i>
                        </div>
                        <div class="mt-3 pt-3 border-top border-white-50">
                            <strong>{{ $last->ppm > 300 ? 'BAHAYA ☠️' : ($last->ppm > 100 ? 'WASPADA ⚠️' : 'AMAN ✅') }}</strong>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-12">
                    <div class="alert alert-warning">Tidak ada data untuk periode ini.</div>
                </div>
            @endif
        </div>

        @if (!$isHistoryMode)  <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fas fa-sliders-h text-primary me-2"></i> Kontrol Perangkat NodeMCU
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center text-center">
                        
                        <div class="col-md-6 mb-3 mb-md-0 border-end">
                            <h6 class="text-uppercase fw-bold text-muted small mb-3">Mode Operasi</h6>
                            <div class="btn-group" role="group">
                                <button type="button" 
                                    class="btn {{ $setting->mode == 'auto' ? 'btn-primary' : 'btn-outline-primary' }} px-4 py-2" 
                                    onclick="updateMode('auto')">
                                    <i class="fas fa-robot me-2"></i> OTOMATIS
                                </button>
                                <button type="button" 
                                    class="btn {{ $setting->mode == 'manual' ? 'btn-danger' : 'btn-outline-danger' }} px-4 py-2" 
                                    onclick="updateMode('manual')">
                                    <i class="fas fa-hand-pointer me-2"></i> MANUAL
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Status: <strong>{{ strtoupper($setting->mode) }}</strong></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-uppercase fw-bold text-muted small mb-3">Kontrol Kipas (Manual)</h6>
                            
                            <div id="fan-wrapper" style="{{ $setting->mode == 'auto' ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                        class="btn {{ $setting->fan_status == 1 ? 'btn-success' : 'btn-outline-success' }} px-4 py-2" 
                                        onclick="updateFan(1)">
                                        <i class="fas fa-fan fa-spin me-2"></i> NYALA
                                    </button>
                                    <button type="button" 
                                        class="btn {{ $setting->fan_status == 0 ? 'btn-secondary' : 'btn-outline-secondary' }} px-4 py-2" 
                                        onclick="updateFan(0)">
                                        <i class="fas fa-power-off me-2"></i> MATI
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2">
                                @if($setting->mode == 'auto')
                                    <small class="text-danger fst-italic">
                                        <i class="fas fa-info-circle me-1"></i> Ubah ke mode Manual untuk mengontrol.
                                    </small>
                                @else
                                    <small class="text-muted">Status Kipas: <strong>{{ $setting->fan_status ? 'MENYALA' : 'MATI' }}</strong></small>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif 
        
        <div class="row mb-4 g-3">
    <div class="col-md-8">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header fw-bold py-3 border-bottom">
                <i class="fas fa-chart-bar text-primary me-2"></i> Statistik: {{ $label }}
            </div>
            <div class="card-body p-0">
                <div class="row g-0 h-100">
                    
                    <div class="col-md-4 border-end p-3 d-flex flex-column justify-content-center">
                        <small class="fw-bold d-block text-center text-uppercase mb-3 opacity-75" style="letter-spacing: 1px;">SUHU (DHT22)</small>
                        <div class="d-flex justify-content-between align-items-end px-2">
                            <div class="text-start">
                                <span class="d-block text-danger small fw-bold mb-1">MAX</span>
                                <span class="fs-4 fw-bold">
                                    <span class="temp-data" data-val="{{ $stats['max_temp'] }}">{{ $stats['max_temp'] }}</span>
                                    <small class="fs-6 opacity-75 unit-label">°C</small>
                                </span>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-primary small fw-bold mb-1">MIN</span>
                                <span class="fs-4 fw-bold">
                                    <span class="temp-data" data-val="{{ $stats['min_temp'] }}">{{ $stats['min_temp'] }}</span>
                                    <small class="fs-6 opacity-75 unit-label">°C</small>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 border-end p-3 d-flex flex-column justify-content-center">
                        <small class="fw-bold d-block text-center text-uppercase mb-3 opacity-75" style="letter-spacing: 1px;">GAS (MQ135)</small>
                        <div class="d-flex justify-content-between align-items-end px-2">
                            <div class="text-start">
                                <span class="d-block text-danger small fw-bold mb-1">MAX</span>
                                <span class="fs-4 fw-bold">{{ $stats['max_gas'] }}</span>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-success small fw-bold mb-1">AVG</span>
                                <span class="fs-4 fw-bold">{{ $stats['avg_gas'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 p-0">
                        @php
                            $gas = $stats['avg_gas'];
                            $heatIndex = $stats['heat_index_22'];

                            $statusText = 'AMAN';
                            $icon = 'fa-check-circle';
                            $bgClass = 'bg-success'; 
                            $detail = 'Kondisi Ideal';
                            // Tambah variabel text class khusus untuk blok ini
                            $blockTextClass = 'text-white'; 
                            $badgeColor = 'bg-white text-success';

                            if ($gas > 300 || $heatIndex >= 40) {
                                $statusText = 'BAHAYA';
                                $icon = 'fa-radiation';
                                $bgClass = 'bg-danger';
                                $blockTextClass = 'text-white';
                                $badgeColor = 'bg-white text-danger';
                                $detail = $heatIndex >= 40 ? 'Suhu Ekstrem!' : 'Gas Berbahaya!';

                            } elseif ($gas > 100 || $heatIndex > 27) {
                                $statusText = 'WASPADA';
                                $icon = 'fa-exclamation-triangle';
                                $bgClass = 'bg-warning';
                                // Khusus warning (kuning), teks harus hitam agar kontras
                                $blockTextClass = 'text-dark'; 
                                $badgeColor = 'bg-dark text-warning';
                                $detail = $heatIndex > 27 ? 'Gerah / Panas' : 'Udara Kotor';
                            }
                        @endphp

                        <div class="{{ $bgClass }} {{ $blockTextClass }} h-100 d-flex flex-column justify-content-center align-items-center text-center p-3" 
                             style="border-top-right-radius: var(--bs-card-inner-border-radius); border-bottom-right-radius: var(--bs-card-inner-border-radius);">
                            
                            <small class="fw-bold text-uppercase opacity-75 mb-2">KESIMPULAN</small>
                            
                            <h3 class="fw-bold mb-2">
                                <i class="fas {{ $icon }} me-1"></i> {{ $statusText }}
                            </h3>
                            
                            <span class="badge rounded-pill {{ $badgeColor }} px-3 py-1 shadow-sm">
                                {{ $detail }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body position-relative d-flex justify-content-center align-items-center flex-column py-4">
                <h6 class="fw-bold mb-3 opacity-75">Gas Meter (Rata-rata)</h6>
                
                <canvas id="gaugeChart" style="max-height: 150px; width: 100%;"></canvas>
                
                <div class="text-center mt-negative-3" style="margin-top: -30px;">
                    <h2 class="fw-bold mb-0 display-5">{{ $stats['avg_gas'] }}</h2>
                    <small class="fw-bold opacity-75">PPM</small>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line"></i> Grafik (100 Data Terakhir)</h6>
                <div class="chart-filter-group">
                    <button id="filter-all" class="filter-btn all active"
                        onclick="toggleDataset('all')">Semua</button>
                    <button id="filter-dht22" class="filter-btn dht22" onclick="toggleDataset(0)"><i
                            class="fas fa-temperature-high me-1"></i>DHT22</button>
                    <button id="filter-dht11" class="filter-btn dht11" onclick="toggleDataset(1)"><i
                            class="fas fa-thermometer-half me-1"></i>DHT11</button>
                    <button id="filter-mq135" class="filter-btn mq135" onclick="toggleDataset(2)"><i
                            class="fas fa-wind me-1"></i>Gas</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="sensorChart" style="height: 350px; width: 100%;"></canvas>
            </div>
        </div>

        @if($isAdmin)
        <div class="card mb-5">
            <div class="card-header fw-bold"><i class="fas fa-list"></i> 10 Data Terakhir dari {{ $label }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">Waktu</th>
                                <th>DHT22</th>
                                <th>DHT11</th>
                                <th>Gas (PPM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->sortByDesc('id')->take(10) as $d)
                                <tr>
                                    <td class="font-monospace">
                                        {{ $d->created_at->timezone('Asia/Jakarta')->format('d M H:i:s') }}</td>
                                    <td><span class="badge bg-primary rounded-pill"><span class="temp-data"
                                                data-val="{{ $d->temp22 }}">{{ $d->temp22 }}</span> <span
                                                class="unit-label">°C</span></span></td>
                                    <td><span class="badge bg-info text-dark rounded-pill"><span class="temp-data"
                                                data-val="{{ $d->temp11 }}">{{ $d->temp11 }}</span> <span
                                                class="unit-label">°C</span></span></td>
                                    <td class="fw-bold {{ $d->ppm > 300 ? 'text-danger' : 'text-success' }}">
                                        {{ $d->ppm }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 text-end py-3">
                <a href="{{ url('/export-excel') }}" class="btn btn-success btn-sm shadow-sm rounded-pill px-4"><i
                        class="fas fa-file-excel me-1"></i> Excel</a>
            </div>
        </div>
    </div>
    @endif

    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script>
        const labels = {!! json_encode(
            $data->pluck('created_at')->map(fn($d) => $d->timezone('Asia/Jakarta')->format($isLiveMode ? 'H:i:s' : 'd M H:i')),
        ) !!};
        const t22 = {!! json_encode($data->pluck('temp22')) !!};
        const t11 = {!! json_encode($data->pluck('temp11')) !!};
        const gas = {!! json_encode($data->pluck('ppm')) !!};
        const lastPpm = {{ $stats['last_ppm'] }};

        window.onload = function() {
            initDashboard(labels, t22, t11, gas, lastPpm);
        };
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    
    <script>
        const labels = {!! json_encode(
            $data->pluck('created_at')->map(fn($d) => $d->timezone('Asia/Jakarta')->format($isLiveMode ? 'H:i:s' : 'd M H:i')),
        ) !!};
        const t22 = {!! json_encode($data->pluck('temp22')) !!};
        const t11 = {!! json_encode($data->pluck('temp11')) !!};
        const gas = {!! json_encode($data->pluck('ppm')) !!};
        const lastPpm = {{ $stats['last_ppm'] ?? 0 }}; // Tambah null coalescing operator biar aman

        window.onload = function() {
            // Pastikan fungsi ini ada di dashboard.js untuk render grafik
            if(typeof initDashboard === 'function') {
                initDashboard(labels, t22, t11, gas, lastPpm);
            }
        };
    </script>

    <script>
        function updateMode(mode) {
            // Gunakan fetch agar lebih ringan dan modern
            fetch("{{ route('update.settings') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                },
                body: JSON.stringify({ mode: mode })
            })
            .then(response => {
                if (!response.ok) throw new Error("HTTP error " + response.status);
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    location.reload(); 
                } else {
                    alert("Gagal mengubah mode.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan koneksi.");
            });
        }

        function updateFan(status) {
            fetch("{{ route('update.settings') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ fan_status: status })
            })
            .then(response => {
                if (!response.ok) throw new Error("HTTP error " + response.status);
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert("Gagal mengubah status kipas.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan koneksi.");
            });
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>