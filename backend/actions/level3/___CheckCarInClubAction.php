<?php
/**
 * ___CheckCarInClubAction — L3 Action для проверки автомобиля в клубе
 * 
 * Назначение: Полный сценарий проверки авто в клубе с OCR распознаванием номера
 * Уровень: L3 (полный сценарий)
 * Префикс: ___ (три подчёркивания)
 * 
 * Логика работы:
 * 1. Получаем фото автомобиля (обязательно)
 * 2. OCR распознавание номера через platerecognizer.com
 * 3. Вызываем __SearchCarAction с распознанным номером
 * 4. Возвращаем полную информацию об авто в клубе
 * 
 * Входные данные:
 *   - photo (file) — фото автомобиля (обязательно)
 *   - user_id (int) — ID пользователя, выполняющего проверку (обязательно)
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные автомобиля и информация о действии
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L2 Actions:
 *   - __SearchCarAction — поиск/создание автомобиля
 * 
 * Использует Helpers:
 *   - RecognizeCarNumberFromPhotoAction — OCR распознавание номера
 */
require_once __DIR__ . '/../level2/__SearchCarAction.php';
require_once __DIR__ . '/../helpers/RecognizeCarNumberFromPhotoAction.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/AppContext.php';

class ___CheckCarInClubAction {
    
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
                        'message' => 'Фото автомобиля обязательно для проверки'
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
            
            // 2. Поиск автомобиля в клубе
            // Если есть base64 данные, создаем временный файл для L2 Action
            if (isset($data['photo']) && !empty($data['photo'])) {
                try {
                    require_once __DIR__ . '/../helpers/FileHelper.php';
                    $_FILES['photo'] = FileHelper::createTempFileFromBase64($data['photo'], 'car_photo.jpg');
                } catch (Exception $e) {
                    Logger::error('Failed to create temp file from base64: ' . $e->getMessage());
                    // Продолжаем без фото
                }
            }
            
            $searchResult = __SearchCarAction::handle([
                'plate_number' => $plateNumber
            ]);
            
            if (!$searchResult['success']) {
                return $searchResult;
            }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => array_merge($searchResult['data'], [ // Используем развернутые данные из L2 Action
                    'plate_number' => $plateNumber,
                    'message' => self::getActionMessage($searchResult['data']['action'])
                ])
            ];
            
            Logger::info("Car check in club completed: plate_number=$plateNumber, user_id=$userId, action=" . $searchResult['data']['action']);
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('___CheckCarInClubAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка проверки автомобиля в клубе: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Получить сообщение для действия
     */
    private static function getActionMessage($action) {
        switch ($action) {
            case 'found':
                return 'Автомобиль найден в клубе';
            case 'created':
                return 'Автомобиль добавлен в клуб';
            default:
                return 'Проверка выполнена';
        }
    }
} 