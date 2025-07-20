<?php
/**
 * Endpoint для проверки текущей авторизации
 * Возвращает информацию о пользователе и сессии
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/SessionMiddleware.php';

class AuthCheckEndpoint {
    private $db;
    private $config;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
    }
    
    /**
     * Обработка GET запроса на проверку авторизации
     */
    public function handle($request) {
        try {
            // Используем middleware для проверки сессии
            $middleware = new SessionMiddleware($this->db, $this->config);
            $result = $middleware->handle($request);
            
            if ($result) {
                // Middleware вернул ошибку
                return $result;
            }
            
            // Получаем пользователя из контекста
            $user = $request->getUser();
            $session = $request->getSession();
            
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
    
    /**
     * Форматирует данные пользователя для ответа
     */
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
    
    /**
     * Возвращает успешный ответ
     */
    private function success($data) {
        http_response_code(200);
        return json_encode([
            'success' => true,
            'data' => $data,
            'message' => 'OK'
        ]);
    }
    
    /**
     * Возвращает ошибку 500
     */
    private function serverError($message) {
        http_response_code(500);
        return json_encode([
            'success' => false,
            'error' => $message,
            'code' => 500
        ]);
    }
}

// Обработка запроса
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $config = require __DIR__ . '/../../config/config.php';
    $db = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4",
        $config['database']['user'],
        $config['database']['password']
    );
    
    $endpoint = new AuthCheckEndpoint($db, $config);
    $response = $endpoint->handle($_REQUEST);
    
    header('Content-Type: application/json');
    echo $response;
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'code' => 405
    ]);
} 