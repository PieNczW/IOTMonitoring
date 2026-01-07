<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('sensor_data', function (Blueprint $table) {
        $table->id();
        $table->float('temp22'); // Suhu DHT22
        $table->float('hum22');  // Lembab DHT22
        $table->float('temp11'); // Suhu DHT11
        $table->float('hum11');  // Lembab DHT11
        $table->float('ppm');    // Gas MQ135
        $table->timestamps();    // Otomatis buat created_at & updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
