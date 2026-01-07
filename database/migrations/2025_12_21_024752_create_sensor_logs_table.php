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
    Schema::create('sensor_logs', function ($table) {
        $table->id();
        $table->float('temp_dht22');
        $table->float('hum_dht22');
        $table->float('temp_dht11');
        $table->float('hum_dht11');
        $table->float('gas_ppm');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_logs');
    }
};
