<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = ['person_id', 'apartment_id', 'entry_at', 'exit_at', 'reason'];

    protected $casts = [
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
