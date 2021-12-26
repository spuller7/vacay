<?php

namespace app\core;

class HereAPI
{
    static private $key = 'S1W-ieZyern54fLk4CPDCu87ugxJ0rE4YAVmryZHwgQ';

    static function get($request_url)
    {
        $request_url = $request_url.'&apiKey='.self::$key;
        return json_decode(file_get_contents($request_url), true);
    }
}