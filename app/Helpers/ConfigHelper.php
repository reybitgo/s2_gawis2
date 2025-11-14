<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class ConfigHelper
{
    /**
     * Get config value with database override
     *
     * @param string $key - config key (e.g., 'app.name')
     * @param mixed $default - default value if neither config nor setting exists
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Convert config key to setting key (e.g., 'app.name' -> 'app_name')
        $settingKey = self::configKeyToSettingKey($key);

        // Try to get from database first
        $dbValue = SystemSetting::get($settingKey);

        if ($dbValue !== null) {
            return $dbValue;
        }

        // Fall back to config file
        return config($key, $default);
    }

    /**
     * Convert config key format to setting key format
     *
     * @param string $configKey
     * @return string
     */
    private static function configKeyToSettingKey(string $configKey): string
    {
        return str_replace('.', '_', $configKey);
    }

    /**
     * Get all app-related settings with database overrides
     *
     * @return array
     */
    public static function getAppSettings(): array
    {
        return [
            'name' => self::get('app.name'),
            'env' => self::get('app.env'),
            'debug' => self::get('app.debug'),
            'url' => self::get('app.url'),
            'timezone' => self::get('app.timezone'),
            'locale' => self::get('app.locale'),
            'fallback_locale' => self::get('app.fallback_locale'),
        ];
    }
}