<?php

namespace App\Http\Requests\Sanitizers;

trait UrlSanitizer
{
    public function sanitizeUrl($input)
    {
        if (isset($input['web_sites'])) {
            $input['web_sites'] = array_map(function ($url) {
                $parts = parse_url($url);
                if (!isset($parts['scheme'])) {
                    return 'http://' . $url;
                }

                return $url;
            }, $input['web_sites']);
        }

        return $input;
    }
}
