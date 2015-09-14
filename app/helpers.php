<?php

if (! function_exists('frontend_url')) {
    /**
     * Generates link to frontend.
     * @param $uri
     * @param $params
     * @return string
     */
    function frontend_url($uri, ...$params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = rawurlencode($value);
        }

        return env('FRONTEND_URL') . '/' . $uri . '/' . implode('/', $params);
    }
}
