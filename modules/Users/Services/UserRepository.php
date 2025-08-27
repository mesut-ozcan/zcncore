<?php
namespace Modules\Users\Services;

use Core\Database;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function findByEmail(string $email): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $st->execute([$email]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function create(string $email, string $password, string $name = '', string $role = 'user'): array
    {
        if ($this->findByEmail($email)) {
            throw new \RuntimeException('Bu e-posta zaten kayıtlı.');
        }
        $name = $name ?: explode('@', $email)[0];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $st = $this->pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
        $st->execute([$name, $email, $hash, $role]);

        $id = (int)$this->pdo->lastInsertId();
        return [
            'id'    => $id,
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
        ];
    }

    public function verify(string $email, string $password): ?array
    {
        $u = $this->findByEmail($email);
        if (!$u) return null;
        if (!password_verify($password, $u['password'])) return null;
        return $u;
    }

    // Şifre sıfırlama token işlemleri
    public function createResetToken(string $email): string
    {
        $token = bin2hex(random_bytes(20));
        $st = $this->pdo->prepare("INSERT INTO password_resets (email, token, used) VALUES (?,?,0)");
        $st->execute([$email, $token]);
        return $token;
    }

    public function validateResetToken(string $token): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM password_resets WHERE token = ? LIMIT 1");
        $st->execute([$token]);
        $row = $st->fetch();
        if (!$row || (int)$row['used'] === 1) return null;

        // 1 saatten eski tokenları geçersiz say (opsiyonel)
        $created = strtotime($row['created_at'] ?? 'now');
        if ($created && (time() - $created) > 3600) {
            return null;
        }
        return $row;
    }

    public function consumeResetToken(string $token): void
    {
        $st = $this->pdo->prepare("UPDATE password_resets SET used=1 WHERE token=?");
        $st->execute([$token]);
    }

    public function updatePassword(string $email, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $st = $this->pdo->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE email=?");
        $st->execute([$hash, $email]);
    }
}
