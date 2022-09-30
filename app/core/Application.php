<?php

namespace app\core;
use app\models\User;

/**
 *  Application Class
 * 
 *  Main class for project that is used to access the router
 */
class Application
{
    public static string $ROOT_DIR;

    public static Application $app;
    public Database $db;
    public Response $response;
    public Request $request;
    public Router $router;
    public Session $session;
    public $users; 
    public View $view;
    public $navigation = [];
    public $config = [];
    
    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->config = $config;

        $this->loggedInUser = null;
        $this->session = new Session();

        $this->db = new Database($config['db']);
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->view = new View();
        
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    public function run()
    {
        try
        {
            echo $this->router->resolve();
        }
        catch (\Exception $e)
        {
            
            if ($code = '42000')
            {
                error_log(print_r($e->getMessage(), true));
            }
            else
            {
                error_log(print_r($e->getCode(), true));
            }

            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
        
    }

    public function login(LdapModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);

        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }

    public static function isLoggedIn()
    {
        return self::$app->user;
    }
}