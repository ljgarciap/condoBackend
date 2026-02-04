<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoexistenceReport extends Model
{
    protected $fillable = ['apartment_id', 'title', 'description', 'status', 'blocks_entry'];

    protected $casts = [
        'blocks_entry' => 'boolean',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
