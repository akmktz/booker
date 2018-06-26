<?php

use Core\Application;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../Core/Autoloader.php';

$application = Application::getInstance();

$application->run();
