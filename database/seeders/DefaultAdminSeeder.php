<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds for Electron app initialization.
     * Creates default admin user for first-time setup.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin role if doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Create default admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@nameless.pos'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin role to user
        if (!$adminUser->hasRole('Admin')) {
            $adminUser->assignRole($adminRole);
            $this->command->info('✅ Admin role assigned to user');
        }

        // Grant all permissions to admin role
        $permissions = \Spatie\Permission\Models\Permission::all();
        if ($permissions->count() > 0) {
            $adminRole->givePermissionTo($permissions);
            $this->command->info('✅ All permissions granted to Admin role');
        }

        $this->command->info('✅ Default admin user created');
        $this->command->info('   Email: admin@nameless.pos');
        $this->command->info('   Password: admin123');
    }
}
