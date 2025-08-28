<?php
namespace Modules\Blog\Http\Controllers;

use Core\Request;
use Core\Response;

class BlogHomeController
{
    public function index(Request $req): Response
    {
        ob_start();
        include base_path('modules/Blog/views/index.php');
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }
}