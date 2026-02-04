<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['apartment_id', 'plate', 'type', 'description'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
