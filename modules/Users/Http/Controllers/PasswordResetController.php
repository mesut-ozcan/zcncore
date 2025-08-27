<?php
namespace Modules\Users\Http\Controllers;

use Core\Config;
use Core\Response;
use Modules\Users\Services\UserRepository;

class PasswordResetController
{
    public function showForgot(): Response
    {
        return new Response(view('Users::forgot', ['title'=>'Şifre Sıfırlama']));
    }

    public function send(): Response
    {
        $email = trim($_POST['email'] ?? '');
        if (!$email) {
            return new Response(view('Users::forgot', [
                'title'=>'Şifre Sıfırlama',
                'error'=>'E-posta gerekli.'
            ]), 422);
        }

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);
        if (!$user) {
            // kullanıcı yoksa da akış aynı döneriz (güvenlik)
            return new Response(view('Users::forgot', [
                'title'=>'Şifre Sıfırlama',
                'info'=>'Eğer e-posta kayıtlıysa bir sıfırlama bağlantısı oluşturuldu.'
            ]));
        }

        $token = $repo->createResetToken($email);
        $base = rtrim(Config::get('app.url',''), '/');
        $link = $base . '/password/reset/' . $token;

        // MVP: linki ekranda göster (mail gönderimi yok)
        return new Response(view('Users::forgot', [
            'title'=>'Şifre Sıfırlama',
            'info'=>"Sıfırlama bağlantısı: <code>" . e($link) . "</code> (1 saat geçerli)"
        ]));
    }

    public function showReset(string $token): Response
    {
        $repo = new UserRepository();
        $row = $repo->validateResetToken($token);
        if (!$row) {
            return new Response('<h1>Geçersiz veya süresi dolmuş token</h1>', 400);
        }
        return new Response(view('Users::reset', ['title'=>'Yeni Şifre', 'token'=>$token]));
    }

    public function reset(string $token): Response
    {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirmation'] ?? '';
        if (!$password || $password !== $password2) {
            return new Response(view('Users::reset', [
                'title'=>'Yeni Şifre',
                'token'=>$token,
                'error'=>'Şifreler eşleşmeli.'
            ]), 422);
        }

        $repo = new UserRepository();
        $row = $repo->validateResetToken($token);
        if (!$row) {
            return new Response('<h1>Geçersiz veya süresi dolmuş token</h1>', 400);
        }

        $repo->updatePassword($row['email'], $password);
        $repo->consumeResetToken($token);

        return new Response(view('Users::login', [
            'title'=>'Giriş Yap',
            'info'=>'Şifre güncellendi. Lütfen giriş yapın.'
        ]));
    }
}
