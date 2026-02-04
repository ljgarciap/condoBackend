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
        
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $vigilanteRole = \App\Models\Role::where('name', 'vigilante')->first();
        
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Vigilante',
            'email' => 'vigilante@example.com',
            'role_id' => $vigilanteRole->id,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
    }
}
