<?php
namespace App\Jobs;

use Core\Logger;

/**
 * Örnek iş: şimdilik sadece log’a yazar.
 * Gerçek mail göndermeyi Notification katmanıyla ekleyebiliriz.
 */
final class WelcomeMailJob
{
    /**
     * @param array $data ['to'=>'mail@example.com','name'=>'Mesut']
     * @return bool true=success, false=retry/fail
     */
    public function handle(array $data): bool
    {
        $to   = $data['to']   ?? null;
        $name = $data['name'] ?? 'User';
        if (!$to) {
            Logger::error("[WelcomeMailJob] missing 'to'");
            return false; // tekrar denensin
        }

        Logger::info("[WelcomeMailJob] to={$to} name={$name}");
        // simulate
        usleep(200 * 1000);
        return true;
    }
}