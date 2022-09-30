<?php
    require_once './app/require.php';

    use app\controllers\SearchController;
    use app\controllers\AdminController;

    use app\core\Application;
    use app\classes\Route;
    
    // Read variables from .env file
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__).'/'.ROOT.'/app');
    $dotenv->load();

    $config = [
        'db' => [
            'dsn' => $_ENV['DB_DSN'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD']
        ],
        'google_api_key' => $_ENV['GOOGLE_API_KEY'],
        'here_api_key' => $_ENV['HERE_API_KEY']
    ];

    $app = new Application(dirname(__DIR__).'/'.ROOT, $config);

    // Route setup mapping path to callback to get view
    $routes = [
        '/' => [SearchController::class, 'index'],
        '/search' => [SearchController::class, 'index'],
        '/admin' => [AdminController::class, 'index']
    ];

    $app->router->set($routes);

    $app->run();
?>