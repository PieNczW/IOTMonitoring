<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SensorExport;
use Carbon\Carbon;

class SensorController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Timezone
        date_default_timezone_set('Asia/Jakarta');

        // 2. Cek Status Online/Offline
        $lastSensorStatus = SensorData::latest()->first();
        $isOnline = false;
        
        if ($lastSensorStatus) {
            $lastUpdate = Carbon::parse($lastSensorStatus->created_at)->timezone('Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');
            $isOnline = $lastUpdate->diffInSeconds($now) < 30;
        }

        // 3. Ambil Input Filter
        $period = $request->input('period', 'daily');
        $dateInput = $request->input('date');
        $weekInput = $request->input('week');
        $monthInput = $request->input('month');

        // 4. Logika Penentuan Waktu
        $startDate = now()->startOfDay();
        $endDate   = now()->endOfDay();
        $label     = "Hari Ini";
        $isHistoryMode = false;

        // A. MODE HARIAN
        if ($period == 'daily') {
            if ($dateInput) {
                $startDate = Carbon::parse($dateInput)->startOfDay();
                $endDate   = Carbon::parse($dateInput)->endOfDay();
                $label     = "Tanggal " . $startDate->translatedFormat('d M Y');
                $isHistoryMode = true;
            } else {
                $label = "Hari Ini (Live)";
                $dateInput = now()->format('Y-m-d');
            }
        }
        
        // B. MODE MINGGUAN
        elseif ($period == 'weekly') {
            if ($weekInput) {
                $year = substr($weekInput, 0, 4);
                $week = substr($weekInput, 6);
                $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
                $endDate   = Carbon::now()->setISODate($year, $week)->endOfWeek();
                $label     = "Minggu ke-" . $week . " (" . $startDate->format('d/m') . " - " . $endDate->format('d/m') . ")";
                $isHistoryMode = true;
            } else {
                $startDate = now()->startOfWeek();
                $endDate   = now()->endOfWeek();
                $label     = "Minggu Ini";
                $weekInput = now()->format('Y-\WW');
            }
        }

        // C. MODE BULANAN
        elseif ($period == 'monthly') {
            if ($monthInput) {
                $startDate = Carbon::parse($monthInput)->startOfMonth();
                $endDate   = Carbon::parse($monthInput)->endOfMonth();
                $label     = "Bulan " . $startDate->translatedFormat('F Y');
                $isHistoryMode = true;
            } else {
                $startDate = now()->startOfMonth();
                $endDate   = now()->endOfMonth();
                $label     = "Bulan Ini";
                $monthInput = now()->format('Y-m');
            }
        }

        // 5. Query Data
        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->latest()->take(100)->get()->sortBy('id');
        
        $last = $data->last(); 

        // Hitung Heat Index
        $hi22 = 0; $hi11 = 0;
        if ($last) {
            $T = $last->temp22; $H = $last->hum22;
            $hi22 = $T + 0.5555 * (($H/100 * 6.105 * exp(17.27 * $T / (237.7 + $T))) - 10);
            $T11 = $last->temp11; $H11 = $last->hum11;
            $hi11 = $T11 + 0.5555 * (($H11/100 * 6.105 * exp(17.27 * $T11 / (237.7 + $T11))) - 10);
        }

        // === PERBAIKAN LOGIKA STATISTIK (GABUNGAN DHT22 & DHT11) ===
        $statsQuery = SensorData::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($statsQuery->count() > 0) {
            // Cari Max/Min dari kedua sensor
            $max22 = $statsQuery->max('temp22');
            $max11 = $statsQuery->max('temp11');
            $globalMax = max($max22, $max11); // Ambil yg paling tinggi

            $min22 = $statsQuery->min('temp22');
            $min11 = $statsQuery->min('temp11');
            $globalMin = min($min22, $min11); // Ambil yg paling rendah

            $stats = [
                'max_temp' => $globalMax, 
                'min_temp' => $globalMin,
                'avg_temp' => round($statsQuery->avg('temp22'), 1), // Avg tetap DHT22 (sensor utama)
                'max_gas'  => $statsQuery->max('ppm'),
                'avg_gas'  => round($statsQuery->avg('ppm'), 1),
                'last_ppm' => $last ? $last->ppm : 0,
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

        return view('dashboard', compact(
            'data', 'stats', 'label', 'period', 
            'dateInput', 'weekInput', 'monthInput', 
            'isOnline', 'isHistoryMode'
        ));
    }

    public function store(Request $request) { SensorData::create($request->all()); return response()->json(['message' => 'Success'], 201); }
    public function export() { return Excel::download(new SensorExport, 'laporan_sensor.xlsx'); }
}