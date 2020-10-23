<?php
namespace Controllers;

use Core\Controller;

/**
 * Class Home
 * @package Controllers
 */
class Json extends Controller
{
    /**
     * Index action.
     */
    public function index()
    {
        return [
            'message' => 'Hello',
        ];
    }
}
