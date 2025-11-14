<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for e-wallet operations
        $permissions = [
            // Admin-only permissions
            'wallet_management',
            'transaction_approval',
            'system_settings',

            // Member permissions
            'deposit_funds',
            'transfer_funds',
            'withdraw_funds',
            'view_transactions',
            'profile_update',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create admin role with all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create member role with limited permissions
        $memberRole = Role::create(['name' => 'member']);
        $memberRole->givePermissionTo([
            'deposit_funds',
            'transfer_funds',
            'withdraw_funds',
            'view_transactions',
            'profile_update'
        ]);

        // Create default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@ewallet.com'],
            [
                'username' => 'admin',
                'fullname' => 'System Administrator',
                'email' => 'admin@ewallet.com',
                'password' => Hash::make('Admin123!@#'),
                'email_verified_at' => now(),
                'suspended_at' => null,
                'phone' => '123-456-7890',
            ]
        );
        $admin->assignRole('admin');

        // Create default member user if not exists
        $member = User::firstOrCreate(
            ['email' => 'member@ewallet.com'],
            [
                'username' => 'member',
                'fullname' => 'Test Member',
                'email' => 'member@ewallet.com',
                'password' => Hash::make('Member123!@#'),
                'email_verified_at' => now(),
                'suspended_at' => null,
            ]
        );
        $member->assignRole('member');

        $this->command->info('Roles and permissions have been seeded successfully.');
        $this->command->info('Admin user: admin@ewallet.com / Admin123!@#');
        $this->command->info('Member user: member@ewallet.com / Member123!@#');
    }
}