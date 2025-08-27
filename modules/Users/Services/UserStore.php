<?php
namespace Modules\Users\Services;

final class UserStore
{
    private static function file(): string
    {
        return \base_path('storage/users.json');
    }

    private static function ensureFile(): void
    {
        $f = self::file();
        if (!is_file($f)) {
            @file_put_contents($f, json_encode([]));
        }
    }

    /** @return array<int, array{email:string,password:string,role:string,name:string}> */
    public static function all(): array
    {
        self::ensureFile();
        $data = json_decode((string)file_get_contents(self::file()), true);
        return is_array($data) ? $data : [];
    }

    public static function saveAll(array $users): void
    {
        file_put_contents(self::file(), json_encode($users, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public static function findByEmail(string $email): ?array
    {
        foreach (self::all() as $u) {
            if (strcasecmp($u['email'] ?? '', $email) === 0) return $u;
        }
        return null;
    }

    public static function create(string $email, string $password, string $name = '', string $role = 'user'): array
    {
        $users = self::all();
        if (self::findByEmail($email)) {
            throw new \RuntimeException('Bu e-posta zaten kayıtlı.');
        }
        $user = [
            'email' => $email,
            'name' => $name ?: explode('@', $email)[0],
            'role' => $role,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];
        $users[] = $user;
        self::saveAll($users);
        return $user;
    }
}
