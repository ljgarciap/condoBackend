<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Return all users with their person and role data
        return User::with(['person', 'role'])->get();
    }
}
