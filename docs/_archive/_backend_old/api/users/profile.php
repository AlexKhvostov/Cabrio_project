<?php
/**
 * API Endpoint: Чтение профиля пользователя
 * POST /api/users/profile.php
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
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Профиль получен",
 *     "data": {
 *       "user": {
 *         "id": 456,
 *         "telegram_id": 287536885,
 *         "username": "lex",
 *         "first_name": "Lex",
 *         "last_name": "Smith",
 *         "role": "member",
 *         "join_date": "2024-01-15",
 *         "photo": "uploads/avatars/user_456_1234567890.jpg"
 *       },
 *       "cars": [
 *         {
 *           "id": 789,
 *           "reg_number": "А123БВ77",
 *           "brand": "Mazda",
 *           "model": "MX-5",
 *           "year": 2020,
 *           "color": "Красный",
 *           "status": "active"
 *         }
 *       ]
 *     }
 *   }
 * }
 */

// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

// Загружаем конфигурацию
require_once __DIR__ . '/../../config/config.php';

// Функция для получения значения из конфига (если не определена)
if (!function_exists('getConfig')) {
    function getConfig($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class UserProfileEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (доступно всем авторизованным)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        // Получаем user_id или telegram_id для просмотра профиля
        $targetUserId = $this->getData('user_id');
        $telegramId = $this->getData('telegram_id');
        $authUserId = $this->getAuth('user_id');
        // Если не указан user_id, но есть telegram_id — ищем по нему
        if (empty($targetUserId) && !empty($telegramId)) {
            $db = $this->getDb();
            $stmt = $db->prepare('SELECT id FROM users WHERE telegram_id = ?');
            $stmt->execute([$telegramId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['id'])) {
                $targetUserId = $row['id'];
            } else {
                return $this->error('Пользователь не найден', 404, 'NOT_FOUND');
            }
        }
        // Если user_id не указан, показываем свой профиль
        if (empty($targetUserId)) {
            $targetUserId = $authUserId;
        }
        // Проверяем что user_id указан
        if (empty($targetUserId)) {
            return $this->error('User ID обязателен', 400, 'VALIDATION_ERROR', [
                'field' => 'user_id',
                'rule' => 'required'
            ]);
        }
        try {
            $db = $this->getDb();
            // Получаем информацию о пользователе
            $user = $this->getUserInfo($db, $targetUserId);
            if (!$user) {
                return $this->error('Пользователь не найден', 404, 'NOT_FOUND');
            }
            
            // Получаем список машин пользователя
            $cars = $this->getUserCars($db, $targetUserId);
            
            // Получаем фото профиля
            $photo = $this->getUserPhoto($db, $targetUserId);
            if ($photo) {
                $user['photo'] = $photo;
            }
            
            $result = [
                'user' => $user,
                'cars' => $cars
            ];
            
            return $this->success($result, 'Профиль получен');
            
        } catch (Exception $e) {
            return $this->error('Ошибка получения профиля: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
    
    /**
     * Получает информацию о пользователе
     */
    protected function getUserInfo($db, $userId) {
        $sql = "
            SELECT 
                u.id,
                u.telegram_id,
                u.username,
                u.first_name_tg,
                u.last_name_tg,
                u.first_name_app,
                u.last_name_app,
                u.join_date,
                u.created_at,
                u.updated_at,
                u.telegram_photo_id,
                r.code as role
            FROM users u
            JOIN ref_roles r ON u.role_id = r.id
            WHERE u.id = ?
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return null;
        }
        
        // Формируем имя для отображения
        $user['first_name'] = $user['first_name_app'] ?: $user['first_name_tg'];
        $user['last_name'] = $user['last_name_app'] ?: $user['last_name_tg'];
        
        // Убираем лишние поля
        unset($user['first_name_tg'], $user['last_name_tg'], $user['first_name_app'], $user['last_name_app']);
        
        // telegram_photo_id и updated_at уже есть в $user
        return $user;
    }
    
    /**
     * Получает список машин пользователя
     */
    protected function getUserCars($db, $userId) {
        $sql = "
            SELECT 
                c.id,
                c.reg_number,
                c.car_brand_id,
                c.model,
                c.year,
                c.color,
                c.created_at,
                c.updated_at,
                b.brand,
                s.code as status
            FROM cars c
            LEFT JOIN ref_car_brands b ON c.car_brand_id = b.id
            LEFT JOIN ref_statuses s ON c.status_id = s.id
            WHERE c.owner_user_id = ?
            ORDER BY c.created_at DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Получает фото профиля пользователя
     */
    protected function getUserPhoto($db, $userId) {
        $sql = "
            SELECT url
            FROM photos
            WHERE entity_type = 'user' 
            AND entity_id = ?
            ORDER BY uploaded_at DESC
            LIMIT 1
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $photo ? $photo['url'] : null;
    }
}

// Запускаем обработку
$endpoint = new UserProfileEndpoint();
$endpoint->handle();
?> 