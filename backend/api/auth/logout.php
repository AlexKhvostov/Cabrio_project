<?php
/**
 * Endpoint для выхода из системы
 * Деактивирует текущую сессию
 */

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../middleware/SessionMiddleware.php';

class LogoutEndpoint {
    private $db;
    private $config;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
    }
    
    /**
     * Обработка POST запроса на выход
     */
    public function handle($request) {
        try {
            // Используем middleware для проверки сессии
            $middleware = new SessionMiddleware($this->db, $this->config);
            $result = $middleware->handle($request);
            if ($result && isset($result['error'])) {
                return json_encode($result); // Возвращаем ошибку как JSON
            }
            // Получаем сессию из middleware
            $session = $middleware->getSession();
            // Деактивируем сессию
            $this->invalidateSession($session['id']);
            return $this->success([
                'message' => 'Successfully logged out'
            ]);
        } catch (Exception $e) {
            return $this->serverError('Internal server error');
        }
    }
    
    /**
     * Деактивирует сессию
     */
    private function invalidateSession($sessionId) {
        $stmt = $this->db->prepare("
            UPDATE sessions SET is_active = 0 WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $config = require __DIR__ . '/../../config/config.php';
    $db = Database::getInstance()->getConnection();
    $config = [];
    $input = json_decode(file_get_contents('php://input'), true);
    $endpoint = new LogoutEndpoint($db, $config);
    $response = $endpoint->handle($input);
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