<?php
namespace Core;

/**
 * Class Application
 * @package Core
 */
class Application
{
    private static $instance;

    private $router = null;
    private $config;
    private $database;
    private $rootPath;


    /**
     * @return Application
     */
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self();
            $app = self::$instance;
            $app->initialize();
            $app->run();
        }
        return self::$instance;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    private function __clone() {}
    private function __construct() {}

    /**
     * Initialize application.
     */
    private function initialize()
    {
        // Routing
        $this->rootPath = rtrim(preg_replace('/Core\/?$/', '', __DIR__), '/') . '/';
        $this->router = new Router($this);
    }

    /**
     * Run application.
     */
    private function run()
    {
        $controllerName = $this->router->getController();
        $actionName = $this->router->getAction();
        if (!$controllerName || !$actionName) {
            http_response_code(404);
            ob_clean();
            include $this->getRootPath() . 'Views/404.php';
            die();
        }

        $controllerName = 'Controllers\\' . $controllerName;
        $controller = new $controllerName;
        $controller->{$actionName}();
    }
}
