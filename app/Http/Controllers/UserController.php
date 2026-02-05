<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['person', 'role']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('person', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('document', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('per_page')) {
            return $query->paginate($request->input('per_page'));
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'document' => 'required|string',
            'document_type' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'role_name' => 'required|exists:roles,name'
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

        $role = Role::where('name', $validated['role_name'])->first();

        $user = User::create([
            'person_id' => $person->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        return response()->json($user->load('person', 'role'), 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'document' => 'required|string',
            'document_type' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'role_name' => 'required|exists:roles,name'
        ]);

        $person = $user->person;
        if ($person) {
            $person->update([
                'name' => $validated['name'],
                'document' => $validated['document'],
                'document_type' => $validated['document_type'],
                'email' => $validated['email'],
                'phone' => $validated['phone']
            ]);
        }

        $role = Role::where('name', $validated['role_name'])->first();

        $userData = [
            'email' => $validated['email'],
            'role_id' => $role->id
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return response()->json($user->load('person', 'role'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
