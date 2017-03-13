<?php

namespace App\Services;

use BadMethodCallException;

/**
 * Class SocialSharing
 * @package App\Services
 *
 * @method static string facebook(string $url)
 * @method static string twitter(string $url)
 * @method static string google(string $url)
 */
class SocialSharing
{
    public static $share_links = [
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=%s',
        'twitter' => 'https://twitter.com/intent/tweet?url=%s',
        'google' => 'https://plus.google.com/share?url=%s'
    ];

    /**
     * is triggered when invoking inaccessible methods in a static context.
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    public static function __callStatic($name, $arguments)
    {
        $share_url = $arguments[0];

        if (isset(self::$share_links[$name])) {
            return sprintf(self::$share_links[$name], $share_url);
        } else {
            throw new BadMethodCallException;
        }
    }
}
