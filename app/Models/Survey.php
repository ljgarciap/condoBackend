<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = ['title', 'questions', 'is_active'];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];
}
