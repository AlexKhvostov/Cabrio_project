<?php
/**
 * __AddCarToUserAction — L2 Action для добавления автомобиля пользователю
 * 
 * Назначение: Добавляет автомобиль пользователю (если авто был в БД и без владельца то ставит владельца, если нет в БД то создает)
 * Уровень: L2 (бизнес-операция)
 * Префикс: __ (два подчёркивания)
 * 
 * Логика работы:
 * 1. Проверяем существование автомобиля по номеру
 * 2. Если автомобиль найден:
 *    - Проверяем есть ли у него владелец
 *    - Если владельца нет - назначаем пользователя владельцем
 *    - Если владелец есть - возвращаем ошибку
 *    - Возвращаем action: "assigned"
 * 3. Если автомобиль не найден:
 *    - Создаём новый автомобиль с пользователем как владельцем
 *    - Возвращаем action: "created"
 * 4. Если передана фото:
 *    - Сохраняем файл на сервер
 *    - Создаём запись в БД через L1 Action
 * 
 * Входные данные:
 *   - plate_number (string) — номер автомобиля (обязательно)
 *   - model (string, опционально) — модель автомобиля
 *   - color (string, опционально) — цвет автомобиля
 *   - year (int, опционально) — год выпуска
 *   - photo (file, опционально) — фото автомобиля
 * 
 * Пользователь получается из глобального контекста (AppContext)
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные автомобиля и информация о действии
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L1 Actions:
 *   - _CheckCarInDbAction — проверка существования автомобиля
 *   - _CreateCarAction — создание нового автомобиля
 *   - _UpdateOwnerToCarAction — назначение владельца автомобилю
 *   - _CreatePhotoAction — создание записи о фото (если передана фото)
 * 
 * Использует Helpers:
 *   - FileHelper — сохранение файла на сервер
 */

require_once __DIR__ . '/../level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../level1/_CreateCarAction.php';
require_once __DIR__ . '/../level1/_UpdateOwnerToCarAction.php';
require_once __DIR__ . '/../level1/_CreatePhotoAction.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/AppContext.php';

class __AddCarToUserAction {
    
    public static function handle($data) {
        try {
            // Получаем пользователя из глобального контекста
            $user = AppContext::getCurrentUser();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_USER',
                        'message' => 'Пользователь не найден в контексте'
                    ]
                ];
            }
            
            $userId = $user['id'];
            
            // Валидация обязательных полей
            ValidationHelper::requireFields($data, ['plate_number']);
            
            $plateNumber = $data['plate_number'];
            $action = null;
            $carData = null;
            
            // 1. Проверяем существование автомобиля
            $checkResult = _CheckCarInDbAction::handle(['plate_number' => $plateNumber]);
            
            if ($checkResult['success'] && $checkResult['data'] !== null) {
                // Автомобиль найден
                $carData = $checkResult['data'];
                $carId = $carData['id'];
                
                // Проверяем есть ли владелец
                if ($carData['owner_user_id'] !== null) {
                    return [
                        'success' => false,
                        'error' => [
                            'code' => 'CAR_ALREADY_OWNED',
                            'message' => 'Автомобиль уже имеет владельца'
                        ]
                    ];
                }
                
                // Назначаем пользователя владельцем
                $updateResult = _UpdateOwnerToCarAction::handle([
                    'car_id' => $carId,
                    'user_id' => $userId
                ]);
                
                if ($updateResult['success']) {
                    $action = 'assigned';
                    // Получаем обновленные данные автомобиля
                    $carData = $updateResult['data'];
                } else {
                    return $updateResult;
                }
                
            } else {
                // Автомобиль не найден - создаём новый с владельцем
                $createData = [
                    'reg_number' => $plateNumber,
                    'create_user_id' => $userId,
                    'owner_user_id' => $userId, // Пользователь как владелец
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'year' => $data['year'] ?? null,
                    'status_id' => 2 // "в гараже" по умолчанию
                ];
                
                $createResult = _CreateCarAction::handle($createData);
                
                if ($createResult['success']) {
                    $action = 'created';
                    $carData = $createResult['data'];
                    $carId = $carData['id'];
                } else {
                    return $createResult;
                }
            }
            
            // 2. Обрабатываем фото если передана
            $photoData = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Сохраняем файл на сервер
                    $photoId = Photo::getNextId(); // Получаем следующий ID заранее
                    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $fileName = FileHelper::generateCorrectFileName('car', $carId, $photoId, $extension);
                    
                    // Сохраняем файл
                    $savedPath = FileHelper::savePhoto($_FILES['photo'], 'car', $carId, $photoId);
                    
                    // Создаём запись в БД
                    $photoResult = _CreatePhotoAction::handle([
                        'entity_type' => 'car',
                        'entity_id' => $carId,
                        'file_name' => $fileName,
                        'url' => $savedPath,
                        'photo_type' => 'cover',
                        'description' => 'Фото автомобиля',
                        'uploaded_by' => $userId
                    ]);
                    
                    if ($photoResult['success']) {
                        $photoData = $photoResult['data'];
                    }
                    
                } catch (Exception $e) {
                    Logger::error('Photo upload failed: ' . $e->getMessage());
                    // Не прерываем выполнение, только логируем ошибку
                }
            }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => [
                    'car_id' => $carData['id'],
                    'action' => $action,
                    'plate_number' => $carData['reg_number'],
                    'model' => $carData['model'],
                    'color' => $carData['color'],
                    'year' => $carData['year'],
                    'status_id' => $carData['status_id'],
                    'owner_user_id' => $carData['owner_user_id'],
                    'create_user_id' => $carData['create_user_id'] ?? $userId,
                    'message' => self::getActionMessage($action)
                ]
            ];
            
            // Добавляем информацию о фото если загружали
            if ($photoData) {
                $response['data']['photo'] = $photoData;
            }
            
            Logger::info("Car added to user: plate_number=$plateNumber, user_id=$userId, action=$action");
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('__AddCarToUserAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка добавления автомобиля пользователю: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Получить сообщение для действия
     */
    private static function getActionMessage($action) {
        switch ($action) {
            case 'assigned':
                return 'Автомобиль назначен пользователю';
            case 'created':
                return 'Автомобиль создан и назначен пользователю';
            default:
                return 'Операция выполнена';
        }
    }
} 