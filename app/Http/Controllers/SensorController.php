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
        // Ambil 50 data terakhir (supaya grafik tidak terlalu padat)
        $data = SensorData::latest()->take(50)->get()->sortBy('id'); // sort by id asc agar grafik urut

        return view('dashboard', compact('data'));
    }

    public function export()
    {
        return Excel::download(new SensorExport, 'laporan_sensor.xlsx');
    }
}