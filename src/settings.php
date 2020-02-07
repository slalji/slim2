<?php
/**
 * Application settings
 *
 * --> Settings that will not change between environment or instances should go here.
 *
 * --> Envirnoment variables and private settings should be set in config/.env
 *
 */
$db_local[] = [
	"host" => "localhost",
	"dbname" => "barcode",
	"user" => "root",
	'pass' => ''
];
$db[]= [
				"host" => "127.0.0.1",
				"dbname" => "likejagg_barcode",
				"user" => "likejagg_barcode",
				"pass" => "5xKu1WjoEJj4qptK"

			];

if ($_SERVER["HTTP_HOST"] === 'localhost')
	$db = $db_local;

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Renderer settings
        'renderer' => [
            'template_path' => $_SERVER['DOCUMENT_ROOT']. '/../src/View/',
            'template_cache' => $_SERVER['DOCUMENT_ROOT']  . '/../cache/twig/',
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
			"db" => $db,
//        "db" => [
//            "host" => "127.0.0.1",
//            "dbname" => "barcode",
//            "user" => "root",
//            //"pass" => "5xKu1WjoEJj4qptK"
//            'pass' => 'K9@m2076'
//        ],
//			"db" => [
//				"host" => "127.0.0.1",
//				"dbname" => "likejagg_barcode",
//				"user" => "likejagg_barcode",
//				"pass" => "5xKu1WjoEJj4qptK"
//				//'pass' => 'K9@m2076'
//			],

        'assets' =>
            [
                'build_dir' =>  '/../public/assets/'  // path for front-end build output
            ]
    ],
];