<?php

require __DIR__ . '/../vendor/autoload.php';

use Framework\Router;
use Framework\Session;
use Framework\Database;

// Start the session
Session::start();

// Load helpers
require '../helpers.php';

// Load DB and config
$config = require basePath('config/db.php');
$db = new Database($config);

// Instantiating the router
$router = new Router();

// Get routes
$routes = require basePath('routes.php');

// Get current URI and HTTP Method.
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route the request
$router->route($uri);
