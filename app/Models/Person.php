<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'document', 'document_type', 'birth_date', 'email', 'phone'];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
