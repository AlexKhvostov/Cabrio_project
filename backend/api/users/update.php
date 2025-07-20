<?php
/**
 * API Endpoint: Обновление профиля пользователя
 * POST /api/users/update.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "user_id": 123,
 *     "first_name": "Lex",
 *     "last_name": "Smith",
 *     "photo": "base64_encoded_image"
 *   }
 * }
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Профиль обновлён",
 *     "data": {
 *       "user_id": 123,
 *       "updated_fields": ["first_name", "last_name", "photo"]
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

class UserUpdateEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (доступно всем авторизованным)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем user_id для обновления
        $targetUserId = $this->getData('user_id');
        $authUserId = $this->getAuth('user_id');
        
        // Если user_id не указан, обновляем свой профиль
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
        
        // Проверяем что пользователь обновляет свой профиль или имеет права admin
        $authRole = $this->getAuth('role');
        if ($targetUserId != $authUserId && $authRole !== 'admin') {
            return $this->error('Нет прав для обновления чужого профиля', 403, 'ACCESS_DENIED');
        }
        
        try {
            $db = $this->getDb();
            
            // Проверяем что пользователь существует
            $user = $this->getUserInfo($db, $targetUserId);
            if (!$user) {
                return $this->error('Пользователь не найден', 404, 'NOT_FOUND');
            }
            
            // Получаем данные для обновления
            $firstName = $this->getData('first_name');
            $lastName = $this->getData('last_name');
            $photo = $this->getData('photo');
            
            $updatedFields = [];
            
            // Обновляем данные пользователя
            if ($firstName !== null || $lastName !== null) {
                $this->updateUserData($db, $targetUserId, $firstName, $lastName);
                if ($firstName !== null) $updatedFields[] = 'first_name';
                if ($lastName !== null) $updatedFields[] = 'last_name';
            }
            
            // Сохраняем фото если передано
            if ($photo) {
                $this->saveUserPhoto($db, $targetUserId, $photo);
                $updatedFields[] = 'photo';
            }
            
            if (empty($updatedFields)) {
                return $this->error('Нет данных для обновления', 400, 'VALIDATION_ERROR');
            }
            
            $result = [
                'user_id' => $targetUserId,
                'updated_fields' => $updatedFields
            ];
            
            return $this->success($result, 'Профиль обновлён');
            
        } catch (Exception $e) {
            return $this->error('Ошибка обновления профиля: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
    
    /**
     * Получает информацию о пользователе
     */
    protected function getUserInfo($db, $userId) {
        $sql = "SELECT id FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Обновляет данные пользователя
     */
    protected function updateUserData($db, $userId, $firstName, $lastName) {
        $updates = [];
        $params = [];
        
        if ($firstName !== null) {
            $updates[] = "first_name_app = ?";
            $params[] = $firstName;
        }
        
        if ($lastName !== null) {
            $updates[] = "last_name_app = ?";
            $params[] = $lastName;
        }
        
        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    /**
     * Сохраняет фото пользователя
     */
    protected function saveUserPhoto($db, $userId, $base64Photo) {
        // Валидируем фото
        if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $base64Photo)) {
            return; // Пропускаем невалидное фото
        }
        
        // Сохраняем во временный файл
        $base64Data = substr($base64Photo, strpos($base64Photo, ',') + 1);
        $imageData = base64_decode($base64Data);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'user_photo_');
        file_put_contents($tempFile, $imageData);
        
        // Определяем расширение
        if (preg_match('/^data:image\/([^;]+);base64,/', $base64Photo, $matches)) {
            $format = $matches[1];
            $extension = $format === 'jpeg' ? 'jpg' : $format;
        } else {
            $extension = 'jpg'; // По умолчанию
        }
        
        // Создаём имя файла
        $fileName = 'user_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../../uploads/avatars/';
        
        // Создаём директорию если не существует
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filePath = $uploadPath . $fileName;
        
        // Перемещаем файл
        if (move_uploaded_file($tempFile, $filePath)) {
            // Сохраняем запись в БД
            $sql = "INSERT INTO photos (entity_type, entity_id, file_name, url, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute(['user', $userId, $fileName, 'uploads/avatars/' . $fileName, $userId]);
        }
        
        // Удаляем временный файл
        unlink($tempFile);
    }
}

// Запускаем обработку
$endpoint = new UserUpdateEndpoint();
$endpoint->handle();
?> 