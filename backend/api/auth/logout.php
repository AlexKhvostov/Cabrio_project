<?php
/**
 * API Endpoint: Выход пользователя (logout), деактивация сессии
 * POST /api/auth/logout.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';
require_once __DIR__ . '/../../middleware/SessionMiddleware.php';

class LogoutEndpoint extends ApiHandler {
    protected function process() {
        $db = $this->getDb();
        $config = [];
        $middleware = new SessionMiddleware($db, $config);
        $result = $middleware->handle($this->auth);
        if ($result && isset($result['error'])) {
            return $this->error($result['error'], 401, 'AUTH_ERROR');
        }
        $session = $middleware->getSession();
        if (!$session) {
            return $this->error('Сессия не найдена', 401, 'AUTH_ERROR');
        }
        // Деактивируем сессию
        $stmt = $db->prepare("UPDATE sessions SET is_active = 0 WHERE id = ?");
        $stmt->execute([$session['id']]);
        return $this->success(['message' => 'Successfully logged out']);
    }
}

$endpoint = new LogoutEndpoint();
$endpoint->handle(); 