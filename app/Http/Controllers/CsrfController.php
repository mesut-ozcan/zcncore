<?php
namespace App\Http\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Csrf;

class CsrfController extends Controller
{
    public function refresh(Request $req): Response
    {
        // Yeni token üret ve cookie garanti altına al
        Csrf::ensureCookie();
        $token = Csrf::token();
        return Response::json(['ok'=>true,'token'=>$token]);
    }
}
