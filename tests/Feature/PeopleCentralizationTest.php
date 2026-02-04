<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Person;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleCentralizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'vigilante']);
    }

    public function test_user_registration_creates_person()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document' => '12345678',
            'document_type' => 'CC',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('people', [
            'name' => 'John Doe',
            'document' => '12345678',
            'document_type' => 'CC'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'person_id' => Person::where('document', '12345678')->first()->id
        ]);
    }

    public function test_resident_creation_links_to_existing_person()
    {
        $person = Person::create([
            'name' => 'Existing Person',
            'document' => '88888888',
            'document_type' => 'CC'
        ]);

        $apt = Apartment::create(['number' => '101', 'block' => 'A']);
        
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->first()->id,
            'person_id' => Person::factory()->create()->id
        ]);

        $response = $this->actingAs($admin)->postJson('/api/residents', [
            'apartment_id' => $apt->id,
            'name' => 'Updated Name', // This should update the person
            'document' => '88888888',
            'document_type' => 'CC',
            'birthdate' => '1990-01-01'
        ]);

        $response->assertStatus(201);
        $this->assertEquals(1, Person::where('document', '88888888')->count());
        $this->assertEquals('Updated Name', Person::where('document', '88888888')->first()->name);
        
        $this->assertDatabaseHas('residents', [
            'person_id' => $person->id,
            'apartment_id' => $apt->id
        ]);
    }

    public function test_visit_creation_creates_person_and_visit()
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->first()->id,
            'person_id' => Person::factory()->create()->id
        ]);

        $apt = Apartment::create(['number' => '102', 'block' => 'B']);

        $response = $this->actingAs($admin)->postJson('/api/visits', [
            'document' => '99999999',
            'document_type' => 'CC',
            'name' => 'Visitor Name',
            'apartment_id' => $apt->id,
            'reason' => 'Meeting'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('people', ['document' => '99999999']);
        $this->assertDatabaseHas('visits', [
            'person_id' => Person::where('document', '99999999')->first()->id,
            'reason' => 'Meeting'
        ]);
    }
}
