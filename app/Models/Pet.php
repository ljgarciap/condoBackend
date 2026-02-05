<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $fillable = ['apartment_id', 'type', 'name', 'vaccinations_current', 'breed', 'description'];

    protected $casts = [
        'vaccinations_current' => 'boolean',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
