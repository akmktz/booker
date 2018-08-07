<?php

namespace Core;

/**
 * Class Controller
 * @package Core
 */
class Controller
{
    /**
     * @var Application
     */
    protected $application;
    protected $h1 = null;
    protected $title = null;
    protected $layout = 'layout';

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->application = Application::getInstance();
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->h1;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Before action run
     */
    public function before()
    {
        //
    }

    /**
     * After action run
     */
    public function after()
    {
        //
    }

    /**
     * Render View
     *
     * @param string $pathName
     * @param array $params
     * @param array $
     * @return string
     */
    public function view(string $pathName, array $params = [], array $unscreeningParams = []): string
    {
        // Screening of data
        foreach ($params as &$param) {
            if (is_string($param)) {
                $param = htmlentities($param);
            }
        }
        unset($param);

        extract($params, EXTR_SKIP);
        extract($unscreeningParams, EXTR_SKIP);

        ob_start();
        require $this->application->getRootPath() . 'Views/' . $pathName . '.php';

        return ob_get_clean();
    }
}