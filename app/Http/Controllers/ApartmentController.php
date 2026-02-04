<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Apartment::with(['residents.person', 'vehicles', 'owner.person']);

        if ($user->isResident()) {
            $resident = \App\Models\Resident::where('person_id', $user->person_id)->first();
            $query->where('id', $resident->apartment_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('block', 'like', "%{$search}%")
                  ->orWhereHas('owner.person', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($request->input('per_page', 5));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'block' => 'required|string',
            'floor' => 'nullable|integer',
            'owner_id' => 'nullable|exists:residents,id',
        ]);

        $apartment = Apartment::create($validated);

        return response()->json($apartment, 201);
    }

    public function show(Apartment $apartment)
    {
        return $apartment->load(['residents.person', 'vehicles', 'owner.person']);
    }

    public function update(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'number' => 'sometimes|required|string',
            'block' => 'sometimes|required|string',
            'floor' => 'nullable|integer',
            'owner_id' => 'nullable|exists:residents,id',
        ]);

        $apartment->update($validated);

        return response()->json($apartment);
    }

    public function destroy(Apartment $apartment)
    {
        $apartment->delete();

        return response()->json(null, 204);
    }
}
