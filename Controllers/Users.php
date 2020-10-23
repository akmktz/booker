<?php
namespace Controllers;

use Core\Controller;
use Core\Database\QueryBuilder;

/**
 * Class Home
 * @package Controllers
 */
class Users extends Controller
{
    public function before()
    {
        $this->h1 = 'Users';
        $this->title = 'Booker: Users ';
    }

    /**
     * Index action.
     */
    public function index()
    {
        $query = QueryBuilder::factory(['table', 't'])
            ->select(
                'age',
                ['COUNT', 't.id', 'users_count'],
                ['GROUP_CONCAT', 't.id', 'user_ids'],
                ['GROUP_CONCAT', 't.name', 'user_names']
            )
                ->whereGroupBegin()
                ->where('t.age', '>=', 20)
                ->where('t.age', '<=', 29)
                ->whereGroupEnd()
                ->orWhereGroupBegin()
                ->where('t.age', '>=', 30)
                ->where('t.age', '<=', 39)
                ->whereGroupEnd()
            ->groupBy('t.age')
            ->orderBy('t.age', 'DESC');

        return $this->view('Users/index', ['list' => $query->getAll()]);
    }
}
