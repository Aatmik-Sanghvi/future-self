<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function write($channel, $level, $message, $context = [])
    {
        $context = array_map(function ($value) {

            // Convert JSON strings → arrays (removes \" escaping)
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            // Convert objects → arrays
            if (is_object($value)) {
                return json_decode(json_encode($value), true);
            }

            return $value;
        }, $context);

        Log::channel($channel)->{$level}($message, $context);
    }
}
