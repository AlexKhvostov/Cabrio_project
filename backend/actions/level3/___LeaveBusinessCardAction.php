<?php
/**
 * ___LeaveBusinessCardAction — L3 Action для оставления визитки
 * 
 * Назначение: Полный сценарий оставления визитки с OCR распознаванием номера
 * Уровень: L3 (полный сценарий)
 * Префикс: ___ (три подчёркивания)
 * 
 * Логика работы:
 * 1. Получаем фото автомобиля (обязательно)
 * 2. OCR распознавание номера через platerecognizer.com
 * 3. Вызываем __DropBusinessCardAction с распознанным номером
 * 4. Возвращаем информацию о созданной визитке
 * 
 * Входные данные:
 *   - photo (file) — фото автомобиля (обязательно)
 *   - user_id (int) — ID пользователя, оставляющего визитку (обязательно)
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные визитки и автомобиля
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L2 Actions:
 *   - __DropBusinessCardAction — создание визитки
 * 
 * Использует Helpers:
 *   - RecognizeCarNumberFromPhotoAction — OCR распознавание номера
 */
require_once __DIR__ . '/../level2/__DropBusinessCardAction.php';
require_once __DIR__ . '/../helpers/RecognizeCarNumberFromPhotoAction.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';

class ___LeaveBusinessCardAction {
    
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
                        'message' => 'Фото автомобиля обязательно для оставления визитки'
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
            
            // 2. Создание визитки
            // Передаем фото в $_FILES для L2 Action ПЕРЕД вызовом
            $_FILES['photo'] = $_FILES['photo'];
            
            $cardResult = __DropBusinessCardAction::handle([
                'plate_number' => $plateNumber,
                'user_id' => $userId
            ]);
            
            if (!$cardResult['success']) {
                return $cardResult;
            }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => [
                    'action' => $cardResult['data']['action'],
                    'plate_number' => $plateNumber,
                    'car' => $cardResult['data']['car'],
                    'business_card' => $cardResult['data']['business_card'],
                    'message' => self::getActionMessage($cardResult['data']['action'])
                ]
            ];
            
            // Добавляем информацию о фото если есть
            if (isset($cardResult['data']['photo'])) {
                $response['data']['photo'] = $cardResult['data']['photo'];
            }
            
            Logger::info("Business card left: plate_number=$plateNumber, user_id=$userId, action=" . $cardResult['data']['action']);
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('___LeaveBusinessCardAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка оставления визитки: ' . $e->getMessage()
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
                return 'Визитка оставлена для существующего автомобиля';
            case 'car_and_card_created':
                return 'Автомобиль создан и визитка оставлена';
            default:
                return 'Визитка оставлена';
        }
    }
} 