<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\AjaxResponse;
use app\core\Request;
use app\core\Response;
use app\core\GoogleAPI;
use app\core\HereAPI;

use app\models\Adventure;
use app\models\Address;

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

        $ajax->specialPlace = true;
        $fields = '&fields=name,rating,formatted_phone_number,opening_hours,formatted_address,photo';
        if (!$_GET['free'] && $_GET['oneDollar'] && !$_GET['twoDollar'] && !$_GET['threeDollar'])
        {
            $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
            $place_id = 'ChIJgxFrSUSuPIgRNMHA3whSTiA';
            $ajax->response = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$place_id.$fields.'&key='.$key), true);
        }
        else if (!$_GET['free'] && !$_GET['oneDollar'] && $_GET['twoDollar'] && !$_GET['threeDollar'])
        {
            $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
            $place_id = 'ChIJ26Km7jyuPIgRMMksnW9KTx0';
            $ajax->response = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$place_id.$fields.'&key='.$key), true);
        }
        else if (!$_GET['free'] && !$_GET['oneDollar'] && !$_GET['twoDollar'] && $_GET['threeDollar'])
        {
            $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
            $place_id = 'ChIJu9W4fD-uPIgRKLcd_YjV04s';
            $ajax->response = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?place_id='.$place_id.$fields.'&key='.$key), true);
        }
        else
        {
            $ajax->specialPlace = false;
            $key = 'S1W-ieZyern54fLk4CPDCu87ugxJ0rE4YAVmryZHwgQ';
            $ajax->response  = json_decode(file_get_contents('https://discover.search.hereapi.com/v1/discover?in=circle:42.2808,-83.7430;r=30000&q=restaurants&limit=100&apiKey='.$key), true);
        }

        $ajax->success = true;

        $ajax->send();
    }

    public function search_suggestions(Request $request, Response $response)
    {
        $ajax = new AjaxResponse();
        $query = json_encode($_GET['query']);
        $key = 'AIzaSyA3tAENcwKmOa6m2Y4B4SIXbEEi_GN0F4A';
        $request_url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input='.$query.'&inputtype=textquery&fields=formatted_address,name,place_id&locationbias=circle:2000@42.2808,-83.7430&key='.$key;
        $ajax->query = $_GET['query'];
        $ajax->response  = json_decode(file_get_contents($request_url), true);
        $ajax->success = true;

        $ajax->send();
    }

    public function recommend_place()
    {

        $ajax = new AjaxResponse();
        $google_place_id = $_POST['place_id'];
        $adventure = Adventure::findOneByID($google_place_id);

        // Add new location to database
        if (!$adventure)
        {
            error_log('Creating Adventure...');
            $adventure = new Adventure();
            $address = new Address();
            $adventure->google_place_id = $google_place_id;

            // Get geolocation of Place from Google
            $fields = 'place_id,address_components,geometry,price_level';
            $request_url = 'https://maps.googleapis.com/maps/api/place/details/json?fields='.$fields.'&place_id='.$google_place_id;
            $results = GoogleAPI::get($request_url);

            if ($results['price_level'])
            {
                $adventure->price_level = $results['price_level'];
            }

            $address->longitude = $results['geometry']['location']['lng'];
            $address->latitude = $results['geometry']['location']['lat'];
            
            $street = null;
            $street_number = null;
            foreach ($results['address_components'] as $component)
            {
                if (in_array('street_number', $component['types']))
                {
                    $street_number = $component['long_name'];
                }
                else if (in_array('route', $component['types']))
                {
                    $street = $component['short_name'];
                }
                else if (in_array('locality', $component['types']))
                {
                    $address->city = $component['long_name'];
                }
                else if (in_array('administrative_area_level_1', $component['types']))
                {
                    $address->state = $component['short_name'];
                }
                else if (in_array('country', $component['types']))
                {
                    $address->country = $component['long_name'];
                }
                else if (in_array('postal_code', $component['types']))
                {
                    $address->zipcode = $component['long_name'];
                }
            }

            $address->address = $street_number.' '.$street;

            $here_categories = [];

            // Find Here Place ID with coordinates
            $request_url = 'https://discover.search.hereapi.com/v1/discover?at='.$address->latitude.'%2C'.$address->longitude.'&lang=en-US';
            $results = HereAPI::get($request_url);

            if ($results)
            {
                $adventure->here_place_id = $results['items'][0]['id'];

                if ($adventure->here_place_id)
                {
                    $results = HereAPI::get('https://lookup.search.hereapi.com/v1/lookup?id='.$adventure->here_place_id);
                    $here_categories = $results['categories'];
                }
            }

            if ($adventure->save())
            {
                $address->adventure_id = $adventure->id;
                $address->save();

                // Save all categories associated to the place in database and check if never seen before categories
                foreach ($here_categories as $here_category)
                {
                    $here_category_obj = HereCategory::findOne(['name' => $here_category]);

                    if (!$here_category_obj)
                    {
                        $new_here_category = new HereCategory();
                        $new_here_category->name = $here_category;
                        $here_category_obj = $new_here_category->save();
                    }
                    
                    $category_map = new AdventureHereCategoryMap();
                    $category_map->adventure_id = $adventure->id;
                    $category_map->here_category_obj->id;
                    $category_map->save();
                }
            }
        }

        // TODO add check if IP has recently made similar recommendation
        $recommendation = new Recommendation();
        $recommendation->adventure_id = $adventure->id;
        $recommendation->ip = $_SERVER['REMOTE_ADDR'];
        $ajax->success = $recommendation->save();
        $ajax->send();
    }
}
