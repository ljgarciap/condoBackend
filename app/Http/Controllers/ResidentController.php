<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Resident;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with(['apartment', 'person.user.role']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('person', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        return $query->paginate($request->input('per_page', 5));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'name' => 'required|string',
            'document' => 'required|string',
            'document_type' => 'required|string|in:CC,TI,TE,PAS,PEP,RC',
            'email' => 'required_if:create_user,true|nullable|email',
            'phone' => 'nullable|string',
            'birthdate' => 'required|date',
            'create_user' => 'boolean',
            'password' => 'required_if:create_user,true|string|min:6',
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

        $resident = Resident::create([
            'apartment_id' => $validated['apartment_id'],
            'person_id' => $person->id,
            'birthdate' => $validated['birthdate']
        ]);

        if ($request->create_user) {
            $role = \App\Models\Role::where('name', 'resident')->first();
            \App\Models\User::updateOrCreate(
                ['person_id' => $person->id],
                [
                    'email' => $person->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                    'role_id' => $role->id,
                ]
            );
        }

        return response()->json($resident->load(['apartment', 'person']), 201);
    }

    public function show(Resident $resident)
    {
        return $resident->load(['apartment', 'person']);
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'apartment_id' => 'sometimes|required|exists:apartments,id',
            'name' => 'sometimes|required|string',
            'document' => 'sometimes|required|string',
            'document_type' => 'sometimes|required|string|in:CC,TI,TE,PAS,PEP,RC',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'birthdate' => 'sometimes|required|date',
            'create_user' => 'boolean',
            'password' => 'required_if:create_user,true|string|min:6',
        ]);

        if ($request->has(['name', 'document'])) {
            $resident->person->update([
                'name' => $validated['name'] ?? $resident->person->name,
                'document' => $validated['document'] ?? $resident->person->document,
                'document_type' => $validated['document_type'] ?? $resident->person->document_type,
                'email' => $validated['email'] ?? $resident->person->email,
                'phone' => $validated['phone'] ?? $resident->person->phone,
            ]);
        }

        $resident->update([
            'apartment_id' => $validated['apartment_id'] ?? $resident->apartment_id,
            'birthdate' => $validated['birthdate'] ?? $resident->birthdate,
        ]);

        if ($request->create_user) {
            $role = \App\Models\Role::where('name', 'resident')->first();
            \App\Models\User::updateOrCreate(
                ['person_id' => $resident->person_id],
                [
                    'email' => $validated['email'] ?? $resident->person->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                    'role_id' => $role->id,
                ]
            );
        }

        return response()->json($resident->load(['apartment', 'person']));
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json(null, 204);
    }
}
