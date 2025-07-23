<?php
/**
 * API Endpoint: Добавление нового пользователя
 * POST /api/users/add.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "telegram_id": 287536885,
 *     "username": "lex",
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
 *     "message": "Пользователь создан",
 *     "data": {
 *       "user_id": 456,
 *       "telegram_id": 287536885,
 *       "role": "guest",
 *       "created": true
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

class UserAddEndpoint extends ApiHandler {
    
    /**
     * Переопределяем валидацию auth для создания нового пользователя
     */
    protected function validateAuth() {
        // Для создания нового пользователя auth может быть пустым
        return true;
    }
    
    protected function process() {
        // Проверяем права доступа (доступно всем)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем данные
        $telegramId = $this->requireField('telegram_id', 'Telegram ID обязателен');
        $username = $this->getData('username');
        $firstName = $this->getData('first_name');
        $lastName = $this->getData('last_name');
        $photo = $this->getData('photo');
        
        // Валидируем telegram_id
        if (!is_numeric($telegramId) || $telegramId <= 0) {
            return $this->error('Неверный формат Telegram ID', 400, 'VALIDATION_ERROR', [
                'field' => 'telegram_id',
                'rule' => 'numeric_positive'
            ]);
        }
        
        try {
            $db = $this->getDb();
            
            // Проверяем, существует ли пользователь с таким telegram_id
            $stmt = $db->prepare("SELECT id, role_id FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegramId]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                // Пользователь уже существует - обновляем данные
                $result = $this->updateUser($db, $existingUser['id'], $username, $firstName, $lastName, $photo);
                return $this->success($result, 'Данные пользователя обновлены');
            } else {
                // Пользователь не существует - создаём нового
                $result = $this->createUser($db, $telegramId, $username, $firstName, $lastName, $photo);
                return $this->success($result, 'Пользователь создан');
            }
            
        } catch (Exception $e) {
            return $this->error('Ошибка работы с пользователем: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
    
    /**
     * Создаёт нового пользователя
     */
    protected function createUser($db, $telegramId, $username, $firstName, $lastName, $photo) {
        // Получаем ID роли guest
        $stmt = $db->prepare("SELECT id FROM ref_roles WHERE code = 'guest'");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            throw new Exception('Роль guest не найдена в справочнике');
        }
        
        // Создаём пользователя
        $sql = "INSERT INTO users (
            telegram_id, username, first_name_tg, last_name_tg, 
            first_name_app, last_name_app, role_id, join_date, 
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $telegramId,
            $username,
            $firstName,
            $lastName,
            $firstName, // Пока используем telegram данные как app данные
            $lastName,
            $role['id']
        ]);
        
        $userId = $db->lastInsertId();
        
        // Сохраняем фото если передано
        if ($photo) {
            $this->saveUserPhoto($db, $userId, $photo);
        }
        
        return [
            'user_id' => $userId,
            'telegram_id' => $telegramId,
            'role' => 'guest',
            'created' => true
        ];
    }
    
    /**
     * Обновляет существующего пользователя
     */
    protected function updateUser($db, $userId, $username, $firstName, $lastName, $photo) {
        // Обновляем данные пользователя
        $sql = "UPDATE users SET 
            username = ?, 
            first_name_tg = ?, 
            last_name_tg = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $firstName, $lastName, $userId]);
        
        // Сохраняем фото если передано
        if ($photo) {
            $this->saveUserPhoto($db, $userId, $photo);
        }
        
        // Получаем роль пользователя
        $stmt = $db->prepare("
            SELECT r.code as role 
            FROM users u 
            JOIN ref_roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'user_id' => $userId,
            'telegram_id' => $this->getData('telegram_id'),
            'role' => $user['role'],
            'created' => false
        ];
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
        if (rename($tempFile, $filePath)) {
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
$endpoint = new UserAddEndpoint();
$endpoint->handle();
?> 