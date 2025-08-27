<?php
namespace Modules\Users\Http\Controllers;

use Core\Response;
use Modules\Users\Services\UserStore;

class AuthController
{
    public function showLogin(): Response
    {
        return new Response(view('Users::login', ['title' => 'Giriş Yap']));
    }

    public function login(): Response
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = UserStore::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return new Response(view('Users::login', [
                'title' => 'Giriş Yap',
                'error' => 'E-posta veya şifre hatalı.'
            ]), 401);
        }

        $_SESSION['user'] = [
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'],
        ];
        return Response::redirect('/account');
    }

    public function showRegister(): Response
    {
        return new Response(view('Users::register', ['title' => 'Kayıt Ol']));
    }

    public function register(): Response
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirmation'] ?? '';

        if (!$email || !$password || $password !== $password2) {
            return new Response(view('Users::register', [
                'title' => 'Kayıt Ol',
                'error' => 'Alanları kontrol edin. Şifreler eşleşmeli.'
            ]), 422);
        }

        try {
            $user = UserStore::create($email, $password, $name, 'user');
        } catch (\Throwable $e) {
            return new Response(view('Users::register', [
                'title' => 'Kayıt Ol',
                'error' => $e->getMessage()
            ]), 409);
        }

        // Auto-login
        $_SESSION['user'] = [
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'],
        ];
        return Response::redirect('/account');
    }

    public function logout(): Response
    {
        unset($_SESSION['user']);
        return Response::redirect('/');
    }
}
