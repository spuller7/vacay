<?php
    require_once __DIR__.'/../vendor/autoload.php';

    $helper_files = glob(__DIR__.'/helpers/*');
    foreach ($helper_files as $filename) {
        require $filename;
    }

    require_once 'config/config.php';

    //$init = new Core;
?>