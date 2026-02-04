<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingMovement extends Model
{
    protected $fillable = ['vehicle_id', 'entry_time', 'exit_time'];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
