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

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@techub.id',
            'password' => bcrypt('password'),
        ]);

        \App\Models\BusinessUnit::insert([
            ['code' => 'UB1', 'name' => 'PT Binawan Inti Teknologi'],
            ['code' => 'UB2', 'name' => 'Universitas Binawan'],
            ['code' => 'UB3', 'name' => 'Klinik Binawan'],
        ]);

        \App\Models\Laboratory::insert([
            ['name' => 'Labkom 1', 'capacity' => 29, 'status' => 'active'],
            ['name' => 'Labkom 2', 'capacity' => 32, 'status' => 'active'],
            ['name' => 'Labkom 3', 'capacity' => 55, 'status' => 'active'],
            ['name' => 'Labkom 4', 'capacity' => 36, 'status' => 'active'],
            ['name' => 'Labkom 5', 'capacity' => 13, 'status' => 'active'],
        ]);
    }
}
