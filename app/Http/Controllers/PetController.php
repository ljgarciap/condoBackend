<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index(Request $request)
    {
        $query = Pet::with('apartment');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhereHas('apartment', function($q) use ($search) {
                      $q->where('number', 'like', "%{$search}%")
                        ->orWhere('block', 'like', "%{$search}%");
                  });
        }

        return $query->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'type' => 'required|string|in:dog,cat,other',
            'name' => 'required|string|max:255',
            'vaccinations_current' => 'boolean',
            'breed' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $pet = Pet::create($validated);

        return response()->json($pet, 201);
    }

    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'type' => 'sometimes|string|in:dog,cat,other',
            'name' => 'sometimes|string|max:255',
            'vaccinations_current' => 'boolean',
            'breed' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $pet->update($validated);

        return response()->json($pet);
    }

    public function destroy(Pet $pet)
    {
        $pet->delete();
        return response()->json(null, 204);
    }
}
