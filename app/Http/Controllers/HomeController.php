<?php
namespace App\Http\Controllers;

use Core\Head;
use Core\Response;

class HomeController
{
    public function index(): Response
    {
        Head::setTitle('Welcome — ZCNCore');
        Head::setDescription('Minimal Saf PHP çekirdek framework — module/theme ile esnek.');
        return new Response(view('home', ['title' => 'ZCNCore']));
    }
}
