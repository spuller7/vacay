<?php

namespace app\core;

/**
 * Helper class to handle interaction with the Google API
 */
class GoogleAPI
{
    static function get($request_url)
    {
        $request_url = $request_url.'&key='.Application::$app->config['google_api_key'];
        return json_decode(file_get_contents($request_url), true);
    }

    static function getPhoto($reference)
    {
        $url = 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference='.$reference.'&key='.Application::$app->config['google_api_key'];
        return $url;
    }
}