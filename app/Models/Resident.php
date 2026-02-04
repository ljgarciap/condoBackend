<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = ['apartment_id', 'name', 'document', 'email', 'phone', 'birthdate'];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
