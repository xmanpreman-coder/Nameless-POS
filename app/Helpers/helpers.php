<?php

if (!function_exists('settings')) {
    function settings() {
        $settings = cache()->remember('settings', 24*60, function () {
            return \Modules\Setting\Entities\Setting::firstOrFail();
        });

        return $settings;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($value, $format = true) {
        if (!$format) {
            return $value;
        }

        $settings = settings();
        $position = $settings->default_currency_position;
        $symbol = $settings->currency->symbol;
        $decimal_separator = $settings->currency->decimal_separator;
        $thousand_separator = $settings->currency->thousand_separator;

        if ($position == 'prefix') {
            $formatted_value = $symbol . number_format((float) $value, 2, $decimal_separator, $thousand_separator);
        } else {
            $formatted_value = number_format((float) $value, 2, $decimal_separator, $thousand_separator) . $symbol;
        }

        return $formatted_value;
    }
}

if (!function_exists('format_currency_short')) {
    /**
     * Format currency with abbreviated suffix for large numbers
     * e.g., 3.500.000 -> Rp 3,5 Jt, 936.193.750 -> Rp 936 Jt
     */
    function format_currency_short($value) {
        $settings = settings();
        $symbol = $settings->currency->symbol;
        $absValue = abs((float) $value);
        
        if ($absValue >= 1000000000) {
            // Miliar (Billion)
            $formatted = number_format($absValue / 1000000000, 1, ',', '.');
            $suffix = 'M';
        } elseif ($absValue >= 1000000) {
            // Juta (Million)
            $formatted = number_format($absValue / 1000000, 1, ',', '.');
            $suffix = 'Jt';
        } elseif ($absValue >= 1000) {
            // Ribu (Thousand)
            $formatted = number_format($absValue / 1000, 1, ',', '.');
            $suffix = 'Rb';
        } else {
            $formatted = number_format($absValue, 0, ',', '.');
            $suffix = '';
        }
        
        // Remove trailing ,0 if exists
        $formatted = preg_replace('/,0$/', '', $formatted);
        
        $sign = $value < 0 ? '-' : '';
        return $sign . $symbol . $formatted . ($suffix ? ' ' . $suffix : '');
    }
}

if (!function_exists('make_reference_id')) {
    function make_reference_id($prefix, $number) {
        $padded_text = $prefix . '-' . str_pad($number, 5, 0, STR_PAD_LEFT);

        return $padded_text;
    }
}

if (!function_exists('array_merge_numeric_values')) {
    function array_merge_numeric_values() {
        $arrays = func_get_args();
        $merged = array();
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                if (!isset($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] += $value;
                }
            }
        }

        return $merged;
    }
}
