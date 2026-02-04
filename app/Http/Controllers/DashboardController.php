<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\Vehicle;
use App\Models\ParkingMovement;
use App\Models\AdminPayment;
use App\Models\Visit;
use App\Models\Person;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();

        // Residents
        $totalResidents = Resident::count();

        // Apartments
        $totalApartments = Apartment::count();
        $completeApartments = Apartment::has('owner')->has('residents')->count();
        $incompleteApartments = $totalApartments - $completeApartments;

        // Vehicles
        $totalVehicles = Vehicle::count();
        $totalCars = Vehicle::where('type', 'car')->count();
        $totalMotos = Vehicle::where('type', 'motorcycle')->count();
        $vehiclesInside = ParkingMovement::whereNull('exit_time')->count();

        // Portfolio (Cartera)
        $apartmentsInDebt = Apartment::whereHas('adminPayments', function($q) use ($now) {
            $q->where('status', '!=', 'paid')
              ->where('due_date', '<', $now->format('Y-m-d'));
        })->count();

        $totalOverdueDebt = AdminPayment::where('status', '!=', 'paid')
            ->where('due_date', '<', $now->format('Y-m-d'))
            ->sum('amount');

        // Visitors
        $visitorsInside = Visit::whereNull('exit_at')->count();

        // Demographics
        $over70 = Person::whereNotNull('birth_date')
            ->where('birth_date', '<=', $now->copy()->subYears(70)->format('Y-m-d'))
            ->count();
        
        $under18 = Person::whereNotNull('birth_date')
            ->where('birth_date', '>', $now->copy()->subYears(18)->format('Y-m-d'))
            ->count();

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
                'cars' => $totalCars,
                'motos' => $totalMotos,
                'inside' => $vehiclesInside
            ],
            'portfolio' => [
                'in_debt_count' => $apartmentsInDebt,
                'total_overdue' => (float) $totalOverdueDebt
            ],
            'visitors' => [
                'inside' => $visitorsInside
            ],
            'demographics' => [
                'over_70' => $over70,
                'under_18' => $under18
            ]
        ]);
    }
}
