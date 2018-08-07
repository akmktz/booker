<?php
namespace Controllers;

use Core\Controller;
use Core\Database\QueryBuilder;

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
        //$query = QueryBuilder::factory(['table\'', 't\''])
        //    ->select([1, 'one'], [2, 'two'], 'a', 'bb', 'ccc', ['test\'', 'test\''])
        //    ->where('t.name', '=', '"test\'(!@#$%^&*())')
        //    ->execute();

        $query = QueryBuilder::factory(['table', 't'])
            ->select('t.id', ['t.name', 'user_name'])
            ->where('t.id', '=', 1)
            ->orWhere('t.id', '=', 2)
            ->orWhere('t.id', '=', 3)
            ->execute();

        var_dump($query->getAll());
        //var_dump($query->getAll('id', 'user_name'));
        die();
        return $this->view('Home/index', ['param' => 'TEST <PARAM>']);
    }
}