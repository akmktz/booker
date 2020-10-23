<?php

use Core\Application;

// TODO: MOVE TO CONFIG
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__.'/../Core/Autoloader.php';

$application = Application::getInstance();

$application->run();
