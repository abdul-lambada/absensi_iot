<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    protected $table = 'perangkat';

    protected $fillable = [
        'nama_perangkat',
        'lokasi_perangkat',
        'status_perangkat',
        'device_uid',
        'api_key',
    ];
}
