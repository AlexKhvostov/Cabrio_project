<?php
/**
 * Endpoint для авторизации через Telegram WebApp
 * Проверяет подпись Telegram, создаёт пользователя и сессию
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Logger.php';

class TelegramAuthEndpoint {
    private $db;
    private $config;
    private $logger;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->logger = new Logger();
    }
    
    /**
     * Обработка POST запроса на авторизацию
     */
    public function handle($request) {
        try {
            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['initData'])) {
                return $this->badRequest('Missing initData');
            }
            
            $initData = $input['initData'];
            
            // Проверяем подпись Telegram (КРИТИЧНО!)
            if (!$this->verifyTelegramSignature($initData)) {
                $this->logger->error('Invalid Telegram signature', ['initData' => $initData]);
                return $this->badRequest('Invalid Telegram signature');
            }
            
            // Парсим данные пользователя
            $telegramData = $this->parseInitData($initData);
            if (!$telegramData) {
                return $this->badRequest('Invalid initData format');
            }
            
            // Проверяем членство в чате
            if (!$this->checkChatMembership($telegramData['user']['id'])) {
                return $this->forbidden('User not in club chat');
            }
            
            // Создаём или обновляем пользователя
            $user = $this->createOrUpdateUser($telegramData);
            if (!$user) {
                return $this->serverError('Failed to create/update user');
            }
            
            // Создаём короткую сессию (30 минут)
            $session = $this->createShortSession($user['id'], $telegramData);
            if (!$session) {
                return $this->serverError('Failed to create session');
            }
            
            // Обновляем время последней авторизации
            $this->updateLastTelegramAuth($user['id']);
            
            // Логируем успешную авторизацию
            $this->logger->info('User authorized via Telegram', [
                'user_id' => $user['id'],
                'telegram_id' => $telegramData['user']['id']
            ]);
            
            return $this->success([
                'session_token' => $session['session_token'],
                'expires_at' => $session['expires_at'],
                'user' => $this->formatUser($user)
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Telegram auth error', ['error' => $e->getMessage()]);
            return $this->serverError('Internal server error');
        }
    }
    
    /**
     * Проверяет подпись Telegram WebApp
     */
    private function verifyTelegramSignature($initData) {
        $botToken = $this->config['telegram']['bot_token'];
        
        // Разбираем initData на параметры
        $data = [];
        parse_str($initData, $data);
        
        if (!isset($data['hash'])) {
            return false;
        }
        
        $hash = $data['hash'];
        unset($data['hash']);
        
        // Сортируем параметры по алфавиту
        ksort($data);
        
        // Создаём строку для проверки
        $dataCheckString = '';
        foreach ($data as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");
        
        // Создаём секретный ключ
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        
        // Вычисляем хеш
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));
        
        return hash_equals($calculatedHash, $hash);
    }
    
    /**
     * Парсит initData и извлекает данные пользователя
     */
    private function parseInitData($initData) {
        $data = [];
        parse_str($initData, $data);
        
        if (!isset($data['user'])) {
            return null;
        }
        
        $user = json_decode($data['user'], true);
        if (!$user || !isset($user['id'])) {
            return null;
        }
        
        return [
            'user' => $user,
            'auth_date' => $data['auth_date'] ?? null,
            'query_id' => $data['query_id'] ?? null
        ];
    }
    
    /**
     * Проверяет членство в чате через Telegram API
     */
    private function checkChatMembership($telegramId) {
        $botToken = $this->config['telegram']['bot_token'];
        $chatId = $this->config['telegram']['main_chat_id'];
        
        $url = "https://api.telegram.org/bot{$botToken}/getChatMember";
        $data = [
            'chat_id' => $chatId,
            'user_id' => $telegramId
        ];
        
        $response = $this->makeTelegramRequest($url, $data);
        
        if (!$response || !isset($response['ok']) || !$response['ok']) {
            return false;
        }
        
        $status = $response['result']['status'];
        return in_array($status, ['member', 'administrator', 'creator']);
    }
    
    /**
     * Создаёт или обновляет пользователя
     */
    private function createOrUpdateUser($telegramData) {
        $user = $telegramData['user'];
        
        // Проверяем, существует ли пользователь
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE telegram_id = ?
        ");
        $stmt->execute([$user['id']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // Обновляем существующего пользователя
            $stmt = $this->db->prepare("
                UPDATE users SET
                    username = ?,
                    first_name_tg = ?,
                    last_name_tg = ?,
                    telegram_photo_url = ?,
                    updated_at = NOW()
                WHERE telegram_id = ?
            ");
            
            $stmt->execute([
                $user['username'] ?? null,
                $user['first_name'] ?? null,
                $user['last_name'] ?? null,
                $user['photo_url'] ?? null,
                $user['id']
            ]);
            
            return $existingUser;
        } else {
            // Создаём нового пользователя
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    telegram_id, username, first_name_tg, last_name_tg,
                    telegram_photo_url, role_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 
                    (SELECT id FROM ref_roles WHERE code = 'new'), 
                    NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                $user['id'],
                $user['username'] ?? null,
                $user['first_name'] ?? null,
                $user['last_name'] ?? null,
                $user['photo_url'] ?? null
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Получаем созданного пользователя
            $stmt = $this->db->prepare("
                SELECT * FROM users WHERE id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * Создаёт короткую сессию (30 минут)
     */
    private function createShortSession($userId, $telegramData) {
        // Генерируем уникальный токен
        $token = bin2hex(random_bytes(32));
        
        // Устанавливаем время истечения (30 минут)
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);
        
        $stmt = $this->db->prepare("
            INSERT INTO sessions (
                user_id, session_token, telegram_data, 
                created_at, expires_at, is_active
            ) VALUES (?, ?, ?, NOW(), ?, 1)
        ");
        
        $stmt->execute([
            $userId,
            $token,
            json_encode($telegramData),
            $expiresAt
        ]);
        
        return [
            'session_token' => $token,
            'expires_at' => $expiresAt
        ];
    }
    
    /**
     * Обновляет время последней авторизации через Telegram
     */
    private function updateLastTelegramAuth($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET last_telegram_auth = NOW() WHERE id = ?
        ");
        $stmt->execute([$userId]);
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
            'telegram_photo_url' => $user['telegram_photo_url']
        ];
    }
    
    /**
     * Делает запрос к Telegram API
     */
    private function makeTelegramRequest($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
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
     * Возвращает ошибку 400
     */
    private function badRequest($message) {
        http_response_code(400);
        return json_encode([
            'success' => false,
            'error' => $message,
            'code' => 400
        ]);
    }
    
    /**
     * Возвращает ошибку 403
     */
    private function forbidden($message) {
        http_response_code(403);
        return json_encode([
            'success' => false,
            'error' => $message,
            'code' => 403
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
    $config = require __DIR__ . '/../../config/config.php';
    $db = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4",
        $config['database']['user'],
        $config['database']['password']
    );
    
    $endpoint = new TelegramAuthEndpoint($db, $config);
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