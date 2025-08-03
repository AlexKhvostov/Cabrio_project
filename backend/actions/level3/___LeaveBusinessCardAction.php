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
require_once __DIR__ . '/../../utils/AppContext.php';

class ___LeaveBusinessCardAction {
    
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
            
            // Проверяем наличие фото (base64 или файл)
            if ((!isset($data['photo']) || empty($data['photo'])) && 
                (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK)) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'PHOTO_REQUIRED',
                        'message' => 'Фото автомобиля обязательно для оставления визитки'
                    ]
                ];
            }
            
            // 1. OCR распознавание номера
            try {
                // Проверяем, есть ли base64 данные в data
                if (isset($data['photo']) && !empty($data['photo'])) {
                    // Используем base64 данные
                    $plateNumber = RecognizeCarNumberFromPhotoAction::handleFromBase64($data['photo']);
                } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    // Используем файл из $_FILES
                    $plateNumber = RecognizeCarNumberFromPhotoAction::handle($_FILES['photo']);
                } else {
                    throw new Exception('Фото не предоставлено ни в base64, ни в файле');
                }
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
            // Если есть base64 данные, создаем временный файл для L2 Action
            if (isset($data['photo']) && !empty($data['photo'])) {
                try {
                    require_once __DIR__ . '/../helpers/FileHelper.php';
                    $_FILES['photo'] = FileHelper::createTempFileFromBase64($data['photo'], 'business_card_photo.jpg');
                } catch (Exception $e) {
                    Logger::error('Failed to create temp file from base64: ' . $e->getMessage());
                    // Продолжаем без фото
                }
            }
            
            $cardResult = __DropBusinessCardAction::handle([
                'plate_number' => $plateNumber
            ]);
            
            if (!$cardResult['success']) {
                return $cardResult;
            }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => array_merge($cardResult['data'], [ // Используем развернутые данные из L2 Action
                    'plate_number' => $plateNumber,
                    'message' => self::getActionMessage($cardResult['data']['action'])
                ])
            ];
            
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