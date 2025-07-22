<?php
/**
 * Endpoint для проверки текущей авторизации (POST, API-стандарт)
 * Возвращает информацию о пользователе и сессии
 */

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../middleware/SessionMiddleware.php';

class AuthCheckEndpoint {
    private $db;
    private $config;
    private $auth;
    private $data;

    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->parseRequest();
    }

    private function parseRequest() {
        $input = json_decode(file_get_contents('php://input'), true);
        $this->auth = $input['auth'] ?? [];
        $this->data = $input['data'] ?? [];
    }

    public function handle() {
        try {
            // Используем middleware для проверки сессии
            $middleware = new SessionMiddleware($this->db, $this->config);
            $result = $middleware->handle($this->auth);
            if ($result && isset($result['error'])) {
                return $this->error($result['error'], 401);
            }

            // Получаем пользователя и сессию через публичные методы
            $user = $middleware->getUser();
            $session = $middleware->getSession();

            if (!$user || !$session) {
                return $this->error('Пользователь или сессия не найдены', 401);
            }

            // Вычисляем оставшееся время сессии
            $expiresAt = strtotime($session['expires_at']);
            $remainingTime = $expiresAt - time();

            return $this->success([
                'user' => $this->formatUser($user),
                'session' => [
                    'expires_at' => $session['expires_at'],
                    'remaining_seconds' => max(0, $remainingTime),
                    'is_active' => $session['is_active']
                ]
            ]);
        } catch (Exception $e) {
            return $this->serverError('Internal server error');
        }
    }

    private function formatUser($user) {
        return [
            'id' => $user['id'],
            'telegram_id' => $user['telegram_id'],
            'username' => $user['username'],
            'first_name' => $user['first_name_tg'],
            'last_name' => $user['last_name_tg'],
            'role' => $user['role_code'] ?? 'new',
            'role_name' => $user['role_name'] ?? 'Новый участник',
            'telegram_photo_url' => $user['telegram_photo_url'],
            'last_telegram_auth' => $user['last_telegram_auth']
        ];
    }

    private function success($data) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => 'OK'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function serverError($message) {
        $this->error($message, 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $config = require __DIR__ . '/../../config/config.php';
    $db = Database::getInstance()->getConnection();
    $config = [];
    $endpoint = new AuthCheckEndpoint($db, $config);
    $endpoint->handle();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'code' => 405
    ], JSON_UNESCAPED_UNICODE);
    exit;
} 