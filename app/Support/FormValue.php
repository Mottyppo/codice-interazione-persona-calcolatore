<?php

namespace App\Support;

class FormValue
{
    /** Return only values that a scalar form control can display safely. */
    public static function old(string $key, mixed $default = null): string
    {
        $value = old($key, $default);

        return is_scalar($value) ? (string) $value : '';
    }
}
