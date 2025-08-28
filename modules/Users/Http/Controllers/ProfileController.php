<?php
namespace Modules\\Users\\Http\\Controllers;

use Core\Request;
use Core\Response;

class ProfileController
{
    public function index(Request $req): Response
    {
        return new Response('<h1>ProfileController::index</h1>', 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }
}