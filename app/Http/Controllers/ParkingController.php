<?php

namespace App\Http\Controllers;

use App\Models\ParkingMovement;
use App\Models\Vehicle;
use App\Models\ParkingSetting;
use App\Models\AdminPayment;
use App\Models\CoexistenceReport;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ParkingController extends Controller
{
    public function status()
    {
        // Get settings
        $carCapacity = (int) ParkingSetting::where('key', 'car_capacity')->value('value') ?? 50;
        $motoCapacity = (int) ParkingSetting::where('key', 'motorcycle_capacity')->value('value') ?? 20;

        // Count current vehicles inside by type
        $occupiedCars = ParkingMovement::whereNull('exit_time')
            ->whereHas('vehicle', function($q) {
                $q->where('type', 'car');
            })->count();

        $occupiedMotos = ParkingMovement::whereNull('exit_time')
             ->whereHas('vehicle', function($q) {
                $q->where('type', 'motorcycle');
            })->count();

        return response()->json([
            'cars' => [
                'occupied' => $occupiedCars,
                'total' => $carCapacity,
                'available' => max(0, $carCapacity - $occupiedCars)
            ],
            'motorcycles' => [
                'occupied' => $occupiedMotos,
                'total' => $motoCapacity,
                'available' => max(0, $motoCapacity - $occupiedMotos)
            ],
            'settings' => [
                'car_capacity' => $carCapacity,
                'motorcycle_capacity' => $motoCapacity,
                'max_overdue_amount' => (float) ParkingSetting::where('key', 'max_overdue_amount')->value('value') ?? 0
            ]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'car_capacity' => 'required|integer|min:0',
            'motorcycle_capacity' => 'required|integer|min:0',
            'max_overdue_amount' => 'required|numeric|min:0',
        ]);

        ParkingSetting::updateOrCreate(
            ['key' => 'car_capacity'],
            ['value' => (string) $validated['car_capacity']]
        );

         ParkingSetting::updateOrCreate(
            ['key' => 'motorcycle_capacity'],
            ['value' => (string) $validated['motorcycle_capacity']]
        );

        ParkingSetting::updateOrCreate(
            ['key' => 'max_overdue_amount'],
            ['value' => (string) $validated['max_overdue_amount']]
        );

        return response()->json(['message' => 'Configuración actualizada correctamente']);
    }

    public function registerEntry(Request $request)
    {
        $validated = $request->validate([
            'plate' => 'required|string|exists:vehicles,plate',
        ]);

        $vehicle = Vehicle::where('plate', $validated['plate'])->firstOrFail();
        
        $this->checkAccessEligibility($vehicle->apartment_id);

        // Check if vehicle is already inside
        $lastMovement = ParkingMovement::where('vehicle_id', $vehicle->id)
            ->latest()
            ->first();

        if ($lastMovement && is_null($lastMovement->exit_time)) {
             throw ValidationException::withMessages([
                'plate' => ['El vehículo ya se encuentra registrado adentro.'],
            ]);
        }

        // Check Capacity for specific type
        $type = $vehicle->type; // 'car' or 'motorcycle'
        $settingKey = $type === 'car' ? 'car_capacity' : 'motorcycle_capacity';
        $capacity = (int) ParkingSetting::where('key', $settingKey)->value('value') ?? ($type === 'car' ? 50 : 20);

        $currentOccupied = ParkingMovement::whereNull('exit_time')
            ->whereHas('vehicle', function($q) use ($type) {
                $q->where('type', $type);
            })->count();

        if ($currentOccupied >= $capacity) {
            throw ValidationException::withMessages([
                'error' => ["Capacidad máxima excedida para " . ($type === 'car' ? 'carros' : 'motos') . "."],
            ]);
        }

        $movement = ParkingMovement::create([
            'vehicle_id' => $vehicle->id,
            'entry_time' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Entrada registrada correctamente',
            'data' => $movement
        ]);
    }

    public function registerExit(Request $request)
    {
        $validated = $request->validate([
            'plate' => 'required|string|exists:vehicles,plate',
        ]);

        $vehicle = Vehicle::where('plate', $validated['plate'])->firstOrFail();

        $movement = ParkingMovement::where('vehicle_id', $vehicle->id)
            ->whereNull('exit_time')
            ->latest()
            ->first();
        
        if (!$movement) {
            throw ValidationException::withMessages([
                'plate' => ['El vehículo no se encuentra registrado adentro.'],
            ]);
        }

        $movement->update([
            'exit_time' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Salida registrada correctamente',
            'data' => $movement
        ]);
    }

    public function history(Request $request)
    {
        $movements = ParkingMovement::with(['vehicle.apartment.owner.person'])
            ->latest()
            ->paginate($request->input('per_page', 20)); // Keep this higher or default to 5 if standardizing everything

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Vehicles currently inside that entered over 30 days ago
        $stationaryVehicles = Vehicle::with(['apartment.owner.person'])
            ->whereHas('movements', function($q) use ($thirtyDaysAgo) {
                $q->whereNull('exit_time')
                  ->where('entry_time', '<', $thirtyDaysAgo);
            })
            ->get();

        return response()->json([
            'movements' => $movements,
            'stationary' => $stationaryVehicles
        ]);
    }

    private function checkAccessEligibility($apartmentId)
    {
        // 1. Check for blocking Coexistence Reports
        $hasBlocks = CoexistenceReport::where('apartment_id', $apartmentId)
            ->where('status', 'active')
            ->where('blocks_entry', true)
            ->exists();
        
        if ($hasBlocks) {
            throw ValidationException::withMessages([
                'error' => ['Ingreso bloqueado por reporte de convivencia activo.'],
            ]);
        }

        // 2. Check for pending Admin Payments against configurable limit
        $maxOverdue = (float) ParkingSetting::where('key', 'max_overdue_amount')->value('value') ?? 0;
        
        $totalOverdue = AdminPayment::where('apartment_id', $apartmentId)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::now()->format('Y-m-d'))
            ->sum('amount');
        
        if ($totalOverdue > $maxOverdue) {
            $formattedTotal = number_format($totalOverdue, 0, ',', '.');
            $formattedMax = number_format($maxOverdue, 0, ',', '.');
            throw ValidationException::withMessages([
                'error' => ["Ingreso bloqueado. La deuda en mora ($ {$formattedTotal}) supera el límite permitido ($ {$formattedMax})."],
            ]);
        }
    }
}
