<?php
namespace App\\Http\\Controllers;

use Core\Request;
use Core\Response;

class FooController
{
    public function index(Request $req): Response
    {
        return new Response('<h1>FooController::index</h1>', 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }
}