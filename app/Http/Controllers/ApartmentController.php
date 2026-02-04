<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index()
    {
        return Apartment::with(['residents', 'vehicles', 'owner'])->get();
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
        return $apartment->load(['residents', 'vehicles', 'owner']);
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
