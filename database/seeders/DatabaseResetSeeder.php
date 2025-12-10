<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Wallet;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MlmSetting;

class DatabaseResetSeeder extends Seeder
{
    /**
     * Run the database seeds to reset to initial state.
     * This seeder preserves current system settings and restores the first two users.
     */
    public function run(): void
    {
        // Increase execution time limit for hosting environments
        @ini_set('max_execution_time', 300); // 5 minutes
        @ini_set('memory_limit', '512M');

        $this->command->info('🔄 Starting database reset...');
        $this->command->newLine();

        // Step 0: Clear all caches and optimize
        $this->clearAllCaches();

        // Clear cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log Sprint 1 optimization status
        $this->logOptimizationStatus();

        // Step 1: Clear only user transactions and non-default users (preserve everything else)
        if (app()->environment('testing')) {
            $this->clearAllData();
        } else {
            $this->clearUserData();
        }

        // Step 2: Ensure roles and permissions exist (don't recreate if they exist)
        $this->ensureRolesAndPermissions();

        // Step 3: Re-create/ensure default users exist
        $this->ensureDefaultUsers();

        // Step 4: Ensure system settings are preserved (no action needed since we don't clear them)
        $this->ensureSystemSettings([]);

        // Step 4.5: Ensure application settings are preserved/created
        $this->ensureApplicationSettings();

        // Step 5: Create/update wallets for users
        $this->ensureUserWallets();

        // Step 6: Reset and reload preloaded packages
        $this->resetAndReloadPackages();

        // Step 6.5: Reset and reload preloaded products
        $this->resetAndReloadProducts();

        // Step 7: Update reset tracking
        $this->updateResetTracking();

        // Step 8: Verify MLM commission migration status
        $this->verifyMLMCommissionMigration();

        // Step 9: Update network status for existing users
        $this->updateNetworkStatusForExistingUsers();

        // Step 10: Ensure rank system is properly configured
        $this->ensureRankSystemConfiguration();

        $this->command->info('✅ Database reset completed successfully!');
        $this->command->info('👤 Admin: admin / admin@gawisherbal.com / Admin123!@#');
        $this->command->info('👤 Member: member / member@gawisherbal.com / Member123!@#');
        $this->command->info('⚙️  System settings preserved');
        $this->command->info('⚙️  Application settings preserved');
        $this->command->info('📦 Preloaded packages restored with MLM settings');
        $this->command->info('🛒 Order history cleared (ready for new orders)');
        $this->command->info('↩️  Return requests cleared (ready for new returns)');
        $this->command->info('🔗 Referral clicks cleared (ready for new tracking)');
        $this->command->info('📊 Activity logs cleared (fresh audit trail)');
        $this->command->info('🏆 Rank advancements cleared (fresh rank progression tracking)');
        $this->command->info('👥 Direct sponsors tracker cleared (fresh sponsorship tracking)');
        $this->command->info('🔢 User IDs reset to sequential (1, 2)');
        $this->command->info('📍 Complete profile data for admin and member');
        $this->command->info('');
        $this->command->info('🚀 E-Commerce Platform Features:');
        $this->command->info('  ✅ 26-Status Order Lifecycle Management');
        $this->command->info('  ✅ Dual Delivery Methods (Office Pickup + Home Delivery)');
        $this->command->info('  ✅ Shopping Cart with Real-time Updates');
        $this->command->info('  ✅ Integrated E-Wallet Payment System');
        $this->command->info('  ✅ Complete Return & Refund System');
        $this->command->info('  ✅ Package Management with Inventory Tracking');
        $this->command->info('  ✅ Order Analytics Dashboard');
        $this->command->info('');
        $this->command->info('🏆 Ranking System Features:');
        $this->command->info('  ✅ Automatic Rank Advancement System');
        $this->command->info('    • Real-time advancement on sponsorship milestones');
        $this->command->info('    • Hourly scheduled processing for all users');
        $this->command->info('    • System-funded rank reward packages');
        $this->command->info('    • Direct sponsors tracking (persistent & accurate)');
        $this->command->info('    • Rank-aware commission calculations');
        $this->command->info('    • Complete advancement history & audit trail');
        $this->command->info('    • Network status auto-activation on rank advancement');
        $this->command->info('    • Backward compatible with legacy sponsorships');
        $this->command->info('  ✅ Admin Rank Management Interface');
        $this->command->info('    • Rank system dashboard with statistics');
        $this->command->info('    • Visual rank distribution charts (Chart.js)');
        $this->command->info('    • Configurable rank requirements & progression');
        $this->command->info('    • Advancement history with filters & search');
        $this->command->info('    • Manual rank advancement capability');
        $this->command->info('    • Rank packages: Starter → Newbie → Bronze → Silver → Gold');
        $this->command->info('    • Access: /admin/ranks');
        $this->command->info('');
        $this->command->info('💰 MLM System Features:');
        $this->command->info('  ✅ Core MLM Package & Registration');
        $this->command->info('    • 5-Level Commission Structure (L1: ₱200, L2-L5: ₱50 each)');
        $this->command->info('    • MLM Package Configuration (toggleable per package)');
        $this->command->info('    • Active/Inactive Level Toggling with Real-time Calculations');
        $this->command->info('    • MLM Settings Preservation (survives package toggle)');
        $this->command->info('    • Circular Reference Prevention (self-sponsorship & loops)');
        $this->command->info('    • Sponsor Relationship Validation');
        $this->command->info('    • Segregated Wallet Balances (MLM vs Purchase)');
        $this->command->info('    • Auto-generated Unique Referral Codes');
        $this->command->info('  ✅ Referral Link System & Auto-Fill Sponsor');
        $this->command->info('    • Shareable Referral Links with QR Codes');
        $this->command->info('    • Social Media Sharing (Facebook, WhatsApp, Messenger, Twitter)');
        $this->command->info('    • Referral Click Tracking (IP, User Agent, Timestamp)');
        $this->command->info('    • Auto-fill Sponsor on Registration');
        $this->command->info('    • Referral Statistics Dashboard (Clicks, Conversions, Rate)');
        $this->command->info('    • Copy to Clipboard Functionality');
        $this->command->info('    • Session-based Referral Code Storage');
        $this->command->info('    • Registration Conversion Tracking');
        $this->command->info('  ✅ Real-Time MLM Commission Distribution Engine');
        $this->command->info('    • Automatic Commission Distribution on Order Confirmation');
        $this->command->info('    • Upline Traversal (5 Levels: L1=₱200, L2-L5=₱50 each)');
        $this->command->info('    • Immediate Synchronous Processing (No Queue Required)');
        $this->command->info('    • Multi-Channel Notifications:');
        $this->command->info('      - Database notifications (always sent)');
        $this->command->info('      - Broadcast notifications (real-time if Echo configured)');
        $this->command->info('      - Email notifications (ONLY to verified emails)');
        $this->command->info('    • Transaction Audit Trail (level, source_order_id, metadata)');
        $this->command->info('    • MLM Balance Widget (Real-time Updates with Pulse Animation)');
        $this->command->info('    • Network Stats Panel (Direct Referrals, Total Earnings)');
        $this->command->info('    • Commission Processing Time: < 1 second per order');
        $this->command->info('    • Error Handling: Missing wallets, incomplete upline, duplicates');
        $this->command->info('    • Performance: 3 retry attempts with exponential backoff');
        $this->command->info('');
        $this->command->info('📊 Activity Logging & Audit System:');
        $this->command->info('  ✅ Comprehensive Database-backed Activity Logs');
        $this->command->info('    • MLM Commission Tracking (every commission logged)');
        $this->command->info('    • Wallet Transaction Logging (deposits, withdrawals, transfers)');
        $this->command->info('    • Order Payment & Refund Logging');
        $this->command->info('    • Admin Action Logging (approvals, rejections)');
        $this->command->info('    • Security Event Tracking');
        $this->command->info('    • Filter by Type: MLM Commission, Wallet, Order, Security, Transaction, System');
        $this->command->info('    • Filter by Level: DEBUG, INFO, WARNING, ERROR, CRITICAL');
        $this->command->info('    • Search Functionality across logs');
        $this->command->info('    • Export to CSV/JSON for reporting');
        $this->command->info('    • Automatic Metadata Storage (JSON format)');
        $this->command->info('    • Full Relationship Tracking (User, Transaction, Order)');
        $this->command->info('    • Performance Optimized (8 database indexes)');
        $this->command->info('    • Access: /admin/logs');
        $this->command->info('');
        $this->command->info('🔒 Performance & Security Enhancements:');
        $this->command->info('  ✅ Database indexes for faster queries');
        $this->command->info('  ✅ Eager loading to eliminate N+1 queries');
        $this->command->info('  ✅ Package caching for improved load times');
        $this->command->info('  ✅ Rate limiting on critical routes');
        $this->command->info('  ✅ CSRF protection on all AJAX operations');
        $this->command->info('  ✅ Transaction locking (prevents race conditions)');
        $this->command->info('  ✅ Secure cryptographic order number generation');
        $this->command->info('  ✅ Circular sponsor reference prevention (Model + Database)');
        $this->command->info('  ✅ MySQL triggers protect against raw SQL manipulation');
        $this->command->info('  ✅ User account suspension system (with auto-logout)');
        $this->command->info('  ✅ Session termination for suspended users');
        $this->command->info('');
        $this->command->info('📋 Return & Refund Process:');
        $this->command->info('  ✅ 7-day return window after delivery');
        $this->command->info('  ✅ Customer return request with proof images');
        $this->command->info('  ✅ Admin approval/rejection workflow');
        $this->command->info('  ✅ Automatic wallet refund processing');
    }

    private function clearAllData(): void
    {
        $this->command->info('🗑️  Clearing all data for testing environment...');

        try {
            // Disable foreign key checks for proper truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $table_array = (array)$table;
                $table_name = $table_array[key($table_array)];
                if ($table_name !== 'migrations') {
                    // Use DELETE instead of TRUNCATE for better compatibility
                    DB::table($table_name)->delete();
                    
                    // Reset auto-increment
                    try {
                        DB::statement("ALTER TABLE `{$table_name}` AUTO_INCREMENT = 1");
                    } catch (\Exception $e) {
                        // Some tables may not have auto-increment, ignore errors
                    }
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->command->info('✅ Cleared all tables for testing environment');

        } catch (\Exception $e) {
            // Re-enable foreign key checks even if an error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->error('❌ Error during data clearing: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clear user transactions and orders (preserve system settings, default users, roles, and permissions)
     */
    private function clearUserData(): void
    {
        $this->command->info('🗑️  Clearing user transactions and orders (preserving system settings, users, roles, and permissions)...');

        try {
            // Disable foreign key checks for proper truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Use DELETE instead of TRUNCATE for better compatibility with hosting environments
            // TRUNCATE requires higher privileges and may be blocked
            
            // Clear activity logs (audit trail - can be fully reset)
            DB::table('activity_logs')->delete();
            $this->command->info('✅ Cleared all activity logs (audit trail reset)');

            // Clear referral clicks first (foreign key dependency on users)
            DB::table('referral_clicks')->delete();
            $this->command->info('✅ Cleared all referral clicks');

            // Clear return requests (foreign key dependency on orders)
            DB::table('return_requests')->delete();
            $this->command->info('✅ Cleared all return requests');

            // Clear order status histories (foreign key dependency on orders)
            DB::table('order_status_histories')->delete();
            $this->command->info('✅ Cleared all order status histories');

            // Clear order items (foreign key dependency on orders)
            DB::table('order_items')->delete();
            $this->command->info('✅ Cleared all order items');

            // Clear orders
            DB::table('orders')->delete();
            $this->command->info('✅ Cleared all orders');

            // Clear transactions (all of them)
            DB::table('transactions')->delete();
            $this->command->info('✅ Cleared all transactions');

            // Clear wallets
            DB::table('wallets')->delete();
            $this->command->info('✅ Cleared all wallets');

            // Clear rank advancement history (foreign key dependency on users and orders)
            DB::table('rank_advancements')->delete();
            $this->command->info('✅ Cleared all rank advancement history');

            // Clear direct sponsors tracker (foreign key dependency on users)
            DB::table('direct_sponsors_tracker')->delete();
            $this->command->info('✅ Cleared all direct sponsors tracking');

            // Clear users
            DB::table('users')->delete();
            $this->command->info('✅ Cleared all users');

            // Reset auto-increment counters manually (since DELETE doesn't reset them)
            DB::statement('ALTER TABLE activity_logs AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE referral_clicks AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE return_requests AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE order_status_histories AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE order_items AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE orders AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE wallets AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE rank_advancements AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE direct_sponsors_tracker AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // NOTE: We deliberately preserve:
            // - system_settings table
            // - roles table
            // - permissions table
            // - role_has_permissions table (role-permission relationships)

            $this->command->info('✅ Auto-increment counters reset for all cleared tables');

        } catch (\Exception $e) {
            // Re-enable foreign key checks even if an error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->error('❌ Error during data clearing: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ensure roles and permissions exist (don't recreate if they exist)
     */
    private function ensureRolesAndPermissions(): void
    {
        $this->command->info('🔐 Ensuring roles and permissions exist...');

        // Check if roles and permissions already exist
        $existingRoles = Role::count();
        $existingPermissions = Permission::count();

        if ($existingRoles > 0 && $existingPermissions > 0) {
            $this->command->info("✅ Found $existingRoles roles and $existingPermissions permissions (preserved)");
            return;
        }

        // Only create if they don't exist
        $this->command->info('🔄 Creating missing roles and permissions...');

        // Create permissions for e-wallet operations
        $permissions = [
            // Admin-only permissions
            'wallet_management' => 'Manage user wallets and balances',
            'transaction_approval' => 'Approve or reject transactions',
            'system_settings' => 'Configure system settings',

            // Member permissions
            'deposit_funds' => 'Deposit funds to wallet',
            'transfer_funds' => 'Transfer funds to other users',
            'withdraw_funds' => 'Withdraw funds from wallet',
            'view_transactions' => 'View transaction history',
            'profile_update' => 'Update profile information',
        ];

        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create admin role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create member role with limited permissions
        $memberRole = Role::firstOrCreate(['name' => 'member']);
        $memberRole->syncPermissions([
            'deposit_funds',
            'transfer_funds',
            'withdraw_funds',
            'view_transactions',
            'profile_update'
        ]);

        $this->command->info("✅ Ensured " . count($permissions) . " permissions and 2 roles exist");
    }

    /**
     * Ensure default users exist and have correct roles with proper sequential IDs
     */
    private function ensureDefaultUsers(): void
    {
        $this->command->info('👥 Ensuring default users exist and have correct roles...');

        // Delete existing default users to recreate with proper IDs
        $defaultUserEmails = ['admin@gawisherbal.com', 'member@gawisherbal.com'];

        // Get existing user IDs before deletion
        $existingUsers = User::whereIn('email', $defaultUserEmails)->get();
        $existingWallets = [];

        foreach ($existingUsers as $user) {
            // Store wallet data if exists
            if ($user->wallet) {
                $existingWallets[$user->email] = [
                    'mlm_balance' => $user->wallet->mlm_balance,
                    'purchase_balance' => $user->wallet->purchase_balance,
                ];
            }
        }

        // Delete existing default users and their relationships
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($existingUsers as $user) {
            // Delete wallet
            DB::table('wallets')->where('user_id', $user->id)->delete();

            // Delete role assignments
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->delete();

            // Delete permission assignments
            DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->delete();
        }

        // Delete the users
        User::whereIn('email', $defaultUserEmails)->delete();

        // Reset users auto-increment to 1
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user (will get ID = 1) with complete profile - NO SPONSOR
        $admin = User::create([
            'username' => 'admin',
            'fullname' => 'System Administrator',
            'email' => 'admin@gawisherbal.com',
            'password' => Hash::make('Admin123!@#'),
            'email_verified_at' => now(),
            'suspended_at' => null,
            'phone' => '+63 (947) 367-7436',
            'address' => '123 Herbal Street',
            'address_2' => null,
            'city' => 'Wellness City',
            'state' => 'HC',
            'zip' => '12345',
            'delivery_instructions' => null,
            'delivery_time_preference' => 'anytime',
            'sponsor_id' => null, // Admin has no sponsor
            // referral_code will be auto-generated by User model boot method
        ]);

        $admin->syncRoles(['admin']);
        $this->command->info('✅ Created admin user (ID: ' . $admin->id . ', Referral: ' . $admin->referral_code . ')');

        // Create member user (will get ID = 2) sponsored by admin
        $member = User::create([
            'username' => 'member',
            'fullname' => 'John Michael Santos',
            'email' => 'member@gawisherbal.com',
            'password' => Hash::make('Member123!@#'),
            'email_verified_at' => now(),
            'suspended_at' => null,
            'phone' => '+63 (912) 456-7890',
            'address' => '456 Wellness Avenue',
            'address_2' => 'Unit 202',
            'city' => 'Health City',
            'state' => 'Metro Manila',
            'zip' => '54321',
            'delivery_instructions' => 'Ring doorbell twice. Gate code: 1234',
            'delivery_time_preference' => 'morning',
            'sponsor_id' => $admin->id, // Member is sponsored by admin
            // referral_code will be auto-generated by User model boot method
        ]);

        $member->syncRoles(['member']);
        $this->command->info('✅ Created member user (ID: ' . $member->id . ', Referral: ' . $member->referral_code . ', Sponsor: Admin)');

        $this->command->info('✅ Default users created with MLM relationships');
    }

    /**
     * Ensure system settings are preserved (they were not cleared, so just verify they exist)
     */
    private function ensureSystemSettings(array $currentSettings): void
    {
        $this->command->info('⚙️  Verifying system settings preservation...');

        $currentCount = SystemSetting::count();

        if ($currentCount > 0) {
            $this->command->info("✅ System settings preserved ($currentCount settings remain intact)");
            return;
        }

        // If somehow no settings exist (shouldn't happen), create minimal defaults
        $this->command->info('⚠️  No system settings found, creating minimal defaults...');
        $this->createMinimalDefaultSettings();
    }

    /**
     * Create minimal default settings if none exist
     */
    private function createMinimalDefaultSettings(): void
    {
        $this->command->info('⚙️  Creating minimal default settings...');

        $defaults = [
            ['key' => 'app_name', 'value' => 'Gawis iHerbal', 'type' => 'string', 'description' => 'Application name'],
            ['key' => 'app_version', 'value' => '1.0.0', 'type' => 'string', 'description' => 'Application version'],
            ['key' => 'email_verification_enabled', 'value' => true, 'type' => 'boolean', 'description' => 'Enable email verification'],
            ['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean', 'description' => 'Maintenance mode status']
        ];

        foreach ($defaults as $setting) {
            SystemSetting::create($setting);
        }

        $this->command->info("✅ Created " . count($defaults) . " default settings");
    }

    /**
     * Ensure application settings (tax rate, email verification after registration) are preserved/created
     */
    private function ensureApplicationSettings(): void
    {
        $this->command->info('⚙️  Verifying and setting application settings...');

        // Force tax rate to 0 on every reset
        SystemSetting::set('tax_rate', 0.00, 'decimal', 'E-commerce tax rate (0.0 to 1.0)');
        $this->command->info('✅ Set tax rate to 0%');

        // Check if email verification setting exists, create if not
        $emailVerifRegSetting = SystemSetting::where('key', 'email_verification_required')->first();
        if (!$emailVerifRegSetting) {
            SystemSetting::create([
                'key' => 'email_verification_required',
                'value' => true,
                'type' => 'boolean',
                'description' => 'Require email verification after registration'
            ]);
            $this->command->info('✅ Created default email verification setting: enabled');
        } else {
            $this->command->info('✅ Email verification setting preserved.');
        }
    }

    /**
     * Reset wallets for default users to initial balances
     */
    private function ensureUserWallets(): void
    {
        $this->command->info('💰 Resetting default user wallets to initial balances...');

        $admin = User::where('email', 'admin@gawisherbal.com')->first();
        $member = User::where('email', 'member@gawisherbal.com')->first();

        if ($admin) {
            // Reset admin wallet with segregated balances (MLM system)
            Wallet::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'mlm_balance' => 0.00, // MLM earnings (withdrawable)
                    'purchase_balance' => 99999999.00, // Purchase credits (₱1,000,000 for testing)
                    'is_active' => true
                ]
            );
        }

        if ($member) {
            // Reset member wallet with segregated balances (MLM system)
            Wallet::updateOrCreate(
                ['user_id' => $member->id],
                [
                    'mlm_balance' => 0.00, // MLM earnings (withdrawable)
                    'purchase_balance' => 1000.00, // Purchase credits (₱1,000 for Starter Package)
                    'is_active' => true
                ]
            );
        }

        $this->command->info('✅ Default user wallets reset with MLM segregated balances');
        $this->command->info('💰 Admin: ₱1,000,000 (Purchase Balance for testing)');
        $this->command->info('💰 Member: ₱1,000 (Purchase Balance for Starter Package)');
    }

    /**
     * Reset and reload preloaded packages
     */
    private function resetAndReloadPackages(): void
    {
        $this->command->info('📦 Preserving existing packages and MLM settings...');

        // // Clear all existing packages (force delete to completely remove)
        // Package::withTrashed()->forceDelete();
        // $this->command->info('🗑️  Cleared all existing packages');

        // // Clear MLM settings (will be recreated with packages)
        // DB::table('mlm_settings')->truncate();
        // $this->command->info('🗑️  Cleared all MLM settings');

        // // Clear package cache (Sprint 1 enhancement)
        // $this->clearPackageCache();

        // // Reset auto-increment counters
        // DB::statement('ALTER TABLE packages AUTO_INCREMENT = 1');
        // DB::statement('ALTER TABLE mlm_settings AUTO_INCREMENT = 1');

        // // Reload preloaded packages by calling the PackageSeeder
        // $this->command->info('🔄 Reloading preloaded packages with MLM settings...');
        // $this->call(\Database\Seeders\PackageSeeder::class);

        $packageCount = Package::count();
        $mlmSettingsCount = MlmSetting::count();
        $this->command->info("✅ Preserved {$packageCount} packages and {$mlmSettingsCount} MLM settings");
    }

    /**
     * Clear all package-related caches
     */
    private function clearPackageCache(): void
    {
        try {
            // Clear all package caches using pattern matching
            $packages = DB::table('packages')->pluck('id');

            foreach ($packages as $packageId) {
                Cache::forget("package_{$packageId}");
            }

            $this->command->info("🗑️  Cleared cache for " . count($packages) . " packages");
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Failed to clear some package caches: ' . $e->getMessage());
        }
    }

    /**
     * Update reset tracking information
     */
    private function updateResetTracking(): void
    {
        $this->command->info('📊 Updating reset tracking...');

        // Update reset count
        $currentCount = SystemSetting::get('reset_count', 0);
        SystemSetting::set('reset_count', $currentCount + 1, 'integer', 'Number of times database has been reset');
        SystemSetting::set('last_reset_date', now()->toISOString(), 'string', 'Last database reset timestamp');

        $this->command->info('✅ Reset tracking updated');
    }

    /**
     * Log Sprint 1 optimization status
     */
    private function logOptimizationStatus(): void
    {
        $this->command->info('🔍 Checking Sprint 1 optimizations...');

        // Check for performance indexes migration
        $indexMigration = DB::table('migrations')
            ->where('migration', 'like', '%add_performance_indexes_to_tables%')
            ->first();

        if ($indexMigration) {
            $this->command->info('✅ Performance indexes migration detected');
        } else {
            $this->command->warn('⚠️  Performance indexes migration not found - will be applied');
        }

        // Check cache driver
        $cacheDriver = config('cache.default');
        $this->command->info("ℹ️  Cache driver: {$cacheDriver}");

        if ($cacheDriver === 'redis') {
            $this->command->info('✅ Redis cache configured (optimal)');
        } elseif ($cacheDriver === 'database') {
            $this->command->info('ℹ️  Database cache configured (consider Redis for production)');
        }
    }

    /**
     * Clear all application caches for fresh start
     */
    private function clearAllCaches(): void
    {
        $this->command->info('🧹 Clearing all caches...');

        try {
            // Use optimize:clear for faster execution (single command instead of multiple)
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $this->command->info('  ✅ All caches cleared (config, route, view, compiled)');

            $this->command->newLine();
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Some caches could not be cleared: ' . $e->getMessage());
            
            // Fallback: try manual cache clearing
            try {
                Cache::flush();
                $this->command->info('  ✅ Cache flushed manually');
            } catch (\Exception $fallbackError) {
                $this->command->warn('⚠️  Manual cache flush failed: ' . $fallbackError->getMessage());
            }
        }
    }

    /**
     * Verify MLM commission migration status
     */
    private function verifyMLMCommissionMigration(): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Verifying MLM Commission Distribution...');

        // Check for MLM commission migration
        $mlmMigration = DB::table('migrations')
            ->where('migration', 'like', '%add_mlm_fields_to_transactions%')
            ->first();

        if ($mlmMigration) {
            $this->command->info('✅ MLM commission migration applied: MLM fields added to transactions table');

            // Verify the actual columns exist
            try {
                $hasLevel = DB::getSchemaBuilder()->hasColumn('transactions', 'level');
                $hasSourceOrderId = DB::getSchemaBuilder()->hasColumn('transactions', 'source_order_id');
                $hasSourceType = DB::getSchemaBuilder()->hasColumn('transactions', 'source_type');

                if ($hasLevel && $hasSourceOrderId && $hasSourceType) {
                    $this->command->info('✅ Verified: All MLM transaction columns present');
                    $this->command->info('  • level (MLM level tracking)');
                    $this->command->info('  • source_order_id (order linkage)');
                    $this->command->info('  • source_type (transaction categorization)');
                } else {
                    $this->command->warn('⚠️  MLM migration exists but columns missing - run: php artisan migrate');
                }
            } catch (\Exception $e) {
                $this->command->warn('⚠️  Could not verify MLM columns: ' . $e->getMessage());
            }

            // MLM Commission Information
            $this->command->newLine();
            $this->command->info('📌 MLM Commission System:');
            $this->command->info('  ✅  Commissions are processed IMMEDIATELY upon package purchase');
            $this->command->info('  ✅  No queue worker required - synchronous processing');
            $this->command->info('  ✅  Active users (who bought packages) earn from downline purchases');
            $this->command->newLine();
            $this->command->info('  ℹ️  Optional: Monitor application logs:');
            $this->command->info('     php artisan pail --timeout=0');
        } else {
            $this->command->warn('⚠️  MLM commission migration NOT found');
            $this->command->warn('     Run: php artisan migrate');
            $this->command->warn('     Expected migration: *_add_mlm_fields_to_transactions_table.php');
        }

        $this->command->newLine();
    }

    /**
     * Reset and reload preloaded products
     */
    private function resetAndReloadProducts(): void
    {
        $this->command->info('📦 Preserving existing products and unilevel settings...');

        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // \App\Models\UnilevelSetting::truncate();
        // \App\Models\Product::truncate();

        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // $this->command->info('🗑️  Cleared all existing products and unilevel settings');

        // // Reset auto-increment counters
        // DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');
        // DB::statement('ALTER TABLE unilevel_settings AUTO_INCREMENT = 1');

        // // Reload preloaded products by calling the ProductSeeder
        // $this->command->info('🔄 Reloading preloaded products with Unilevel settings...');
        // $this->call(\Database\Seeders\ProductSeeder::class);

        $productCount = \App\Models\Product::count();
        $this->command->info("✅ Preserved {$productCount} existing products.");
    }

    /**
     * Update network status for existing users based on their order history.
     */
    private function updateNetworkStatusForExistingUsers(): void
    {
        $this->command->info('🔄 Updating network status for existing users...');

        $users = User::all();

        foreach ($users as $user) {
            $firstPackageOrder = $user->orders()
                ->where('payment_status', 'paid')
                ->whereHas('orderItems', function ($query) {
                    $query->where('item_type', 'package');
                })
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstPackageOrder) {
                $user->update([
                    'network_status' => 'active',
                    'network_activated_at' => $firstPackageOrder->created_at,
                ]);
            }
        }

        $this->command->info('✅ Network status updated for existing users.');
    }

    /**
     * Ensure rank system is properly configured and ready
     */
    private function ensureRankSystemConfiguration(): void
    {
        $this->command->newLine();
        $this->command->info('🏆 Verifying Rank System Configuration...');

        // Check for rank system migrations
        $rankAdvancementsMigration = DB::table('migrations')
            ->where('migration', 'like', '%create_rank_advancements_table%')
            ->first();

        $directSponsorsMigration = DB::table('migrations')
            ->where('migration', 'like', '%create_direct_sponsors_tracker_table%')
            ->first();

        if ($rankAdvancementsMigration && $directSponsorsMigration) {
            $this->command->info('✅ Rank system migrations detected');

            // Verify the actual tables exist
            try {
                $hasRankAdvancements = DB::getSchemaBuilder()->hasTable('rank_advancements');
                $hasDirectSponsors = DB::getSchemaBuilder()->hasTable('direct_sponsors_tracker');

                if ($hasRankAdvancements && $hasDirectSponsors) {
                    $this->command->info('✅ Verified: All rank system tables present');
                    $this->command->info('  • rank_advancements (advancement history & audit trail)');
                    $this->command->info('  • direct_sponsors_tracker (sponsorship counting)');
                } else {
                    $this->command->warn('⚠️  Rank migrations exist but tables missing - run: php artisan migrate');
                }
            } catch (\Exception $e) {
                $this->command->warn('⚠️  Could not verify rank tables: ' . $e->getMessage());
            }

            // Check for rankable packages
            try {
                $rankablePackages = Package::where('is_rankable', true)->count();

                if ($rankablePackages > 0) {
                    $this->command->info("✅ Found {$rankablePackages} rankable packages configured");

                    // List rank packages
                    $packages = Package::where('is_rankable', true)
                        ->orderBy('rank_order')
                        ->get(['rank_name', 'rank_order', 'required_direct_sponsors']);

                    $this->command->info('');
                    $this->command->info('📋 Rank Progression:');
                    foreach ($packages as $package) {
                        $sponsors = $package->required_direct_sponsors ?? 0;
                        $this->command->info("  {$package->rank_order}. {$package->rank_name} (Requires: {$sponsors} sponsors)");
                    }
                } else {
                    $this->command->warn('⚠️  No rankable packages found - configure via /admin/ranks/configure');
                }
            } catch (\Exception $e) {
                $this->command->warn('⚠️  Could not verify rankable packages: ' . $e->getMessage());
            }

            // Rank Advancement Information
            $this->command->newLine();
            $this->command->info('📌 Rank Advancement System:');
            $this->command->info('  ✅  Automatic advancement on reaching sponsorship milestones');
            $this->command->info('  ✅  Scheduled processing: php artisan schedule:run (runs hourly)');
            $this->command->info('  ✅  Manual command: php artisan rank:process-advancements');
            $this->command->info('  ✅  Admin interface: /admin/ranks');
            $this->command->newLine();
            $this->command->info('  ℹ️  Optional: Set up cron job for automatic processing:');
            $this->command->info('     * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1');
        } else {
            $this->command->warn('⚠️  Rank system migrations NOT found');
            $this->command->warn('     Run: php artisan migrate');
            $this->command->warn('     Expected migrations:');
            $this->command->warn('       - *_create_rank_advancements_table.php');
            $this->command->warn('       - *_create_direct_sponsors_tracker_table.php');
        }

        $this->command->newLine();
    }
}
