<?php


require __DIR__ . '/../vendor/autoload.php';
// Dependencies: Add and initialize global modules here
$container = $app->getContainer();

// Register middleware
require __DIR__ . '/../src/middleware.php';

// use TwigRenderer for views

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../src/View/twig/', [
        'cache' => false,

    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    //$uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    //$view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension( new Slim\Views\TwigExtension( $container['router'], $container['request']->getUri() ) );
    return $view;


};

$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'],
        $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
$container['logger'] = function () {
    $logger = new Monolog\Logger('logger');
    $filename = _DIR__ . '../log/error.log';
    $stream = new Monolog\Handler\StreamHandler($filename, Monolog\Logger::DEBUG);
    $fingersCrossed = new Monolog\Handler\FingersCrossedHandler(
        $stream, Monolog\Logger::ERROR);
    $logger->pushHandler($fingersCrossed);

    return $logger;
};
// Activating routes in a subfolder
$container['environment'] = function () {
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$_SERVER['SCRIPT_NAME'] = dirname(dirname($scriptName)) . '/' . basename($scriptName);
	return new Slim\Http\Environment($_SERVER);
};