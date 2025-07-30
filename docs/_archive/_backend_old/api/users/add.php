<?php
/**
 * API Endpoint: Добавление нового пользователя
 * POST /api/users/add.php
 * 
 * Пример запроса:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "telegram_requestor_profile": {
 *       "telegram_id": 287536885,
 *       "username": "lex",
 *       "first_name": "Lex",
 *       "last_name": "Smith",
 *       "telegram_photo_id": "AgACAgIAAxkBAA...",
 *       "language_code": "ru"
 *     }
 *   }
 * }
 * 
 * Пример успешного ответа:
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
 *
 * Пример ответа с ошибкой (если не передан telegram_id):
 * {
 *   "success": false,
 *   "error": {
 *     "code": 400,
 *     "type": "VALIDATION_ERROR",
 *     "message": "telegram_requestor_profile.telegram_id обязателен",
 *     "details": {
 *       "field": "telegram_requestor_profile.telegram_id",
 *       "rule": "required"
 *     }
 *   }
 * }
 *
 * Все поля telegram_requestor_profile берутся из Telegram-аккаунта пользователя.
 * Если есть вопросы — см. документацию API и Telegram Bot API.
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
        
        // Получаем профиль Telegram из data.telegram_requestor_profile
        $telegramProfile = $this->getData('telegram_requestor_profile');
        if (!$telegramProfile || !isset($telegramProfile['telegram_id'])) {
            return $this->error('telegram_requestor_profile.telegram_id обязателен', 400, 'VALIDATION_ERROR', [
                'field' => 'telegram_requestor_profile.telegram_id',
                'rule' => 'required'
            ]);
        }
        $telegramId = $telegramProfile['telegram_id'];
        $username = $telegramProfile['username'] ?? null;
        $firstName = $telegramProfile['first_name'] ?? null;
        $lastName = $telegramProfile['last_name'] ?? null;
        $telegramPhotoId = $telegramProfile['telegram_photo_id'] ?? null;
        $languageCode = $telegramProfile['language_code'] ?? null; // если потребуется
        
        // Валидируем telegram_id
        if (!is_numeric($telegramId) || $telegramId <= 0) {
            return $this->error('Неверный формат Telegram ID', 400, 'VALIDATION_ERROR', [
                'field' => 'telegram_id',
                'rule' => 'numeric_positive'
            ]);
        }
        
        // Проверяем, состоит ли пользователь в клубном чате (если передан флаг is_member)
        $isMember = $this->getData('is_member', true); // по умолчанию считаем, что член
        
        try {
            $db = $this->getDb();
            
            // Проверяем, существует ли пользователь с таким telegram_id
            $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegramId]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingUser) {
                // --- Обновление существующего пользователя ---
                $fieldsToUpdate = [];
                // Сравниваем и обновляем только изменившиеся поля
                if ($username !== null && $username !== $existingUser['username']) {
                    $fieldsToUpdate['username'] = $username;
                }
                if ($firstName !== null && $firstName !== $existingUser['first_name_tg']) {
                    $fieldsToUpdate['first_name_tg'] = $firstName;
                }
                if ($lastName !== null && $lastName !== $existingUser['last_name_tg']) {
                    $fieldsToUpdate['last_name_tg'] = $lastName;
                }
                if ($telegramPhotoId !== null && $telegramPhotoId !== $existingUser['telegram_photo_id']) {
                    $fieldsToUpdate['telegram_photo_id'] = $telegramPhotoId;
                }
                // Если есть что обновлять
                if (!empty($fieldsToUpdate)) {
                    $set = [];
                    $params = [];
                    foreach ($fieldsToUpdate as $k => $v) {
                        $set[] = "$k = ?";
                        $params[] = $v;
                    }
                    $set[] = "updated_at = NOW()";
                    $params[] = $existingUser['id'];
                    $sql = "UPDATE users SET ".implode(', ', $set)." WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                }
                // Если изменился file_id — скачиваем и сохраняем новое фото
                if (isset($fieldsToUpdate['telegram_photo_id']) && $telegramPhotoId) {
                    $this->saveTelegramAvatar($db, $existingUser['id'], $telegramPhotoId);
                }
                // Если не состоит в чате — роль всегда external
                if (!$isMember && $existingUser['role_id'] !== $this->getExternalRoleId($db)) {
                    $fieldsToUpdate['role_id'] = $this->getExternalRoleId($db);
                }
                return $this->success([
                    'user_id' => $existingUser['id'],
                    'telegram_id' => $telegramId,
                    'role_id' => $existingUser['role_id'],
                    'role' => $this->getRoleCodeById($db, $existingUser['role_id']),
                    'created' => false,
                    'updated_at' => date('Y-m-d H:i:s') // дата обновления профиля
                ], 'Данные пользователя обновлены');
            } else {
                // --- Создание нового пользователя ---
                $roleCode = $isMember ? 'guest' : 'external';
                $stmt = $db->prepare("SELECT id FROM ref_roles WHERE code = ?");
                $stmt->execute([$roleCode]);
                $role = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$role) {
                    throw new Exception('Роль ' . $roleCode . ' не найдена в справочнике');
                }
                $sql = "INSERT INTO users (
                    telegram_id, username, first_name_tg, last_name_tg, 
                    first_name_app, last_name_app, role_id, join_date, 
                    created_at, updated_at, telegram_photo_id
                ) VALUES (?, ?, ?, ?, NULL, NULL, ?, CURDATE(), NOW(), NOW(), ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $telegramId,
                    $username,
                    $firstName,
                    $lastName,
                    $role['id'],
                    $telegramPhotoId
                ]);
                $userId = $db->lastInsertId();
                // Если есть file_id — скачиваем и сохраняем фото
                if ($telegramPhotoId) {
                    $this->saveTelegramAvatar($db, $userId, $telegramPhotoId);
                }
                return $this->success([
                    'user_id' => $userId,
                    'telegram_id' => $telegramId,
                    'role_id' => $role['id'],
                    'role' => $roleCode,
                    'created' => true,
                    'updated_at' => date('Y-m-d H:i:s') // дата создания профиля
                ], 'Пользователь создан');
            }
            
        } catch (Exception $e) {
            return $this->error('Ошибка работы с пользователем: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
    
    /**
     * Создаёт нового пользователя
     */
    protected function createUser($db, $telegramId, $username, $firstName, $lastName, $photo, $telegramPhotoUrl = null) {
        // Получаем ID роли guest
        $stmt = $db->prepare("SELECT id FROM ref_roles WHERE code = 'guest'");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) {
            throw new Exception('Роль guest не найдена в справочнике');
        }
        // Вставляем все возможные поля из Telegram
        $sql = "INSERT INTO users (
            telegram_id, username, first_name_tg, last_name_tg, 
            first_name_app, last_name_app, role_id, join_date, 
            created_at, updated_at, telegram_photo_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), NOW(), NOW(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $telegramId,
            $username,
            $firstName,
            $lastName,
            $firstName, // Пока используем telegram данные как app данные
            $lastName,
            $role['id'],
            $telegramPhotoUrl
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
    protected function updateUser($db, $userId, $username, $firstName, $lastName, $photo, $telegramPhotoUrl = null) {
        // Обновляем данные пользователя, включая telegram_photo_url
        $sql = "UPDATE users SET 
            username = ?, 
            first_name_tg = ?, 
            last_name_tg = ?,
            telegram_photo_url = ?,
            updated_at = NOW()
            WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $firstName, $lastName, $telegramPhotoUrl, $userId]);
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

    /**
     * Сохраняет Telegram-аватар пользователя по file_id (реальное скачивание)
     */
    protected function saveTelegramAvatar($db, $userId, $fileId) {
        // Получаем токен бота из окружения
        $botToken = getenv('BOT_TOKEN') ?: ($_ENV['BOT_TOKEN'] ?? null);
        if (!$botToken) {
            // Не найден токен
            return;
        }
        // 1. Получаем file_path через Telegram API
        $apiUrl = "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}";
        $response = @file_get_contents($apiUrl);
        $data = json_decode($response, true);
        if (!$data || !$data['ok'] || !isset($data['result']['file_path'])) {
            return; // Не удалось получить путь к файлу
        }
        $filePathTg = $data['result']['file_path'];
        // 2. Скачиваем файл по url
        $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePathTg}";
        $imageData = @file_get_contents($fileUrl);
        if (!$imageData) {
            return; // Не удалось скачать файл
        }
        // 3. Сохраняем файл на сервере
        $ext = pathinfo($filePathTg, PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = 'tg_' . $userId . '_' . time() . '.' . $ext;
        $uploadPath = __DIR__ . '/../../../uploads/avatars/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $filePath = $uploadPath . $fileName;
        file_put_contents($filePath, $imageData);
        // 4. Сохраняем запись в photos
        $sql = "INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user',
            $userId,
            $fileName,
            'uploads/avatars/' . $fileName,
            'Telegram аватар',
            $userId
        ]);
    }

    /**
     * Получить id роли external
     */
    protected function getExternalRoleId($db) {
        $stmt = $db->prepare("SELECT id FROM ref_roles WHERE code = 'external'");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ? $role['id'] : null;
    }

    /**
     * Получить строковый код роли по её id
     */
    protected function getRoleCodeById($db, $roleId) {
        $stmt = $db->prepare('SELECT code FROM ref_roles WHERE id = ?');
        $stmt->execute([$roleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['code'] : null;
    }
}

// Запускаем обработку
$endpoint = new UserAddEndpoint();
$endpoint->handle();
?> 