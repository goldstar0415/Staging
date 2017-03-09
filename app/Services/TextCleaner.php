<?php

namespace App\Services;

/**
 * Class TextCleaner
 * @package App\Services
 */
class TextCleaner
{
    /**
     * Remove timestamps from given text
     * @param string $str
     * @return string
     */
    final public static function removeTimestamps(string $str): string
    {
        if ( !is_string($str) ) {
            return $str;
        }

        static $patterns = [
            '/\d{4}-\d{2}-\d{2}[\sT]+\d{2}:\d{2}:\d{2}/i',
            '/\d{4}-\d{2}-\d{2}[\sT]+\d{2}:\d{2}:\d{2}\s*[ap]+m/i',
        ];

        foreach ($patterns as $rgx) {
            $str = preg_replace($rgx, '', $str);
        }

        return trim($str);
    }
}
