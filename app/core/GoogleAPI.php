<?php

namespace app\core;

class GoogleAPI
{
    static private $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';

    static function get($request_url)
    {
        $request_url = $request_url.'&key='.self::$key;
        return json_decode(file_get_contents($request_url), true);
    }

    static function getPhoto($reference)
    {
        $url = 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference='.$reference.'&key='.self::$key;
        return $url;
    }
}