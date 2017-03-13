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

if (! function_exists('link_delete')) {
    function link_delete($url, $title, $options = [], $params = []) {

        $output = Form::open(array_merge($options, ['method' => 'DELETE', 'url' => $url]));
        $output .= Form::button($title, ['type' => 'submit']);
        foreach ($params as $param => $value) {
            $output .= Form::hidden($param, $value);
        }
        $output .= Form::close();

        return $output;
    }
}