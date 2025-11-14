<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SessionTimeoutSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set default session timeout duration to 15 minutes
        SystemSetting::updateOrCreate(
            ['key' => 'session_timeout_minutes'],
            [
                'value' => '15',
                'type' => 'integer',
                'description' => 'Session timeout duration in minutes'
            ]
        );

        // Ensure session timeout is enabled by default
        SystemSetting::updateOrCreate(
            ['key' => 'session_timeout'],
            [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable automatic session timeout'
            ]
        );
    }
}
