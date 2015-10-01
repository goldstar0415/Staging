<?php

if (! function_exists('frontend_url')) {
    /**
     * Generates link to frontend.
     * @param string $uri
     * @param array $params
     * @return string
     */
    function frontend_url($uri = '', ...$params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = rawurlencode((string)$value);
        }
        return implode('/', array_merge([
            env('FRONTEND_URL'),
            $uri
        ], $params));
    }
}
