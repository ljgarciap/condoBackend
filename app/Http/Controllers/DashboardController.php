<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Apartment;
use App\Models\Vehicle;
use App\Models\AdminPayment;
use App\Models\Visit;
use App\Models\ParkingMovement;
use App\Models\Pet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            // Residents
            $totalResidents = Resident::count();

            // Apartments
            $totalApartments = Apartment::count();
            $completeApartments = Apartment::whereNotNull('block')
                ->whereNotNull('number')
                ->whereNotNull('floor')
                ->count();
            $incompleteApartments = $totalApartments - $completeApartments;

            // Vehicles
            $totalVehicles = Vehicle::count();
            $cars = Vehicle::where('type', 'car')->count();
            $motos = Vehicle::where('type', 'motorcycle')->count();
            $vehiclesInside = ParkingMovement::whereNull('exit_time')->count();
            $carsInside = ParkingMovement::whereNull('exit_time')
                ->whereHas('vehicle', function($q) {
                    $q->where('type', 'car');
                })->count();
            $motosInside = ParkingMovement::whereNull('exit_time')
                ->whereHas('vehicle', function($q) {
                    $q->where('type', 'motorcycle');
                })->count();

            // Portfolio (Cartera)
            $overduePayments = AdminPayment::where('status', 'overdue')->get();
            $totalOverdue = $overduePayments->sum('amount');
            $inDebtCount = $overduePayments->pluck('apartment_id')->unique()->count();

            // Visitors
            $visitorsInside = Visit::whereNull('exit_at')->count();

            // Demographics
            $now = Carbon::now();
            $residents = Resident::all();
            $over70 = $residents->filter(function($r) use ($now) {
                return $r->birthdate && Carbon::parse($r->birthdate)->age >= 70;
            })->count();
            $under18 = $residents->filter(function($r) use ($now) {
                return $r->birthdate && Carbon::parse($r->birthdate)->age < 18;
            })->count();

            return response()->json([
                'residents' => [
                    'total' => $totalResidents
                ],
                'apartments' => [
                    'total' => $totalApartments,
                    'complete' => $completeApartments,
                    'incomplete' => $incompleteApartments
                ],
                'vehicles' => [
                    'total' => $totalVehicles,
                    'cars' => $cars,
                    'motos' => $motos,
                    'inside' => $vehiclesInside,
                    'cars_inside' => $carsInside,
                    'motos_inside' => $motosInside
                ],
                'portfolio' => [
                    'total_overdue' => $totalOverdue,
                    'in_debt_count' => $inDebtCount
                ],
                'visitors' => [
                    'inside' => $visitorsInside
                ],
                'demographics' => [
                    'over_70' => $over70,
                    'under_18' => $under18
                ],
                'pets' => [
                    'total' => \App\Models\Pet::count(),
                    'dogs' => \App\Models\Pet::where('type', 'dog')->count(),
                    'cats' => \App\Models\Pet::where('type', 'cat')->count(),
                    'vaccinated' => \App\Models\Pet::where('vaccinations_current', true)->count(),
                    'unvaccinated' => \App\Models\Pet::where('vaccinations_current', false)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading dashboard stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
