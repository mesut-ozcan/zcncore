<?php
use Core\Auth\Gate;

/**
 * Bu dosya Kernel boot sırasında include edilip Gate tanımlarını kaydeder.
 * Örnek iki yetenek:
 *  - users.manage: sadece admin rolü
 *  - account.update: kullanıcı kendi hesabını güncelleyebilir
 */
return function() {
    Gate::define('users.manage', function($user){
        return $user && strtolower($user['role'] ?? '') === 'admin';
    });

    Gate::define('account.update', function($user, $targetUserId){
        return $user && (int)($user['id'] ?? 0) === (int)$targetUserId;
    });
};
