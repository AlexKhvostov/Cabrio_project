<?php
/**
 * API Endpoint: Создание визитки
 * POST /api/business-cards/add.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "reg_number": "A123BC",
 *     "photo": "base64_encoded_image",
 *     "location": "ул. Ленина, 1",
 *     "notes": "Красивый кабриолет"
 *   }
 * }
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Визитка успешно создана",
 *     "data": {
 *       "business_card_id": 456,
 *       "car_id": 789,
 *       "reg_number": "A123BC",
 *       "car_created": true
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

class AddBusinessCardEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (только member+)
        $accessResult = $this->checkAccess('member');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем данные
        $regNumber = $this->requireField('reg_number', 'Номер автомобиля обязателен');
        $regNumber = $this->validateRegNumber($regNumber);
        
        // Проверяем длину номера (минимум 6 символов для визитки)
        if (strlen($regNumber) < 6) {
            return $this->error('Для создания визитки номер должен содержать минимум 6 символов', 400, 'VALIDATION_ERROR', [
                'field' => 'reg_number',
                'rule' => 'min_length_6'
            ]);
        }
        
        $photo = $this->requireField('photo', 'Фото автомобиля обязательно');
        $photo = $this->validatePhoto($photo);
        
        $location = $this->getData('location', '');
        $notes = $this->getData('notes', '');
        
        $userId = $this->getAuth('user_id');
        
        try {
            $db = $this->getDb();
            
            // Ищем автомобиль в БД
            $carSql = "SELECT id FROM cars WHERE reg_number = ?";
            $carStmt = $db->prepare($carSql);
            $carStmt->execute([$regNumber]);
            $existingCar = $carStmt->fetch(PDO::FETCH_ASSOC);
            
            $carId = null;
            $carCreated = false;
            
            if ($existingCar) {
                // Автомобиль найден - используем существующий
                $carId = $existingCar['id'];
            } else {
                // Автомобиль не найден - создаём новый
                $carId = $this->createCar($db, $regNumber, $photo, $userId);
                $carCreated = true;
            }
            
            // Создаём визитку (в любом случае!)
            $cardSql = "INSERT INTO business_cards (
                car_id, location, notes, inviter_user_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $cardStmt = $db->prepare($cardSql);
            $cardStmt->execute([
                $carId,
                $location,
                $notes,
                $userId
            ]);
            
            $businessCardId = $db->lastInsertId();
            
            // Формируем ответ
            $result = [
                'business_card_id' => $businessCardId,
                'car_id' => $carId,
                'reg_number' => $regNumber,
                'car_created' => $carCreated,
                'location' => $location,
                'notes' => $notes,
                'inviter_user_id' => $userId
            ];
            
            $message = $carCreated 
                ? 'Визитка создана. Автомобиль добавлен в базу'
                : 'Визитка создана для существующего автомобиля';
            
            return $this->success($result, $message);
            
        } catch (Exception $e) {
            return $this->error('Ошибка создания визитки: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
    
    /**
     * Создаёт новый автомобиль
     */
    protected function createCar($db, $regNumber, $photo, $userId) {
        // Сохраняем фото
        $photoUrl = $this->savePhoto($photo, $userId);
        
        // Создаём запись в БД (без проверки уникальности для визиток)
        $sql = "INSERT INTO cars (
            reg_number, create_user_id, owner_user_id, status_id, created_at, updated_at
        ) VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $regNumber,
            $userId,
            null, // нет владельца
            3 // external (внешний)
        ]);
        
        $carId = $db->lastInsertId();
        
        // Сохраняем фото в таблицу photos
        $photoSql = "INSERT INTO photos (
            entity_type, entity_id, file_name, url, uploaded_at, uploaded_by
        ) VALUES (?, ?, ?, ?, NOW(), ?)";
        
        $photoStmt = $db->prepare($photoSql);
        $photoStmt->execute([
            'car',
            $carId,
            basename($photoUrl),
            $photoUrl,
            $userId
        ]);
        
        return $carId;
    }
    
    /**
     * Валидирует фото (base64)
     */
    protected function validatePhoto($photo) {
        // Проверяем, что это base64
        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $photo)) {
            $this->error('Неверный формат фото. Ожидается base64', 400, 'VALIDATION_ERROR', [
                'field' => 'photo',
                'rule' => 'base64_image'
            ]);
        }
        
        // Проверяем размер (максимум 5MB)
        $base64Data = substr($photo, strpos($photo, ',') + 1);
        $size = strlen($base64Data) * 0.75; // Примерный размер в байтах
        
        if ($size > 5 * 1024 * 1024) {
            $this->error('Фото слишком большое. Максимум 5MB', 400, 'VALIDATION_ERROR', [
                'field' => 'photo',
                'rule' => 'max_size'
            ]);
        }
        
        return $photo;
    }
    
    /**
     * Сохраняет фото
     */
    protected function savePhoto($base64Photo, $userId) {
        $uploadDir = __DIR__ . '/../../../uploads/cars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Извлекаем данные из base64
        $base64Data = substr($base64Photo, strpos($base64Photo, ',') + 1);
        $imageData = base64_decode($base64Data);
        
        // Определяем расширение
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        
        $extension = 'jpg';
        if ($mimeType === 'image/png') {
            $extension = 'png';
        }
        
        // Генерируем имя файла
        $filename = 'car_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Сохраняем файл
        if (file_put_contents($filepath, $imageData)) {
            return '/uploads/cars/' . $filename;
        }
        
        $this->error('Ошибка сохранения фото', 500, 'FILE_SAVE_ERROR');
    }
}

// Запускаем обработку
$endpoint = new AddBusinessCardEndpoint();
$endpoint->handle();
?> 