<?php
    require_once __DIR__.'/../vendor/autoload.php';

    // Require libraries from folder libraries
    //require_once 'libraries/Core.php';
    //require_once 'libraries/Controller.php';
    //require_once 'libraries/Database.php';

    $helper_files = glob(__DIR__.'/helpers/*');
    foreach ($helper_files as $filename) {
        require $filename;
    }

    require_once 'config/config.php';

    //$init = new Core;
?>