<?php

/**
 * Autoloader
 *
 * @param $class_name
 */
spl_autoload_register(function ($class_name) {
    include rtrim(preg_replace('/Core\/?$/', '', __DIR__), '/') . '/' . str_replace('\\', '/', $class_name) . '.php';
});