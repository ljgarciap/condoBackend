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

    public function adminPayments()
    {
        return $this->hasMany(AdminPayment::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    protected $appends = ['debt_status'];

    public function getDebtStatusAttribute()
    {
        $overdue = $this->adminPayments()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->sum('amount');

        $pending = $this->adminPayments()
            ->where('status', '!=', 'paid')
            ->sum('amount');

        return [
            'is_up_to_date' => $pending <= 0,
            'overdue_amount' => $overdue,
            'total_pending' => $pending,
            'status' => $overdue > 0 ? 'overdue' : ($pending > 0 ? 'pending' : 'up_to_date')
        ];
    }
}
