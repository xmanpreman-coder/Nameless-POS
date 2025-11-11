<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create test users
        $users = [
            [
                'name' => 'Manager User',
                'email' => 'manager@test.com',
                'password' => Hash::make('12345678'),
                'is_active' => 1
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@test.com',
                'password' => Hash::make('12345678'),
                'is_active' => 1
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign role if not already assigned
            if ($userData['email'] === 'manager@test.com') {
                $role = Role::firstOrCreate(['name' => 'Manager']);
                if (!$user->hasRole('Manager')) {
                    $user->assignRole($role);
                }
            } else {
                $role = Role::firstOrCreate(['name' => 'Staff']);
                if (!$user->hasRole('Staff')) {
                    $user->assignRole($role);
                }
            }
        }
    }
}

