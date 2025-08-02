<?php
/**
 * ___AddCarToGarageAction — L3 Action для добавления автомобиля в гараж
 * 
 * Назначение: Полный сценарий добавления авто в гараж с OCR распознаванием номера
 * Уровень: L3 (полный сценарий)
 * Префикс: ___ (три подчёркивания)
 * 
 * Логика работы:
 * 1. Получаем фото автомобиля (обязательно)
 * 2. OCR распознавание номера через platerecognizer.com
 * 3. Вызываем __AddCarToUserAction с распознанным номером
 * 4. Возвращаем информацию о добавленном в гараж авто
 * 
 * Входные данные:
 *   - photo (file) — фото автомобиля (обязательно)
 *   - user_id (int) — ID пользователя, добавляющего авто в гараж (обязательно)
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные автомобиля и информация о действии
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L2 Actions:
 *   - __AddCarToUserAction — добавление автомобиля пользователю
 * 
 * Использует Helpers:
 *   - RecognizeCarNumberFromPhotoAction — OCR распознавание номера
 */
require_once __DIR__ . '/../level2/__AddCarToUserAction.php';
require_once __DIR__ . '/../helpers/RecognizeCarNumberFromPhotoAction.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';

class ___AddCarToGarageAction {
    
    public static function handle($data) {
        try {
            // Валидация обязательных полей
            ValidationHelper::requireFields($data, ['user_id']);
            ValidationHelper::validateInt($data['user_id'], 'user_id');
            
            // Проверяем наличие фото
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'PHOTO_REQUIRED',
                        'message' => 'Фото автомобиля обязательно для добавления в гараж'
                    ]
                ];
            }
            
            $userId = $data['user_id'];
            
            // 1. OCR распознавание номера
            try {
                $plateNumber = RecognizeCarNumberFromPhotoAction::handle($_FILES['photo']);
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'OCR_FAILED',
                        'message' => 'Не удалось распознать номер автомобиля: ' . $e->getMessage()
                    ]
                ];
            }
            
            // 2. Добавление автомобиля в гараж
            // Передаем фото в $_FILES для L2 Action ПЕРЕД вызовом
            $_FILES['photo'] = $_FILES['photo'];
            
            $addResult = __AddCarToUserAction::handle([
                'plate_number' => $plateNumber,
                'user_id' => $userId
            ]);
            
            if (!$addResult['success']) {
                return $addResult;
            }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => [
                    'action' => $addResult['data']['action'],
                    'plate_number' => $plateNumber,
                    'car_id' => $addResult['data']['car_id'],
                    'model' => $addResult['data']['model'],
                    'color' => $addResult['data']['color'],
                    'year' => $addResult['data']['year'],
                    'status_id' => $addResult['data']['status_id'],
                    'owner_user_id' => $addResult['data']['owner_user_id'],
                    'create_user_id' => $addResult['data']['create_user_id'],
                    'message' => self::getActionMessage($addResult['data']['action'])
                ]
            ];
            
            // Добавляем информацию о фото если есть
            if (isset($addResult['data']['photo'])) {
                $response['data']['photo'] = $addResult['data']['photo'];
            }
            
            Logger::info("Car added to garage: plate_number=$plateNumber, user_id=$userId, action=" . $addResult['data']['action']);
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('___AddCarToGarageAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка добавления автомобиля в гараж: ' . $e->getMessage()
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
                return 'Автомобиль назначен вам и добавлен в гараж';
            case 'created':
                return 'Автомобиль создан и добавлен в ваш гараж';
            default:
                return 'Автомобиль добавлен в гараж';
        }
    }
} 