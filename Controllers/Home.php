<?php
namespace Controllers;

use Core\Controller;

/**
 * Class Home
 * @package Controllers
 */
class Home extends Controller
{
    public function before()
    {
        $this->h1 = 'Home';
        $this->title = 'Booker: Home';
    }

    /**
     * Index action.
     */
    public function index()
    {
        return $this->view('Home/index', ['param' => 'TEST <PARAM>']);
    }
}
