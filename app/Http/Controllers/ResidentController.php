<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index()
    {
        return Resident::with('apartment')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'name' => 'required|string',
            'document' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'birthdate' => 'required|date',
        ]);

        $resident = Resident::create($validated);

        return response()->json($resident, 201);
    }

    public function show(Resident $resident)
    {
        return $resident->load('apartment');
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'apartment_id' => 'sometimes|required|exists:apartments,id',
            'name' => 'sometimes|required|string',
            'document' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'birthdate' => 'sometimes|required|date',
        ]);

        $resident->update($validated);

        return response()->json($resident);
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return response()->json(null, 204);
    }
}
