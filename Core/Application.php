<?php
namespace Core;

use PDO;

/**
 * Class Application
 * @package Core
 */
class Application
{
    private static $instance;

    private $router = null;
    private $config = [];
    private $databaseConnection = null;
    private $rootPath = '';


    /**
     * @return Application
     */
    public static function getInstance(): Application
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
     * Get config
     *
     * @param string|null $section
     * @return array
     */
    public function getConfig(string $section = ''): array
    {
        if ($section) {
            return $this->config[$section] ?? [];
        }

        return $this->config;
    }

    /**
     * Get pdo connection to database
     *
     * @return PDO
     */
    public function getDatabaseConnection(): PDO
    {
        if (!$this->databaseConnection) {
            $dbConfig = $this->getConfig('database');
            $dsn = $dbConfig['dsn'] ?? '';
            $username = $dbConfig['username'] ?? '';
            $password = $dbConfig['password'] ?? '';

            $this->databaseConnection = new PDO($dsn, $username, $password);
            $this->databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->databaseConnection;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Run application.
     */
    public function run()
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
        // Config
        $databaseConfig = include '../Config/database.php';
        $this->config = [
          'database' => is_array($databaseConfig) ? $databaseConfig : [],
        ];

        // Routing
        $this->rootPath = rtrim(preg_replace('/Core\/?$/', '', __DIR__), '/') . '/';
        $this->router = new Router($this);
    }

    private function __clone() {}
    private function __construct() {}
}
