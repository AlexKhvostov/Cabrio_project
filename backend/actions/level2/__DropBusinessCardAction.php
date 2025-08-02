<?php
/**
 * __DropBusinessCardAction — L2 Action для добавления визитки в базу
 * 
 * Назначение: Добавляет визитку в базу с проверкой существования авто, и созданием авто если её нет с статусом визитка
 * Уровень: L2 (бизнес-операция)
 * Префикс: __ (два подчёркивания)
 * 
 * Логика работы:
 * 1. Проверяем существование автомобиля по номеру
 * 2. Если автомобиль найден:
 *    - Создаём визитку для существующего автомобиля
 *    - Возвращаем action: "card_created"
 * 3. Если автомобиль не найден:
 *    - Создаём новый автомобиль со статусом "визитка"
 *    - Создаём визитку для нового автомобиля
 *    - Возвращаем action: "car_and_card_created"
 * 4. Если передана фото:
 *    - Сохраняем файл на сервер
 *    - Создаём запись в БД через L1 Action
 * 
 * Входные данные:
 *   - plate_number (string) — номер автомобиля (обязательно)
 *   - user_id (int) — ID пользователя, оставившего визитку (обязательно)
 *   - car_id (int, опционально) — ID автомобиля (если известен)
 *   - model (string, опционально) — модель автомобиля
 *   - color (string, опционально) — цвет автомобиля
 *   - year (int, опционально) — год выпуска
 *   - photo (file, опционально) — фото автомобиля
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные визитки и автомобиля
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L1 Actions:
 *   - _CheckCarInDbAction — проверка существования автомобиля
 *   - _CreateCarAction — создание нового автомобиля
 *   - _UpdateStatusAction — обновление статуса автомобиля
 *   - _CreateBusinessCardAction — создание визитки
 *   - _CreatePhotoAction — создание записи о фото (если передана фото)
 * 
 * Использует Helpers:
 *   - FileHelper — сохранение файла на сервер
 */

require_once __DIR__ . '/../level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../level1/_CreateCarAction.php';
require_once __DIR__ . '/../level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../level1/_CreateBusinessCardAction.php';
require_once __DIR__ . '/../level1/_CreatePhotoAction.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';

class __DropBusinessCardAction {
    
    public static function handle($data) {
        try {
            // Валидация обязательных полей
            ValidationHelper::requireFields($data, ['plate_number', 'user_id']);
            
            // Валидация типов данных
            ValidationHelper::validateInt($data['user_id'], 'user_id');
            
            $plateNumber = $data['plate_number'];
            $userId = $data['user_id'];
            $action = null;
            $carData = null;
            $cardData = null;
            
            // 1. Проверяем существование автомобиля
            $checkResult = _CheckCarInDbAction::handle(['plate_number' => $plateNumber]);
            
            if ($checkResult['success'] && $checkResult['data'] !== null) {
                // Автомобиль найден
                $carData = $checkResult['data'];
                $carId = $carData['id'];
                $action = 'card_created';
                
            } else {
                // Автомобиль не найден - создаём новый со статусом "визитка"
                $createData = [
                    'reg_number' => $plateNumber,
                    'create_user_id' => $userId,
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'year' => $data['year'] ?? null,
                    'owner_user_id' => null, // Без владельца
                    'status_id' => 3 // "визитка" по умолчанию
                ];
                
                $createResult = _CreateCarAction::handle($createData);
                
                if ($createResult['success']) {
                    $carData = $createResult['data'];
                    $carId = $carData['id'];
                    $action = 'car_and_card_created';
                } else {
                    return $createResult;
                }
            }
            
            // 2. Создаём визитку
            $cardResult = _CreateBusinessCardAction::handle([
                'car_id' => $carId,
                'user_id' => $userId
            ]);
            
            if ($cardResult['success']) {
                $cardData = $cardResult['data'];
            } else {
                return $cardResult;
            }
            
            // 3. Обрабатываем фото если передана
            $photoData = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Сохраняем файл на сервер
                    $photoId = Photo::getNextId(); // Получаем следующий ID заранее
                    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $fileName = FileHelper::generateCorrectFileName('business_card', $cardData['id'], $photoId, $extension);
                    
                    // Сохраняем файл
                    $savedPath = FileHelper::savePhoto($_FILES['photo'], 'business_card', $cardData['id'], $photoId);
                    
                    // Создаём запись в БД
                    $photoResult = _CreatePhotoAction::handle([
                        'entity_type' => 'business_card',
                        'entity_id' => $cardData['id'],
                        'file_name' => $fileName,
                        'url' => $savedPath,
                        'photo_type' => 'cover',
                        'description' => 'Фото приглашенного автомобиля',
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
            
            // 4. Формируем ответ
            $response = [
                'success' => true,
                'data' => [
                    'action' => $action,
                    'car' => [
                        'car_id' => $carData['id'],
                        'plate_number' => $carData['reg_number'],
                        'model' => $carData['model'],
                        'color' => $carData['color'],
                        'year' => $carData['year'],
                        'status_id' => $carData['status_id'],
                        'owner_user_id' => $carData['owner_user_id'],
                        'create_user_id' => $carData['create_user_id'] ?? $userId
                    ],
                    'business_card' => [
                        'card_id' => $cardData['id'],
                        'car_id' => $cardData['car_id'],
                        'user_id' => $cardData['user_id'] ?? $userId,
                        'created_at' => $cardData['created_at']
                    ],
                    'message' => self::getActionMessage($action)
                ]
            ];
            
            // Добавляем информацию о фото если загружали
            if ($photoData) {
                $response['data']['photo'] = $photoData;
            }
            
            Logger::info("Business card dropped: plate_number=$plateNumber, user_id=$userId, action=$action");
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('__DropBusinessCardAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка добавления визитки: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Получить сообщение для действия
     */
    private static function getActionMessage($action) {
        switch ($action) {
            case 'card_created':
                return 'Визитка добавлена для существующего автомобиля';
            case 'car_and_card_created':
                return 'Автомобиль создан и визитка добавлена';
            default:
                return 'Операция выполнена';
        }
    }
} 