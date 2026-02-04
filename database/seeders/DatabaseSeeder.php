<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\Role::firstOrCreate(['name' => 'admin']);
        \App\Models\Role::firstOrCreate(['name' => 'vigilante']);
        \App\Models\Role::firstOrCreate(['name' => 'resident']);
        
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $vigilanteRole = \App\Models\Role::where('name', 'vigilante')->first();
        
        // Admin Person
        $adminPerson = \App\Models\Person::updateOrCreate(
            ['document' => '12345678'],
            [
                'name' => 'Administrador',
                'document_type' => 'CC',
                'email' => 'admin@example.com',
                'phone' => '3001234567'
            ]
        );

        User::updateOrCreate(
            ['person_id' => $adminPerson->id],
            [
                'email' => 'admin@example.com',
                'role_id' => $adminRole->id,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Vigilante Person
        $vigilantePerson = \App\Models\Person::updateOrCreate(
            ['document' => '87654321'],
            [
                'name' => 'Vigilante',
                'document_type' => 'CC',
                'email' => 'vigilante@example.com',
                'phone' => '3007654321'
            ]
        );

        User::updateOrCreate(
            ['person_id' => $vigilantePerson->id],
            [
                'email' => 'vigilante@example.com',
                'role_id' => $vigilanteRole->id,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // Create some sample apartments, people and residents
        $apt = \App\Models\Apartment::updateOrCreate(['number' => '101', 'block' => 'A'], ['owner_id' => null]);
        
        $residentPerson = \App\Models\Person::updateOrCreate(
            ['document' => '10203040'],
            [
                'name' => 'Luis Residente',
                'document_type' => 'CC',
                'email' => 'luis@example.com',
                'phone' => '3110000000'
            ]
        );

        $resident = \App\Models\Resident::create([
            'apartment_id' => $apt->id,
            'person_id' => $residentPerson->id,
            'birthdate' => '1990-01-01'
        ]);

        $vehicle = \App\Models\Vehicle::create([
            'apartment_id' => $apt->id,
            'plate' => 'OLD-999',
            'type' => 'car',
            'description' => 'Test Stationary'
        ]);

        // Stationary movement (35 days ago)
        \App\Models\ParkingMovement::create([
            'vehicle_id' => $vehicle->id,
            'entry_time' => \Carbon\Carbon::now()->subDays(35),
        ]);

        $vehicle2 = \App\Models\Vehicle::create([
            'apartment_id' => $apt->id,
            'plate' => 'NEW-111',
            'type' => 'motorcycle',
            'description' => 'Test Recent'
        ]);

        // Recent movement (2 hours ago)
        \App\Models\ParkingMovement::create([
            'vehicle_id' => $vehicle2->id,
            'entry_time' => \Carbon\Carbon::now()->subHours(2),
        ]);

        // Sample Visit
        $person = \App\Models\Person::create([
            'name' => 'John Visitor',
            'document' => 'V-12345',
            'phone' => '555-0000'
        ]);

        \App\Models\Visit::create([
            'person_id' => $person->id,
            'apartment_id' => $apt->id,
            'entry_at' => \Carbon\Carbon::now()->subHours(3),
            'reason' => 'Meeting property owner'
        ]);
    }
}
