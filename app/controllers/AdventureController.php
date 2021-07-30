<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\AjaxResponse;
use app\core\Request;
use app\core\Response;
use app\models\LoginForm;
use GuzzleHttp\Client;

class AdventureController extends Controller {
    
    public function __construct()
    {
    }

    public function validate()
    {
        $errors = [];

        //if (!Get::read)
    }

    public function index()
    {
        $data = [
            'title' => 'Adventure'
        ];

        return $this->render('adventure', $data);
    }

    public function discover(Request $request, Response $response)
    {
        
        $ajax = new AjaxResponse();

        $client = new Client();
        $key = 'S1W-ieZyern54fLk4CPDCu87ugxJ0rE4YAVmryZHwgQ';
        $ajax->response  = json_decode(file_get_contents('https://discover.search.hereapi.com/v1/discover?in=circle:42.2808,-83.7430;r=30000&q=restaurants&apiKey='.$key), true);
        $ajax->success = true;

        $ajax->send();
    }

    function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);
        error_log($result);
        return $result;
    }
}