<?php
namespace Modules\Pages\Http\Controllers;

use Core\Head;
use Core\Response;

class PageController
{
    public function show(string $slug): Response
    {
        Head::setTitle('Page: ' . $slug);
        return new Response(view('Pages::page', ['slug' => $slug]));
    }
}
