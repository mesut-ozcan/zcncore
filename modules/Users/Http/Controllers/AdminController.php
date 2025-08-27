<?php
namespace Modules\Users\Http\Controllers;

use Core\Response;

class AdminController
{
    public function dashboard(): Response
    {
        return new Response(view('Users::admin', [
            'title' => 'Admin Panel'
        ]));
    }
}
