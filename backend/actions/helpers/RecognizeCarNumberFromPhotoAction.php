<?php
/**
 * 🔍 RecognizeCarNumberFromPhotoAction - Распознавание номера автомобиля
 * 
 * Уровень: Utils (Внешние интеграции)
 * Назначение: Распознавание номера автомобиля из фотографии через OCR API
 * 
 * Входные данные:
 * - $photoFile (array) - Данные файла из $_FILES
 * 
 * Выходные данные:
 * - string - Распознанный номер автомобиля
 */

require_once __DIR__ . '/IntegrationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';

class RecognizeCarNumberFromPhotoAction {
    
    /**
     * 🔍 Обработка распознавания номера
     * 
     * @param array $photoFile - Данные файла из $_FILES
     * @return string - Распознанный номер автомобиля
     * @throws Exception - Если не удалось распознать номер
     */
    public static function handle($photoFile) {
        try {
            // Валидация входных данных
            if (!$photoFile || !isset($photoFile['tmp_name'])) {
                throw new Exception('Файл фотографии не предоставлен');
            }
            
            // Распознавание номера через IntegrationHelper
            $plateNumber = IntegrationHelper::recognizePlateNumber($photoFile);
            
            Logger::info("Plate number recognized successfully: $plateNumber");
            
            return $plateNumber;
            
        } catch (Exception $e) {
            Logger::error('RecognizeCarNumberFromPhotoAction failed: ' . $e->getMessage());
            throw new Exception('Не удалось распознать номер автомобиля: ' . $e->getMessage());
        }
    }
}

 