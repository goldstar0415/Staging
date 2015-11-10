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
    function link_delete($url, $title, $options = []) {
        $options['type'] = 'submit';

        $output = Form::open(['method' => 'DELETE', 'url' => $url]);
        $output .= Form::button($title, $options);
        $output .= Form::close();

        return $output;
    }
}