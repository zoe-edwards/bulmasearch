<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::create(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(['ALGOLIA_APP_ID', 'ALGOLIA_API_KEY']);
