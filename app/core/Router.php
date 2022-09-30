<?php

namespace app\core;
use app\core\exception\NotFoundException;
use Exception;

/**
 *  Class Router
 * 
 *  Manage routing between pages and return to login screen if user's credentials aren't authorized
 */
class Router
{
    public Response $response;
    public Request $request;
    protected array $routes = [];
    private bool $show_navigation = false;

    public function __construct(Request $request, Response $response)
    {
        $this->response = $response;
        $this->request = $request;
    }

    public function set($routes)
    {
        $this->routes = $routes;
    }

    /**
     * resolve
     * 
     * handles if url path is valid and is contains a view file before rendering it
     *
     * @return void
     */
    public function resolve()
    {
        $ajaxFunction = '';

        // URL path without the domain and query parameters
        $path = $this->request->getPath();

        // Redirect to login page if not logged in
        // if (!Application::$app->loggedInUser)
        // {
        //     $path = "/login";
        // }
        // // Redirect to home if already logged in
        // else if ($path == "/login")
        // {
        //     $path = "/";
        // }

        // Callback (or view file name) defined in index.php
        $callback = $this->routes[$path] ?? false;
        
        if ($callback == false)
        {
            // www.domain.com/controller/function --> www.domain.com/controller
            $rootPath = substr($path, 0, strrpos($path, '/'));
            $ajaxFunction = substr($path, strrpos($path, '/') + 1);

            $callback = $this->routes[$rootPath] ?? false;
        }

        if ($callback === false)
        {
            throw new NotFoundException();
        }

        // if it's string, then assume it's pointing to a view
        if (is_string($callback))
        {
            return Application::$app->view->renderView($callback);
        }

        if(is_array($callback))
        {
            return call_user_func([new $callback[0], $ajaxFunction ?: $callback[1]], $this->request, $this->response);
        }

        // Callback could also be a function
        return call_user_func($callback, $this->request, $this->response);
    }
}