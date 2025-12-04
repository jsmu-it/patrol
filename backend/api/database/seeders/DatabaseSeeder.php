<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedShifts();
        $this->seedProjectAndUsers();
    }

    private function seedShifts(): void
    {
        $defaultShifts = [
            [
                'name' => 'Pagi',
                'code' => 'SHIFT_PAGI',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
            ],
            [
                'name' => 'Sore',
                'code' => 'SHIFT_SORE',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
            ],
            [
                'name' => 'Malam',
                'code' => 'SHIFT_MALAM',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
            ],
        ];

        foreach ($defaultShifts as $shiftData) {
            Shift::updateOrCreate(
                ['code' => $shiftData['code']],
                [
                    'name' => $shiftData['name'],
                    'start_time' => $shiftData['start_time'],
                    'end_time' => $shiftData['end_time'],
                    'tolerance_minutes' => 10,
                    'is_default' => true,
                ]
            );
        }
    }

    private function seedProjectAndUsers(): void
    {
        $project = Project::firstOrCreate(
            ['name' => 'Default Project'],
            [
                'client_name' => 'Default Client',
                'address' => 'Default Address',
                'latitude' => 0.0000000,
                'longitude' => 0.0000000,
                'geofence_radius_meters' => 500,
                'is_active' => true,
            ]
        );

        $shifts = Shift::all();
        if ($shifts->isNotEmpty()) {
            $syncData = [];
            foreach ($shifts as $shift) {
                $syncData[$shift->id] = ['is_active' => true];
            }
            $project->shifts()->syncWithoutDetaching($syncData);
        }

        User::updateOrCreate(
            ['username' => 'itjsmu'],
            [
                'name' => 'Super Administrator',
                'email' => 'itjsmu@example.com',
                'password' => Hash::make('*Jsmu@378'),
                'role' => User::ROLE_SUPERADMIN,
                'active_project_id' => $project->id,
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('*Jsmu@378'),
                'role' => User::ROLE_ADMIN,
                'active_project_id' => $project->id,
            ]
        );

        User::updateOrCreate(
            ['username' => 'guard1'],
            [
                'name' => 'Guard 1',
                'email' => 'guard1@example.com',
                'password' => Hash::make('*Jsmu@378'),
                'role' => User::ROLE_GUARD,
                'active_project_id' => $project->id,
            ]
        );
    }
}
