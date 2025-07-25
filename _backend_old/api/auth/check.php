<?php
/**
 * API Endpoint: Проверка авторизации пользователя и сессии
 * POST /api/auth/check.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';
require_once __DIR__ . '/../../middleware/SessionMiddleware.php';

class AuthCheckEndpoint extends ApiHandler {
    protected function process() {
        $db = $this->getDb();
        $config = [];
        $middleware = new SessionMiddleware($db, $config);
        $result = $middleware->handle($this->auth);
        if ($result && isset($result['error'])) {
            return $this->error($result['error'], 401, 'AUTH_ERROR');
        }
        $user = $middleware->getUser();
        $session = $middleware->getSession();
        if (!$user || !$session) {
            return $this->error('Пользователь или сессия не найдены', 401, 'AUTH_ERROR');
        }
        $expiresAt = strtotime($session['expires_at']);
        $remainingTime = $expiresAt - time();
        return $this->success([
            'user' => [
                'id' => $user['id'],
                'telegram_id' => $user['telegram_id'],
                'username' => $user['username'],
                'first_name' => $user['first_name_tg'],
                'last_name' => $user['last_name_tg'],
                'role' => $user['role_code'] ?? 'new',
                'role_name' => $user['role_name'] ?? 'Новый участник',
                'telegram_photo_url' => $user['telegram_photo_url'],
                'last_telegram_auth' => $user['last_telegram_auth']
            ],
            'session' => [
                'expires_at' => $session['expires_at'],
                'remaining_seconds' => max(0, $remainingTime),
                'is_active' => $session['is_active']
            ]
        ], 'OK');
    }
}

$endpoint = new AuthCheckEndpoint();
$endpoint->handle(); 