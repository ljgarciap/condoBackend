<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    const CAPACITY_LIMIT = 2.0;
    const POINTS = [
        'car' => 1.25,
        'motorcycle' => 0.75,
    ];

    public function index(Request $request)
    {
        $query = Vehicle::with(['apartment.adminPayments']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('plate', 'like', "%{$search}%")
                  ->orWhereHas('apartment', function($q) use ($search) {
                      $q->where('number', 'like', "%{$search}%")
                        ->orWhere('block', 'like', "%{$search}%");
                  });
        }

        return $query->paginate($request->input('per_page', 5));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'plate' => 'required|string|unique:vehicles,plate',
            'type' => 'required|in:car,motorcycle',
            'description' => 'nullable|string',
        ]);

        $validated['plate'] = strtoupper($validated['plate']);
        $this->ensureCapacityNotExceeded($validated['apartment_id'], $validated['type']);

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle)
    {
        return $vehicle->load('apartment');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate' => 'sometimes|required|string|unique:vehicles,plate,' . $vehicle->id,
            'description' => 'nullable|string',
            // Type updates might be complicated due to capacity checks, handling simple updates for now.
        ]);

        if (isset($validated['plate'])) {
            $validated['plate'] = strtoupper($validated['plate']);
        }

        $vehicle->update($validated);

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(null, 204);
    }

    private function ensureCapacityNotExceeded($apartmentId, $newVehicleType)
    {
        $apartment = Apartment::with('vehicles')->findOrFail($apartmentId);
        
        $currentPoints = $apartment->vehicles->sum(function ($vehicle) {
            return self::POINTS[$vehicle->type] ?? 0;
        });

        $newPoints = self::POINTS[$newVehicleType];

        if (($currentPoints + $newPoints) > self::CAPACITY_LIMIT) {
            throw ValidationException::withMessages([
                'type' => ['Límite de capacidad excedido para el apartamento. Carro=1.25, Moto=0.75. Máximo=2.0'],
            ]);
        }
    }
}
