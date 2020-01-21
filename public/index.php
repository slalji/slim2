<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
require __DIR__ . '/../vendor/autoload.php';


$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);
$container = $app->getContainer();


// Set up dependencies / load additional modules here
require __DIR__ . '/../src/dependencies.php';

// Set up error handling here
//require __DIR__ . '/../src/errorHandlers.php';

// Register middleware here
//require __DIR__ . '/../src/middleware.php';

// Register routes here
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();