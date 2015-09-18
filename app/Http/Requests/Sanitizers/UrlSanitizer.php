<?php

namespace App\Http\Requests\Sanitizers;

trait UrlSanitizer
{
    public function sanitizeUrl($input)
    {
        if (isset($input['web_sites'])) {
            $input['web_sites'] = array_map(function ($url) {
                $url = parse_url($url);
                if (!isset($url['scheme'])) {
                    return 'http://' . $url['path'];
                }

                return $url['scheme'] . '://' . $url['host'];
            }, $input['web_sites']);
        }

        return $input;
    }
}
