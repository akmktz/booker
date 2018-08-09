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
            ->select(['COUNT', 't.id', 'count'],  ['t.name', 'user_name']  , ['AVG', 't.age', 'age_avg'])
            //->where('t.id', '>', 0)
            //->where('t.id', '<', 100)
            ->groupBy('t.name')
            ->orderBy('age_avg')
            //->orderBy('t.id', 'DESC')
            ->execute();

        var_dump($query->getAll());
        //var_dump($query->getAll('id', 'user_name'));
        die();
        return $this->view('Home/index', ['param' => 'TEST <PARAM>']);
    }
}