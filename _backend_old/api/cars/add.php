<?php
/**
 * API Endpoint: Добавление автомобиля
 * POST /api/cars/add.php
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
 *     "model": "MX-5",
 *     "year": 2020,
 *     "color": "Красный",
 *     "show_reg_number": true
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

class AddCarEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (только new+ для добавления авто)
        $accessResult = $this->checkAccess('new');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем данные
        $regNumber = $this->requireField('reg_number', 'Номер автомобиля обязателен');
        $regNumber = $this->validateRegNumber($regNumber);
        
        $photo = $this->requireField('photo', 'Фото автомобиля обязательно');
        $photo = $this->validatePhoto($photo);
        
        $model = $this->getData('model', '');
        $year = $this->getData('year');
        $color = $this->getData('color', '');
        $showRegNumber = $this->getData('show_reg_number', false);
        $statusCode = $this->getData('status_code');
        $noOwner = $this->getData('no_owner', false);
        
        $userId = $this->getAuth('user_id');
        
        try {
            $db = $this->getDb();
            
            // Проверяем уникальность номера
            $checkSql = "SELECT id FROM cars WHERE reg_number = ?";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$regNumber]);
            
            if ($checkStmt->rowCount() > 0) {
                return $this->error('Автомобиль с таким номером уже зарегистрирован', 422, 'DUPLICATE_ERROR');
            }
            
            // Сохраняем фото
            $photoUrl = $this->savePhoto($photo, $userId);

            // Определяем статус авто
            $statusId = null;
            if (!empty($statusCode)) {
                // Если передан status_code — ищем id статуса по коду
                $statusStmt = $db->prepare('SELECT id FROM ref_statuses WHERE code = ? AND entity_type = ?');
                $statusStmt->execute([$statusCode, 'car']);
                $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);
                if ($statusRow) {
                    $statusId = $statusRow['id'];
                }
            }
            if (empty($statusId)) {
                // По умолчанию — статус active
                $statusStmt = $db->prepare('SELECT id FROM ref_statuses WHERE code = ? AND entity_type = ?');
                $statusStmt->execute(['active', 'car']);
                $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);
                $statusId = $statusRow ? $statusRow['id'] : null;
            }
            
            // Создаём запись в БД
            $sql = "INSERT INTO cars (
                reg_number, model, year, color, show_reg_number,
                create_user_id, owner_user_id, status_id, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $db->prepare($sql);
            if ($noOwner) {
                // Если авто создаётся через визитку — владелец null, но создатель всегда userId
                $stmt->execute([
                    $regNumber,
                    $model,
                    $year,
                    $color,
                    $showRegNumber ? 1 : 0,
                    $userId, // create_user_id всегда userId
                    null,     // owner_user_id = null
                    $statusId
                ]);
            } else {
                $stmt->execute([
                    $regNumber,
                    $model,
                    $year,
                    $color,
                    $showRegNumber ? 1 : 0,
                    $userId,
                    $userId,
                    $statusId
                ]);
            }
            
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
            
            // Создаём связь пользователь-авто только если есть владелец
            if (!$noOwner) {
                $linkSql = "INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (?, ?, ?)";
                $linkStmt = $db->prepare($linkSql);
                $linkStmt->execute([$userId, $carId, 1]); // owner role
            }
            
            // Формируем ответ
            $carData = [
                'car_id' => $carId,
                'reg_number' => $regNumber,
                'model' => $model,
                'year' => $year,
                'color' => $color,
                'show_reg_number' => $showRegNumber,
                'photo_url' => $photoUrl,
                'owner_user_id' => $noOwner ? null : $userId // если авто без владельца — null
            ];
            
            return $this->success($carData, 'Автомобиль успешно добавлен');
            
        } catch (Exception $e) {
            return $this->error('Ошибка добавления автомобиля', 500, 'DATABASE_ERROR');
        }
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
$endpoint = new AddCarEndpoint();
$endpoint->handle();
?> 