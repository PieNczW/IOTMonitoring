<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use App\Models\DeviceSetting;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SensorExport;
use Carbon\Carbon;

class SensorController extends Controller
{
    public function index(Request $request)
{
    // 1. Setup Timezone
    date_default_timezone_set('Asia/Jakarta');
    
    // --- CEK SIAPA YANG LOGIN ---
    $user = auth()->user();
    $isAdmin = $user->role === 'admin'; // True jika admin

    // --- LOGIKA DATA REALTIME ---
    $latestRealtime = SensorData::latest()->first(); 
    $isOnline = false;
    if ($latestRealtime) {
        $lastUpdate = Carbon::parse($latestRealtime->created_at)->timezone('Asia/Jakarta');
        $now = Carbon::now('Asia/Jakarta');
        $isOnline = $lastUpdate->diffInSeconds($now) < 60;
    }

    // --- LOGIKA FILTER (DIBATASI BERDASARKAN ROLE) ---
    // Default values
    $period = 'daily';
    $dateInput = now()->format('Y-m-d');
    $weekInput = null;
    $monthInput = null;
    $label = "Hari Ini (Live)";
    $isHistoryMode = false;

    // JIKA ADMIN: Boleh pakai input dari Request (Filter bebas)
    if ($isAdmin) {
        $period = $request->input('period', 'daily');
        $dateInput = $request->input('date', now()->format('Y-m-d'));
        $weekInput = $request->input('week');
        $monthInput = $request->input('month');
    } 
    // JIKA USER BIASA: Abaikan request, paksa ke default (Hari ini)
    else {
        // Variable tetap default seperti di atas.
        // User tidak bisa mengubah $period atau $dateInput lewat URL.
    }

    // --- PROSES QUERY DATA (Sama seperti kodemu sebelumnya) ---
    $startDate = now()->startOfDay();
    $endDate   = now()->endOfDay();

    if ($period == 'daily') {
        if ($dateInput && $dateInput != now()->format('Y-m-d')) {
            $startDate = Carbon::parse($dateInput)->startOfDay();
            $endDate   = Carbon::parse($dateInput)->endOfDay();
            $label     = "Tanggal " . $startDate->translatedFormat('d M Y');
            $isHistoryMode = true;
        }
    } elseif ($period == 'weekly') { // Admin Only logic effectively
        if ($weekInput) {
            $year = substr($weekInput, 0, 4);
            $week = substr($weekInput, 6);
            $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $label     = "Minggu ke-" . $week;
            $isHistoryMode = true;
        }
    } elseif ($period == 'monthly') { // Admin Only logic effectively
        if ($monthInput) {
            $startDate = Carbon::parse($monthInput)->startOfMonth();
            $endDate   = Carbon::parse($monthInput)->endOfMonth();
            $label     = "Bulan " . $startDate->translatedFormat('F Y');
            $isHistoryMode = true;
        }
    }

        // 5. Query Data (Untuk Grafik)
        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->latest()->take(100)->get()->sortBy('id');
        
        // FIX: $last untuk tampilan kartu kita pakai $latestRealtime (jika live mode)
        // Jika sedang mode history (lihat tanggal lama), baru pakai data dari grafik
        $last = ($isHistoryMode) ? $data->last() : $latestRealtime;

        // Hitung Heat Index (Data Kartu)
        $hi22 = 0; $hi11 = 0;
        if ($last) {
            $T = $last->temp22; $H = $last->hum22;
            $hi22 = $T + 0.5555 * (($H/100 * 6.105 * exp(17.27 * $T / (237.7 + $T))) - 10);
            $T11 = $last->temp11; $H11 = $last->hum11;
            $hi11 = $T11 + 0.5555 * (($H11/100 * 6.105 * exp(17.27 * $T11 / (237.7 + $T11))) - 10);
        }

        // === STATISTIK (Berdasarkan Filter Waktu) ===
        $statsQuery = SensorData::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($statsQuery->count() > 0) {
            $max22 = $statsQuery->max('temp22');
            $max11 = $statsQuery->max('temp11');
            $globalMax = max($max22, $max11); 

            $min22 = $statsQuery->min('temp22');
            $min11 = $statsQuery->min('temp11');
            $globalMin = min($min22, $min11); 

            $stats = [
                'max_temp' => $globalMax, 
                'min_temp' => $globalMin,
                'avg_temp' => round($statsQuery->avg('temp22'), 1), 
                'max_gas'  => $statsQuery->max('ppm'),
                'avg_gas'  => round($statsQuery->avg('ppm'), 1),
                // FIX: last_ppm pakai data realtime agar Gauge Chart akurat
                'last_ppm' => $latestRealtime ? $latestRealtime->ppm : 0, 
                'heat_index_22' => round($hi22, 1),
                'heat_index_11' => round($hi11, 1),
            ];
        } else {
            $stats = [
                'max_temp' => 0, 'min_temp' => 0, 'avg_temp' => 0, 
                'max_gas' => 0, 'avg_gas' => 0, 'last_ppm' => 0,
                'heat_index_22' => 0, 'heat_index_11' => 0
            ];
        }

        // Ambil Settingan Kontrol
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0]);
        }

        // Kita kirim variabel $last terpisah untuk View agar tidak bingung
        return view('dashboard', compact(
            'data', 'stats', 'label', 'period', 
            'dateInput', 'weekInput', 'monthInput', 
            'isOnline', 'isHistoryMode',
            'setting', 'last', 'isAdmin' 
        ));
    }

    // --- [UPDATE] STORE: TERIMA DATA & SINKRONISASI DARI TELEGRAM ---
    public function store(Request $request) 
    { 
        // 1. Simpan Data Sensor
        SensorData::create($request->all()); 

        // 2. Ambil Status Tombol Terakhir
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0]);
        }

        // --- [LOGIKA BARU] Update Database jika ada perintah dari Telegram ---
        // Jika ESP mengirim 'update_db' = 1, berarti dia minta database diupdate
        if ($request->has('update_db') && $request->update_db == 1) {
            if ($request->has('mode')) {
                $setting->mode = $request->mode;
            }
            if ($request->has('fan_status')) {
                $setting->fan_status = $request->fan_status;
            }
            $setting->save(); // Simpan perubahan dari Telegram ke Database
        }

        // 3. Return JSON (Kirim Balik Status Terbaru ke ESP)
        return response()->json([
            'message'      => 'Success',
            'command_mode' => $setting->mode,
            'command_fan'  => $setting->fan_status
        ], 201); 
    }

    // --- FUNGSI UPDATE TOMBOL ---
    public function updateSettings(Request $request)
    {
        
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0]);
        }
        
        if ($request->has('mode')) {
            $setting->mode = $request->mode;
        }
        
        if ($request->has('fan_status')) {
            $setting->fan_status = $request->fan_status;
        }

        $setting->save();

        return response()->json(['success' => true]);
    }

    public function export() { 
    // TOLAK JIKA BUKAN ADMIN
    if (auth()->user()->role !== 'admin') {
        abort(403, 'Anda tidak memiliki akses download laporan.');
    }
    return Excel::download(new SensorExport, 'laporan_sensor.xlsx');       
    }
}