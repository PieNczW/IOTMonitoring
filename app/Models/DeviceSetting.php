<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSetting extends Model
{
    use HasFactory;
    
    // Izinkan kolom ini diisi
    protected $fillable = ['mode', 'fan_status'];
}