<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Reading extends Model
{
    use HasFactory;


    protected $fillable = ['device_id', 't22', 'h22', 't11', 'h11', 'mq135', 'mq_right', 'raw'];
}
