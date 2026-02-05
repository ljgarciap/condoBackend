<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['apartment_id', 'plate', 'type', 'description', 'unique_id'];

    public static function booted()
    {
        static::creating(function ($vehicle) {
            $vehicle->unique_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function movements()
    {
        return $this->hasMany(ParkingMovement::class);
    }
}
