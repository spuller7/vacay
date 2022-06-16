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
use app\models\AdventureHereCategoryMap;
use app\models\Address;
use app\models\HereCategory;
use app\models\Recommendation;

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

        $query = "  SELECT DISTINCT adventures.id, (IFNULL(month_recommendations.total, 0) + IFNULL(two_month_recommendations.total, 0) + IFNULL(three_month_recommendations.total, 0)) AS weight, 
                        (
                            3959 * acos (
                            cos ( radians(:lat) )
                            * cos( radians( addresses.latitude ) )
                            * cos( radians( addresses.longitude ) - radians(:lng) )
                            + sin ( radians(:lat) )
                            * sin( radians( addresses.latitude ) )
                        )) AS distance 
                    FROM adventures
                    INNER JOIN adventure_here_category_map AS ahcm ON ahcm.adventure_id = adventures.id
                    INNER JOIN here_categories ON here_categories.id = ahcm.here_category_id
                    INNER JOIN addresses ON addresses.adventure_id = adventures.id
                    LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) AS total FROM recommendations WHERE created_at >= DATE(NOW() - INTERVAL 1 MONTH) GROUP BY adventure_id) AS month_recommendations ON adventures.id = month_recommendations.adventure_id
                    LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) / 2 AS total FROM recommendations WHERE created_at < DATE(NOW() - INTERVAL 1 MONTH) AND created_at >= DATE(NOW() - INTERVAL 2 MONTH) GROUP BY adventure_id) AS two_month_recommendations ON adventures.id = two_month_recommendations.adventure_id
                    LEFT JOIN (SELECT adventure_id, COUNT(adventure_id) / 3 AS total FROM recommendations WHERE created_at < DATE(NOW() - INTERVAL 2 MONTH) AND created_at >= DATE(NOW() - INTERVAL 3 MONTH) GROUP BY adventure_id) AS three_month_recommendations ON adventures.id = three_month_recommendations.adventure_id
                    WHERE 1=1 ";
        $params = [];

        // Get geolocation of Place from Google
        $fields = 'place_id,address_components,geometry,price_level';
        $request_url = 'https://maps.googleapis.com/maps/api/place/details/json?fields='.$fields.'&place_id='.$_POST['cityPlaceID'];
        $results = GoogleAPI::get($request_url)['result'];

        $params['lat'] = $results['geometry']['location']['lat'];
        $params['lng'] = $results['geometry']['location']['lng'];
        
        if ($price_levels = $_POST['prices'])
        {
            $query .= " AND price_level IN (:price_levels) ";
            $params['price_levels'] = implode(', ', $price_levels);;
        }

        if ($categories = $_POST['categories'])
        {
            $query .= " AND here_categories.id IN (:categories) ";
            $params['categories'] = implode(', ', $categories);
        }


        $query .= 'HAVING distance < 25 AND weight != 0 ORDER BY -LOG(1.0 - RAND()) / weight';

        $adventures = Adventure::query($query, $params);
        error_log(print_r($adventures, true));

        $ajax->success = true;
        $ajax->send();
    }

    public function search_suggestions(Request $request, Response $response)
    {
        $ajax = new AjaxResponse();
        $query = json_encode($_GET['query']);
        $request_url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query='.urlencode($query).'&locationbias=circle:2000@42.2808,-83.7430';
        $ajax->query = $_GET['query'];
        $ajax->response  = GoogleAPI::get($request_url)['results'] ?: null;
        $ajax->success = true;

        $ajax->send();
    }

    public function search_cities(Request $request, Response $response)
    {
        $ajax = new AjaxResponse();
        $query = json_encode($_GET['query']);
        $request_url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.urlencode($query).'&types=%28cities%29';
        $ajax->query = $_GET['query'];
        $ajax->response  = GoogleAPI::get($request_url)['predictions'] ?: null;
        $ajax->success = true;

        $ajax->send();
    }


    public function recommend_place()
    {
        $ajax = new AjaxResponse();
        $google_place_id = $_POST['place_id'];
        $adventure = Adventure::findOne(['google_place_id' => $google_place_id]);

        // Add new location to database
        if (!$adventure)
        {
            $adventure = new Adventure();
            $address = new Address();
            $adventure->google_place_id = $google_place_id;

            // Get geolocation of Place from Google
            $fields = 'place_id,address_components,geometry,price_level';
            $request_url = 'https://maps.googleapis.com/maps/api/place/details/json?fields='.$fields.'&place_id='.$google_place_id;
            $results = GoogleAPI::get($request_url)['result'];

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
            $request_url = 'https://discover.search.hereapi.com/v1/discover?at='.$address->latitude.'%2C'.$address->longitude.'&q=restaurant&lang=en-US';
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

            $adventure = $adventure->save();

            if ($adventure)
            {
                $address->adventure_id = $adventure->id;
                $address->save();

                // Save all categories associated to the place in database and check if never seen before categories
                foreach ($here_categories as $here_category)
                {
                    $_here_category = HereCategory::findOne(['name' => $here_category['name']]);

                    if (!$_here_category)
                    {
                        $new_here_category = new HereCategory();
                        $new_here_category->code = $here_category['id'];
                        $new_here_category->name = $here_category['name'];
                        $_here_category = $new_here_category->save();
                    }
                    
                    $category_map = new AdventureHereCategoryMap();
                    $category_map->adventure_id = $adventure->id;
                    $category_map->here_category_id = $_here_category->id;
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
