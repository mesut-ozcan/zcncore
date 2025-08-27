<?php
namespace Modules\Users\Http\Controllers;

use Core\Response;

class AccountController
{
    public function index(): Response
    {
        $user = $_SESSION['user'] ?? null;
        return new Response(view('Users::account', [
            'title' => 'Hesabım',
            'user'  => $user
        ]));
    }
}
