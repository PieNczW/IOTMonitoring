<?php

namespace App\Exports;

use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SensorExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SensorData::all(); // Mengambil semua data
    }

    public function headings(): array
    {
        return [
            'ID',
            'Waktu Rekam',
            'Suhu DHT22 (°C)',
            'Kelembapan DHT22 (%)',
            'Suhu DHT11 (°C)',
            'Kelembapan DHT11 (%)',
            'Gas MQ135 (PPM)',
        ];
    }

    public function map($sensor): array
    {
        return [
            $sensor->id,
            $sensor->created_at,
            $sensor->temp22,
            $sensor->hum22,
            $sensor->temp11,
            $sensor->hum11,
            $sensor->ppm,
        ];
    }
}