<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorData;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SensorExport;

class SensorController extends Controller
{
    // 1. Fungsi untuk MENERIMA data dari ESP8266 (API)
    public function store(Request $request)
    {
        // Validasi data (opsional tapi disarankan)
        $request->validate([
            'temp22' => 'required|numeric',
            'hum22'  => 'required|numeric',
            'temp11' => 'required|numeric',
            'hum11'  => 'required|numeric',
            'ppm'    => 'required|numeric',
        ]);

        // Simpan ke database
        $sensor = SensorData::create($request->all());

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data'    => $sensor
        ], 201);
    }

    // 2. Fungsi untuk MENAMPILKAN data di Web (Dashboard)
    public function index()
    {
        // 1. Ambil Data Realtime (50 Terakhir untuk Grafik)
        $data = SensorData::latest()->take(50)->get()->sortBy('id');

        // 2. Ambil Data HARI INI saja untuk Statistik
        $todayData = SensorData::whereDate('created_at', now()->today())->get();

        // 3. Hitung Statistik (Cek apakah ada data hari ini?)
        if ($todayData->count() > 0) {
            $stats = [
                'max_temp' => $todayData->max('temp22'), // Suhu Tertinggi
                'min_temp' => $todayData->min('temp22'), // Suhu Terendah
                'avg_temp' => round($todayData->avg('temp22'), 1), // Rata-rata
                
                'max_gas'  => $todayData->max('ppm'),
                'avg_gas'  => round($todayData->avg('ppm'), 1),
            ];
        } else {
            // Kalau belum ada data hari ini, isi 0 semua
            $stats = [
                'max_temp' => 0, 'min_temp' => 0, 'avg_temp' => 0,
                'max_gas' => 0, 'avg_gas' => 0
            ];
        }

        return view('iotmonitoring', compact('data', 'stats'));
    }

    public function export()
    {
        return Excel::download(new SensorExport, 'laporan_sensor.xlsx');
    }
}