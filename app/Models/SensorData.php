<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';

    protected $fillable = [
        'temp22',
        'hum22',
        'temp11',
        'hum11',
        'ppm'
    ];
}