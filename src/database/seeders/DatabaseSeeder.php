<?php

namespace Database\Seeders;

use App\Models\CarOption;
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

        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        collect([
            'MP3',
            'Bluetooth',
            'ABS',
            'Panoramic Roof',
            'Heated Seats',
            'Navigation',
        ])->each(fn ($name) => CarOption::firstOrCreate(['name' => $name]));
    }
}
