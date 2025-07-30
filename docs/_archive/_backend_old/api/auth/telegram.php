<?php
/**
 * API Endpoint: Авторизация через Telegram WebApp
 * POST /api/auth/telegram.php
 *
 * Запрос (по стандарту CabrioRide):
 * {
 *   "auth": { ... },
 *   "data": { "initData": "..." }
 * }
 */

require_once __DIR__ . '/../../utils/ApiHandler.php';
require_once __DIR__ . '/../../config/config.php';

class TelegramAuthEndpoint extends ApiHandler {
    protected function validateAuth() {
        // Для авторизации через Telegram auth может быть пустым
        return true;
    }

    protected function process() {
        // 1. Проверяем наличие initData в data
        $initData = $this->getData('initData');
        if (empty($initData)) {
            return $this->error('Missing initData', 400, 'VALIDATION_ERROR', ['field' => 'initData']);
        }

        // 2. Проверяем подпись Telegram (КРИТИЧНО!)
            if (!$this->verifyTelegramSignature($initData)) {
            return $this->error('Invalid Telegram signature', 400, 'VALIDATION_ERROR');
            }
            
        // 3. Парсим данные пользователя
            $telegramData = $this->parseInitData($initData);
            if (!$telegramData) {
            return $this->error('Invalid initData format', 400, 'VALIDATION_ERROR');
            }
            
        // 4. Проверяем членство в чате
            if (!$this->checkChatMembership($telegramData['user']['id'])) {
            return $this->error('User not in club chat', 403, 'ACCESS_DENIED');
            }
            
        // 5. Создаём или обновляем пользователя
            $user = $this->createOrUpdateUser($telegramData);
            if (!$user) {
            return $this->error('Failed to create/update user', 500, 'DATABASE_ERROR');
            }
            
        // 6. Создаём короткую сессию (30 минут)
            $session = $this->createShortSession($user['id'], $telegramData);
            if (!$session) {
            return $this->error('Failed to create session', 500, 'DATABASE_ERROR');
            }
            
        // 7. Обновляем время последней авторизации
            $this->updateLastTelegramAuth($user['id']);
            
        // 8. Логируем успешную авторизацию (можно через logRequest, если нужно)

        // 9. Возвращаем успешный ответ по стандарту CabrioRide
            return $this->success([
                'session_token' => $session['session_token'],
                'expires_at' => $session['expires_at'],
                'user' => $this->formatUser($user)
        ], 'OK');
    }
    
    // --- Вспомогательные методы (без изменений) ---
    private function verifyTelegramSignature($initData) {
        $config = require __DIR__ . '/../../config/config.php';
        $botToken = $config['telegram']['bot_token'];
        $data = [];
        parse_str($initData, $data);
        if (!isset($data['hash'])) return false;
        $hash = $data['hash'];
        unset($data['hash']);
        ksort($data);
        $dataCheckString = '';
        foreach ($data as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));
        return hash_equals($calculatedHash, $hash);
    }
    
    private function parseInitData($initData) {
        $data = [];
        parse_str($initData, $data);
        if (!isset($data['user'])) return null;
        $user = json_decode($data['user'], true);
        if (!$user || !isset($user['id'])) return null;
        return [
            'user' => $user,
            'auth_date' => $data['auth_date'] ?? null,
            'query_id' => $data['query_id'] ?? null
        ];
    }
    
    private function checkChatMembership($telegramId) {
        $config = require __DIR__ . '/../../config/config.php';
        $botToken = $config['telegram']['bot_token'];
        $chatId = $config['telegram']['club_chat_id'];
        $url = "https://api.telegram.org/bot{$botToken}/getChatMember";
        $data = [ 'chat_id' => $chatId, 'user_id' => $telegramId ];
        $response = $this->makeTelegramRequest($url, $data);
        if (!$response || !isset($response['ok']) || !$response['ok']) return false;
        $status = $response['result']['status'];
        return in_array($status, ['member', 'administrator', 'creator']);
    }
    
    private function createOrUpdateUser($telegramData) {
        $config = require __DIR__ . '/../../config/config.php';
        $db = Database::getInstance()->getConnection();
        $user = $telegramData['user'];
        $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$user['id']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingUser) {
            $stmt = $db->prepare("UPDATE users SET username = ?, first_name_tg = ?, last_name_tg = ?, telegram_photo_url = ?, updated_at = NOW() WHERE telegram_id = ?");
            $stmt->execute([
                $user['username'] ?? null,
                $user['first_name'] ?? null,
                $user['last_name'] ?? null,
                $user['photo_url'] ?? null,
                $user['id']
            ]);
            return $existingUser;
        } else {
            $stmt = $db->prepare("INSERT INTO users (telegram_id, username, first_name_tg, last_name_tg, telegram_photo_url, role_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, (SELECT id FROM ref_roles WHERE code = 'new'), NOW(), NOW())");
            $stmt->execute([
                $user['id'],
                $user['username'] ?? null,
                $user['first_name'] ?? null,
                $user['last_name'] ?? null,
                $user['photo_url'] ?? null
            ]);
            $userId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    private function createShortSession($userId, $telegramData) {
        $db = Database::getInstance()->getConnection();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);
        $stmt = $db->prepare("INSERT INTO sessions (user_id, session_token, telegram_data, created_at, expires_at, is_active) VALUES (?, ?, ?, NOW(), ?, 1)");
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
    
    private function updateLastTelegramAuth($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET last_telegram_auth = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
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
}

// --- Запуск обработчика ---
$endpoint = new TelegramAuthEndpoint();
$endpoint->handle(); 