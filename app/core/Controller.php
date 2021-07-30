<?php

namespace app\core;

class Controller
{
    protected $json = null;
    protected $sentJson = false;
    protected $sendsJson = false;

    public function render($view, $params = [])
    {
        return Application::$app->view->renderView($view, $params);
    }
}