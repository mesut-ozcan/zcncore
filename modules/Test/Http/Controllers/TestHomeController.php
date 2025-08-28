<?php
namespace Modules\Test\Http\Controllers;

use Core\Request;
use Core\Response;

class TestHomeController
{
    public function index(Request $req): Response
    {
        ob_start();
        include base_path('modules/Test/views/index.php');
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }
}