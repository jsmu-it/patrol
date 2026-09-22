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
        $this->seedProjectAndUsers();
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

        // Shift dimiliki project. Setiap project baru mendapat tiga shift dasar
        // miliknya sendiri, terpisah dari project lain.
        $shiftDasar = [
            ['name' => 'Pagi',  'code' => 'SHIFT_PAGI',  'start_time' => '07:00:00', 'end_time' => '15:00:00'],
            ['name' => 'Sore',  'code' => 'SHIFT_SORE',  'start_time' => '15:00:00', 'end_time' => '23:00:00'],
            ['name' => 'Malam', 'code' => 'SHIFT_MALAM', 'start_time' => '23:00:00', 'end_time' => '07:00:00'],
        ];

        foreach ($shiftDasar as $data) {
            Shift::updateOrCreate(
                ['project_id' => $project->id, 'code' => $data['code']],
                $data + ['tolerance_minutes' => 10, 'is_default' => true]
            );
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
