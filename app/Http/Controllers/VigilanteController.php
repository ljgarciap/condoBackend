<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VigilanteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'document' => 'required|string',
            'document_type' => 'required|string|in:CC,TI,TE,PAS,PEP,RC',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
        ]);

        $person = Person::updateOrCreate(
            ['document' => $validated['document']],
            [
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'email' => $validated['email'],
                'phone' => $validated['phone']
            ]
        );

        $role = Role::where('name', 'vigilante')->first();

        $user = User::create([
            'person_id' => $person->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        return response()->json($user->load('person'), 201);
    }
}
