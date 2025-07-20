<?php
/**
 * Middleware для проверки сессий авторизации
 * Проверяет токен сессии, валидность и права доступа
 */

class SessionMiddleware {
    private $db;
    private $config;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
    }
    
    /**
     * Обработка запроса с проверкой авторизации
     */
    public function handle($request) {
        // Получаем токен из заголовка
        $token = $this->extractToken($request);
        
        // Проверяем наличие токена
        if (!$token) {
            return $this->unauthorized('No session token provided');
        }
        
        // Получаем сессию из БД
        $session = $this->getSession($token);
        if (!$session) {
            return $this->unauthorized('Invalid session token');
        }
        
        // Проверяем активность сессии
        if (!$session['is_active']) {
            return $this->unauthorized('Session is inactive');
        }
        
        // Проверяем истечение сессии
        if (time() > strtotime($session['expires_at'])) {
            $this->invalidateSession($session['id']);
            return $this->unauthorized('Session expired');
        }
        
        // Получаем пользователя
        $user = $this->getUser($session['user_id']);
        if (!$user) {
            $this->invalidateSession($session['id']);
            return $this->unauthorized('User not found');
        }
        
        // Проверяем членство в чате (каждый запрос!)
        if (!$this->checkChatMembership($user['telegram_id'])) {
            $this->updateUserRole($user['id'], 'external');
            return $this->forbidden('User not in club chat');
        }
        
        // Проверяем права доступа к функции
        $function = $request->getFunction();
        if (!$this->checkUserRole($user, $function)) {
            return $this->forbidden('Insufficient permissions');
        }
        
        // Сохраняем в контекст запроса
        $request->setUser($user);
        $request->setSession($session);
        
        return null; // Продолжаем обработку
    }
    
    /**
     * Извлекает токен из заголовка Authorization
     */
    private function extractToken($request) {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return null;
        }
        
        // Формат: "Bearer TOKEN" или просто "TOKEN"
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return $authHeader;
    }
    
    /**
     * Получает сессию из БД
     */
    private function getSession($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM sessions 
            WHERE session_token = ? AND is_active = 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Получает пользователя из БД
     */
    private function getUser($userId) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.code as role_code, r.name as role_name
            FROM users u
            LEFT JOIN ref_roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
     * Обновляет роль пользователя
     */
    private function updateUserRole($userId, $roleCode) {
        $stmt = $this->db->prepare("
            UPDATE users u
            JOIN ref_roles r ON r.code = ?
            SET u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$roleCode, $userId]);
    }
    
    /**
     * Проверяет права пользователя на функцию
     */
    private function checkUserRole($user, $function) {
        // Получаем минимальную роль для функции
        $requiredRole = $this->getRequiredRole($function);
        if (!$requiredRole) {
            return true; // Функция не требует авторизации
        }
        
        // Проверяем роль пользователя
        return $this->hasRole($user, $requiredRole);
    }
    
    /**
     * Проверяет, есть ли у пользователя нужная роль
     */
    private function hasRole($user, $requiredRole) {
        $roleHierarchy = [
            'external' => 0,
            'guest' => 1,
            'new' => 2,
            'registered' => 3,
            'member' => 4,
            'moderator' => 5,
            'admin' => 6
        ];
        
        $userRoleLevel = $roleHierarchy[$user['role_code']] ?? 0;
        $requiredRoleLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        return $userRoleLevel >= $requiredRoleLevel;
    }
    
    /**
     * Получает минимальную роль для функции
     */
    private function getRequiredRole($function) {
        $functionRoles = [
            'auth.login' => 'all',
            'auth.check' => 'all',
            'auth.logout' => 'all',
            'users.profile' => 'all',
            'users.profile.update' => 'registered',
            'users.list' => 'member',
            'cars.list' => 'member',
            'cars.add' => 'registered',
            'cars.update' => 'owner',
            'cars.delete' => 'owner',
            'events.list' => 'member',
            'events.add' => 'member',
            'events.update' => 'organizer',
            'events.delete' => 'organizer',
            'guide.list' => 'member',
            'guide.add' => 'member',
            'guide.update' => 'author',
            'guide.delete' => 'author',
            'admin.users' => 'admin',
            'admin.logs' => 'admin'
        ];
        
        return $functionRoles[$function] ?? 'all';
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
     * Деактивирует сессию
     */
    private function invalidateSession($sessionId) {
        $stmt = $this->db->prepare("
            UPDATE sessions SET is_active = 0 WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
    }
    
    /**
     * Возвращает ошибку 401
     */
    private function unauthorized($message) {
        return [
            'success' => false,
            'error' => $message,
            'code' => 401
        ];
    }
    
    /**
     * Возвращает ошибку 403
     */
    private function forbidden($message) {
        return [
            'success' => false,
            'error' => $message,
            'code' => 403
        ];
    }
} 