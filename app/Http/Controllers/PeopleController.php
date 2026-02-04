<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::with(['residents', 'user', 'visits']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        return $query->paginate(20);
    }

    public function showByDocument($document)
    {
        $person = Person::where('document', $document)->first();
        
        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        return response()->json($person);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'document' => 'required|string|unique:people',
            'document_type' => 'required|string|in:CC,TI,TE,PAS,PEP,RC',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $person = Person::create($validated);

        return response()->json($person, 201);
    }

    public function show(Person $person)
    {
        return $person->load(['residents.apartment', 'user', 'visits']);
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'document' => 'sometimes|required|string|unique:people,document,' . $person->id,
            'document_type' => 'sometimes|required|string|in:CC,TI,TE,PAS,PEP,RC',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $person->update($validated);

        return response()->json($person);
    }

    public function destroy(Person $person)
    {
        // Check if person is linked to a user or resident
        if ($person->user()->exists() || $person->residents()->exists()) {
            return response()->json([
                'message' => 'Cannot delete person linked to a user or resident'
            ], 422);
        }

        $person->delete();

        return response()->json(null, 204);
    }
}
