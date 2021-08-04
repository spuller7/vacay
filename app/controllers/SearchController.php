<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\AjaxResponse;
use app\core\Request;
use app\core\Response;
use app\models\LoginForm;
use GuzzleHttp\Client;

class SearchController extends Controller {
    
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
            'title' => 'Search'
        ];

        return $this->render('search', $data);
    }

    public function discover(Request $request, Response $response)
    {
        
        $ajax = new AjaxResponse();
        $key = 'S1W-ieZyern54fLk4CPDCu87ugxJ0rE4YAVmryZHwgQ';
        $ajax->response  = json_decode(file_get_contents('https://discover.search.hereapi.com/v1/discover?in=circle:42.2808,-83.7430;r=30000&q=restaurants&limit=100&apiKey='.$key), true);
        $ajax->success = true;

        $ajax->send();
    }

    public function search_suggestions(Request $request, Response $response)
    {
        
        $ajax = new AjaxResponse();
        error_log(print_r($_GET, true));
        $query = json_encode($_GET['query']);
        $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
        $request_url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input='.$query.'&inputtype=textquery&fields=formatted_address,name&locationbias=circle:2000@42.2808,-83.7430&key='.$key;
        $ajax->query = $_GET['query'];
        $ajax->response  = json_decode(file_get_contents($request_url), true);
        $ajax->success = true;

        $ajax->send();
    }
}
