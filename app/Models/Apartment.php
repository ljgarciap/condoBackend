<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $fillable = ['number', 'block', 'floor', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(Resident::class, 'owner_id');
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
