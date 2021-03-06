<?php
namespace Core;

/**
 * Class Router
 * @package Core
 */
class Router
{
    private $controller = null;
    private $action = null;

    /**
     * Router constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $url = $_SERVER['REQUEST_URI'];
        $routes = require_once $app->getRootPath() . 'Route/route.php';
        foreach ($routes as $route => $path) {
            //TODO: Realize params
            //if (preg_match_all('|^' . preg_quote($route) . '$|', $url, $params) !== false) {
            if ($route === $url) {
                $temp = explode('@', $path);
                $this->action = array_pop($temp);
                $this->controller = array_pop($temp);
                break;
            }
        }
    }

    /**
     * @return string|null
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string|null
     */
    public function getAction(): string
    {
        return $this->action;
    }

}

