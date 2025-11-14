<?php

if (!function_exists('currency')) {
    /**
     * Format amount with system currency symbol
     *
     * @param float $amount
     * @param bool $showSymbol
     * @return string
     */
    function currency($amount, $showSymbol = true)
    {
        $symbol = \App\Models\SystemSetting::get('currency_symbol', '₱');
        $formatted = number_format($amount, 2);

        return $showSymbol ? $symbol . $formatted : $formatted;
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get the system currency symbol
     *
     * @return string
     */
    function currency_symbol()
    {
        return \App\Models\SystemSetting::get('currency_symbol', '₱');
    }
}

if (!function_exists('currency_code')) {
    /**
     * Get the system currency code
     *
     * @return string
     */
    function currency_code()
    {
        return \App\Models\SystemSetting::get('currency', 'PHP');
    }
}
