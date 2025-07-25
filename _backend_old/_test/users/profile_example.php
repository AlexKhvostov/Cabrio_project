<?php
/**
 * Пример endpoint'а профиля пользователя
 * Использует базовый класс ApiHandler
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "user_id": 456
 *   }
 * }
 */

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';
require_once __DIR__ . '/../../config/config.php';

class ProfileEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (только member+)
        $accessResult = $this->checkAccess('member');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем ID пользователя для просмотра
        $targetUserId = $this->requireField('user_id', 'ID пользователя обязателен');
        
        try {
            $db = $this->getDb();
            
            // Получаем профиль пользователя
            $sql = "SELECT 
                        u.*,
                        r.code as role_code,
                        r.name as role_name
                    FROM users u
                    LEFT JOIN ref_roles r ON u.role_id = r.id
                    WHERE u.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$targetUserId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return $this->error('Пользователь не найден', 404, 'NOT_FOUND');
            }
            
            // Формируем ответ
            $userData = [
                'id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name_tg'],
                'last_name' => $user['last_name_tg'],
                'role' => $user['role_name'],
                'city' => $user['city'],
                'email' => $user['email'],
                'telegram_id' => $user['telegram_id']
            ];
            
            return $this->success($userData, 'Профиль получен');
            
        } catch (Exception $e) {
            return $this->error('Ошибка получения профиля', 500, 'DATABASE_ERROR');
        }
    }
}

// Запускаем обработку
$endpoint = new ProfileEndpoint();
$endpoint->handle();
?> 