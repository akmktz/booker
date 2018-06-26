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
    //TODO: Implement
    private $config;
    //TODO: Implement
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

    public function run()
    /**
     * Run application.
     */
    {
        $controllerName = $this->router->getController();
        $actionName = $this->router->getAction();
        if (!$controllerName || !$actionName) {
            ob_clean();
            http_response_code(404);
            include $this->getRootPath() . 'Views/404.php';
            die();
        }

        // Run controller and get rendered view
        $content = [];
        $controllerName = 'Controllers\\' . $controllerName;

        $controller = new $controllerName;
        $controller->before();
        $content['view'] = $controller->{$actionName}();
        $controller->after();

        $content['title'] = $controller->getTitle();
        $content['h1'] = $controller->getH1();

        // Put view into layout
        echo $controller->view($controller->getLayout(), compact('content'));
    }

    /**
     * Initialize application.
     */
    private function initialize()
    {
        // Routing
        $this->rootPath = rtrim(preg_replace('/Core\/?$/', '', __DIR__), '/') . '/';
        $this->router = new Router($this);
    }

    private function __clone() {}
    private function __construct() {}
}
