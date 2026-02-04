<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCapacityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role if not exists
        if (Role::count() == 0) {
             Role::create(['name' => 'admin']);
        }
    }

    public function test_vehicle_capacity_logic()
    {
        $user = User::factory()->create(['role_id' => Role::first()->id]);
        $apartment = Apartment::create([
            'number' => '101',
            'block' => 'A',
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        // 1. Add Car (1.25) -> Total 1.25
        $response = $this->postJson('/api/vehicles', [
            'apartment_id' => $apartment->id,
            'plate' => 'CAR001',
            'type' => 'car',
        ]);
        $response->assertStatus(201);

        // 2. Add Motorcycle (0.75) -> Total 2.0
        $response = $this->postJson('/api/vehicles', [
            'apartment_id' => $apartment->id,
            'plate' => 'MOTO001',
            'type' => 'motorcycle',
        ]);
        $response->assertStatus(201);

        // 3. Try Add another Motorcycle (0.75) -> Total 2.75 (> 2.0) -> Should Fail
        $response = $this->postJson('/api/vehicles', [
            'apartment_id' => $apartment->id,
            'plate' => 'MOTO002',
            'type' => 'motorcycle',
        ]);
        $response->assertStatus(422) // Validation Error
                 ->assertJsonValidationErrors(['type']);
    }

    public function test_parking_access_blocked_by_debt()
    {
         $user = User::factory()->create(['role_id' => Role::first()->id]);
         $apartment = Apartment::create([
             'number' => '102',
             'block' => 'A',
             'user_id' => $user->id
         ]);
         $vehicle = Vehicle::create([
             'apartment_id' => $apartment->id,
             'plate' => 'CAR002',
             'type' => 'car'
         ]);

         $this->actingAs($user);

         // Add Overdue Payment
         \App\Models\AdminPayment::create([
             'apartment_id' => $apartment->id,
             'amount' => 100,
             'due_date' => now()->subDays(1), // Overdue
             'status' => 'pending'
         ]);

         // Try Entry
         $response = $this->postJson('/api/parking/entry', [
             'plate' => 'CAR002'
         ]);
         
         $response->assertStatus(422)
                  ->assertJsonFragment(['Entry blocked due to overdue administration payments.']);
    }
}
