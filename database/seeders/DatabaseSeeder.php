<?php

namespace Database\Seeders;

use App\Models\ParkingArea;
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
        $areas = [
            ['code' => 'A', 'name' => 'Parking A'],
            ['code' => 'B', 'name' => 'Parking B'],
            ['code' => 'C', 'name' => 'Parking C'],
            ['code' => 'D', 'name' => 'Parking D'],
            ['code' => 'E', 'name' => 'Parking E'],
            ['code' => 'F', 'name' => 'Parking F'],
        ];

        foreach ($areas as $areaData) {
            $area = ParkingArea::firstOrCreate(
                ['code' => $areaData['code']],
                ['name' => $areaData['name'], 'total_slots' => 20]
            );

            if ($area->slots()->count() === 0) {
                for ($i = 1; $i <= $area->total_slots; $i++) {
                    $area->slots()->create([
                        'slot_number' => $i,
                        'status' => 'vacant',
                    ]);
                }
            }
        }

        // Create a default admin for testing
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'is_admin' => false,
            ]
        );
    }
}
