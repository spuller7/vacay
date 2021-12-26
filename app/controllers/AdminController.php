<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\AjaxResponse;
use app\core\Request;
use app\core\Response;

use app\models\Adventure;

class AdminController extends Controller {
    
    public function __construct()
    {
        
    }

    public function index()
    {
        $data = [
            'title' => 'Admin'
        ];

        return $this->render('admin', $data);
    }

    public function getAdventureStats()
    {
        $ajax = new AjaxResponse();
        $google_place_id = $_POST['place_id'];


    }
}