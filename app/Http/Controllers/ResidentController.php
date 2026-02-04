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
        $query = Resident::with(['apartment', 'person']);

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
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'birthdate' => 'required|date',
        ]);

        $person = Person::updateOrCreate(
            ['document' => $validated['document']],
            [
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'email' => $request->email,
                'phone' => $request->phone
            ]
        );

        $resident = Resident::create([
            'apartment_id' => $validated['apartment_id'],
            'person_id' => $person->id,
            'birthdate' => $validated['birthdate']
        ]);

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

        return response()->json($resident->load(['apartment', 'person']));
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json(null, 204);
    }
}
