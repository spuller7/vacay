<?php

namespace app\core;

// Helper class to handle interaction with the Here API
class HereAPI
{
    static function get($request_url)
    {
        $request_url = $request_url.'&apiKey='.Application::$app->config['here_api_key'];
        return json_decode(file_get_contents($request_url), true);
    }
}