<?php

if (! function_exists('formatQuantity')) {

    /**
     * Format quantities for display.
     *
     * Examples:
     * 50.000  -> 50
     * 20.500  -> 20.5
     * 12.250  -> 12.25
     * 0.125   -> 0.125
     */

    function formatQuantity($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return rtrim(
            rtrim(number_format((float) $value, 3, '.', ''), '0'),
            '.'
        );
    }
}