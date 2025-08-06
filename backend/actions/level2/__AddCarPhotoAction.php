<?php
/**
 * __AddCarPhotoAction — L2 Action для сохранения фото к автомобилю
 * 
 * Назначение: Сохраняет фото к автомобилю (файл + запись в БД)
 * Уровень: L2 (бизнес-операция)
 * Префикс: __ (два подчёркивания)
 * 
 * Логика работы:
 * 1. Проверяем наличие фото (base64 или файл)
 * 2. Если фото есть - сохраняем файл на сервер
 * 3. Создаём запись в БД через _CreatePhotoAction
 * 4. Возвращаем данные сохраненного фото
 * 
 * Входные данные:
 *   - car_id (int) — ID автомобиля (обязательно)
 *   - user_id (int) — ID пользователя, загружающего фото (обязательно)
 *   - photo (string, опционально) — фото в формате base64
 *   - photo_file (file, опционально) — фото как файл из $_FILES
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array, опционально) — данные сохраненного фото
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L1 Actions:
 *   - _CreatePhotoAction — создание записи о фото в БД
 * 
 * Использует Helpers:
 *   - FileHelper — сохранение файла на сервер
 */

require_once __DIR__ . '/../level1/_CreatePhotoAction.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../models/Photo.php';

class __AddCarPhotoAction {
    
    public static function handle($data) {
        try {
            $carId = $data['car_id'] ?? null;
            $userId = $data['user_id'] ?? null;
            
            // Проверяем обязательные параметры
            if (!$carId) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'CAR_ID_REQUIRED',
                        'message' => 'ID автомобиля обязателен'
                    ]
                ];
            }
            
            if (!$userId) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'USER_ID_REQUIRED',
                        'message' => 'ID пользователя обязателен'
                    ]
                ];
            }
            
            Logger::info('L2 Action: Starting car photo save', [
                'car_id' => $carId,
                'user_id' => $userId
            ]);
            
            // Проверяем наличие фото (base64 или файл)
            $hasBase64Photo = isset($data['photo']) && !empty($data['photo']);
            $hasFilePhoto = isset($data['photo_file']) && $data['photo_file']['error'] === UPLOAD_ERR_OK;
            
            if (!$hasBase64Photo && !$hasFilePhoto) {
                Logger::info("No photo provided for car_id=$carId");
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'PHOTO_REQUIRED',
                        'message' => 'Фото обязательно для сохранения'
                    ]
                ];
            }
            
            // Получаем следующий ID для фото
            $photoId = Photo::getNextId();
            $extension = 'jpg';
            $fileName = FileHelper::generateCorrectFileName('car', $carId, $photoId, $extension);
            
            // Сохраняем фото на сервер
            if ($hasBase64Photo) {
                $savedPath = FileHelper::savePhotoFromBase64($data['photo'], 'car', $carId, $photoId, 'car_photo.jpg');
            } else {
                $savedPath = FileHelper::savePhoto($data['photo_file'], 'car', $carId, $photoId);
            }
            
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
                Logger::info("Car photo saved successfully: car_id=$carId, photo_id=" . $photoResult['data']['id']);
                
                return [
                    'success' => true,
                    'data' => $photoResult['data']
                ];
            } else {
                Logger::error("Failed to create car photo record: " . json_encode($photoResult['error']));
                
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'PHOTO_SAVE_FAILED',
                        'message' => 'Ошибка сохранения фото: ' . ($photoResult['error']['message'] ?? 'Неизвестная ошибка')
                    ]
                ];
            }
            
        } catch (Exception $e) {
            Logger::error('__AddCarPhotoAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка сохранения фото к автомобилю: ' . $e->getMessage()
                ]
            ];
        }
    }
} 