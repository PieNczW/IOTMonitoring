<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::create('readings', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->float('t22')->nullable();
            $table->float('h22')->nullable();
            $table->float('t11')->nullable();
            $table->float('h11')->nullable();
            $table->float('mq135')->nullable();
            $table->float('mq_right')->nullable();
            $table->text('raw')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('readings');
    }
};
