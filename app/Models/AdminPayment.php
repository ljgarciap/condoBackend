<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPayment extends Model
{
    protected $fillable = ['apartment_id', 'amount', 'due_date', 'paid_date', 'status', 'description'];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
