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
    // =========================================================================
    // 1. FUNGSI STORE (API dari ESP8266) - DIPERBAIKI LOGIKA SINKRONISASINYA
    // =========================================================================
    public function store(Request $request)
    {
        // 1. Validasi Input Data Sensor
        $request->validate([
            'temp22' => 'required|numeric',
            'hum22'  => 'required|numeric',
            'ppm'    => 'required|numeric',
        ]);

        // 2. Simpan Data Sensor ke Database
        $sensor = SensorData::create($request->all());

        // 3. Ambil Settingan Terakhir dari Database
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0, 'pwm_speed' => 0]);
        }

        // 4. LOGIKA SINKRONISASI DARI TELEGRAM (ESP) -> WEB
        // Jika ESP mengirim flag 'update_db' = 1, berarti ada perubahan dari Telegram.
        // Kita update database web agar sinkron dengan perintah Telegram.
        if ($request->has('update_db') && $request->update_db == 1) {
             if ($request->has('mode')) {
                 $setting->mode = $request->mode;
             }
             if ($request->has('fan_status')) {
                 $setting->fan_status = $request->fan_status;
             }
             if ($request->has('pwm_speed')) {
                 $setting->pwm_speed = $request->pwm_speed;
             }
             $setting->save();
        }

        // 5. KIRIM BALIKAN KE ESP (Respon JSON)
        // [PERBAIKAN PENTING]: 
        // Jika mode sedang AUTO, jangan kirim nilai PWM manual ke ESP.
        // Kirim 0 agar ESP tidak "terjebak" membaca nilai slider lama.
        $pwmToSend = ($setting->mode == 'auto') ? 0 : $setting->pwm_speed;

        return response()->json([
            'message'      => 'Success',
            'data'         => $sensor,
            'command_mode' => $setting->mode,
            'command_fan'  => $setting->fan_status,
            'command_pwm'  => $pwmToSend // <--- Nilai ini sudah aman dari konflik Auto
        ], 201);
    }

    // =========================================================================
    // 2. FUNGSI UPDATE SETTINGS (Dari Tombol Web Dashboard) - DIPERBAIKI
    // =========================================================================
    public function updateSettings(Request $request)
    {
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0, 'pwm_speed' => 0]);
        }
        
        // Update Mode (Auto/Manual)
        if ($request->has('mode')) {
            $setting->mode = $request->mode;
            
            // [PERBAIKAN]: Jika user klik "Auto", reset nilai PWM di database jadi 0
            // Ini untuk mencegah konflik saat nanti balik ke manual
            if ($request->mode == 'auto') {
                $setting->pwm_speed = 0;
            }
        }
        
        // Update Fan Status (Tombol ON/OFF)
        if ($request->has('fan_status')) {
            $setting->fan_status = $request->fan_status;
            // Jika user menekan tombol ON/OFF, otomatis anggap ini Mode Manual
            $setting->mode = 'manual';
        }

        // Update PWM (Slider)
        if ($request->has('pwm_speed')) {
            $setting->pwm_speed = $request->pwm_speed;
            // Jika user menggeser slider, otomatis anggap ini Mode Manual
            $setting->mode = 'manual';
        }

        $setting->save();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // 3. FUNGSI DASHBOARD (INDEX) - TIDAK DIUBAH (HANYA FORMATTING)
    // =========================================================================
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';

        $latestRealtime = SensorData::latest()->first(); 
        $isOnline = false;
        if ($latestRealtime) {
            $lastUpdate = Carbon::parse($latestRealtime->created_at)->timezone('Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');
            $isOnline = $lastUpdate->diffInSeconds($now) < 60;
        }

        // Filter Logic
        $period = $isAdmin ? $request->input('period', 'daily') : 'daily';
        $dateInput = $isAdmin ? $request->input('date', now()->format('Y-m-d')) : now()->format('Y-m-d');
        $weekInput = $request->input('week');
        $monthInput = $request->input('month');
        $label = "Hari Ini (Live)";
        $isHistoryMode = false;

        $startDate = now()->startOfDay();
        $endDate   = now()->endOfDay();

        if ($period == 'daily' && $dateInput != now()->format('Y-m-d')) {
            $startDate = Carbon::parse($dateInput)->startOfDay();
            $endDate   = Carbon::parse($dateInput)->endOfDay();
            $label     = "Tanggal " . $startDate->translatedFormat('d M Y');
            $isHistoryMode = true;
        } elseif ($period == 'weekly' && $weekInput) {
            $year = substr($weekInput, 0, 4);
            $week = substr($weekInput, 6);
            $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $label     = "Minggu ke-" . $week;
            $isHistoryMode = true;
        } elseif ($period == 'monthly' && $monthInput) {
            $startDate = Carbon::parse($monthInput)->startOfMonth();
            $endDate   = Carbon::parse($monthInput)->endOfMonth();
            $label     = "Bulan " . $startDate->translatedFormat('F Y');
            $isHistoryMode = true;
        }

        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->latest()->take(100)->get()->sortBy('id');
        
        $last = ($isHistoryMode) ? $data->last() : $latestRealtime;

        $stats = [
            'max_temp' => 0, 'min_temp' => 0, 'avg_temp' => 0, 
            'max_gas' => 0, 'avg_gas' => 0, 'last_ppm' => 0,
            'heat_index_22' => 0, 'heat_index_11' => 0
        ];

        if ($data->count() > 0) {
            $statsQuery = SensorData::whereBetween('created_at', [$startDate, $endDate]);
            if ($statsQuery->count() > 0) {
                $stats['max_temp'] = max($statsQuery->max('temp22'), $statsQuery->max('temp11'));
                $stats['min_temp'] = min($statsQuery->min('temp22'), $statsQuery->min('temp11'));
                $stats['avg_temp'] = round($statsQuery->avg('temp22'), 1);
                $stats['max_gas']  = $statsQuery->max('ppm');
                $stats['avg_gas']  = round($statsQuery->avg('ppm'), 1);
                $stats['last_ppm'] = $latestRealtime ? $latestRealtime->ppm : 0;
            }
        }

        if ($last) {
            $T = $last->temp22; $H = $last->hum22;
            $stats['heat_index_22'] = round($T + 0.5555 * (($H/100 * 6.105 * exp(17.27 * $T / (237.7 + $T))) - 10), 1);
            $T11 = $last->temp11; $H11 = $last->hum11;
            $stats['heat_index_11'] = round($T11 + 0.5555 * (($H11/100 * 6.105 * exp(17.27 * $T11 / (237.7 + $T11))) - 10), 1);
        }

        // Ambil Settingan Kontrol
        $setting = DeviceSetting::first();
        if (!$setting) {
            $setting = DeviceSetting::create(['mode' => 'auto', 'fan_status' => 0, 'pwm_speed' => 0]);
        }

        return view('dashboard', compact(
            'data', 'stats', 'label', 'period', 
            'dateInput', 'weekInput', 'monthInput', 
            'isOnline', 'isHistoryMode',
            'setting', 'last', 'isAdmin' 
        ));
    }

    // =========================================================================
    // 4. FUNGSI EXPORT EXCEL - TIDAK DIUBAH
    // =========================================================================
    public function export()
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses download laporan.');
        }
        return Excel::download(new SensorExport, 'laporan_sensor.xlsx');
    }
}