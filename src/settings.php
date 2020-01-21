<?php
/**
 * Application settings
 *
 * --> Settings that will not change between environment or instances should go here.
 *
 * --> Envirnoment variables and private settings should be set in config/.env
 *
 */

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../src/View/',
            'template_cache' => __DIR__  . '/../cache/twig/',
            'page_js' => false,  // if true will try to load `www/build/js/{{page_name}}.js`
            'page_css' => false // if true will try to load `www/build/js/{{page_name}}.css`
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-test',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/error.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        // Database connection settings
        "db" => [
            "host" => "127.0.0.1",
            "dbname" => "barcode",
            "user" => "root",
            //"pass" => "5xKu1WjoEJj4qptK"
            'pass' => 'K9@m2076'
        ],

        'assets' =>
            [
                'build_dir' => __DIR__ . '/../public/assets/'  // path for front-end build output
            ]
    ],
];