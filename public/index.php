<?php
    require_once '../app/require.php';

    use app\controllers\AdventureController;

    use app\core\Application;
    use app\classes\Route;

    Css::loadAll(array('datatable_actions', 'modal'));

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__).'/app');
    $dotenv->load();

    $config = [
        'db' => [
            'dsn' => $_ENV['DB_DSN'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ]
    ];

    $app = new Application(dirname(__DIR__), $config);

    // Route setup mapping path to callback to get view
    $routes = [
        '/' => [AdventureController::class, 'index'],
        '/adventure' => [AdventureController::class, 'index'],
    ];

    $app->router->set($routes);

    $app->run();
?>