<?php

/**
 * Class Application
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
        $this->rootPath = rtrim(preg_replace('/core\/?$/', '', __DIR__), '/') . '/';
        require_once 'router.php';
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
            include $this->getRootPath() . 'views/404.php';
        }

        require_once $this->getRootPath() . 'controllers/' . $controllerName . '.php';
        $controller = new $controllerName;
        $controller->{$actionName}();
    }
}

return Application::getInstance();