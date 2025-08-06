<?php
/**
 * 🔧 FileHelper - Вспомогательные функции для работы с файлами
 * 
 * Назначение: Сохранение, валидация и обработка файлов для Actions
 * Используется в: L1, L2, L3 Actions для работы с фотографиями
 */

require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/ValidationException.php';
require_once __DIR__ . '/../../utils/Logger.php';

class FileHelper {
    
    /**
     * 📸 Сохранение фотографии в папку uploads
     * 
     * @param array $fileData - Данные файла из $_FILES
     * @param string $entityType - Тип сущности (cars, users, events, etc.)
     * @param int $entityId - ID сущности
     * @param int $photoId - ID записи в таблице photos
     * @return string - Относительный путь к сохраненному файлу
     * @throws Exception - Если не удалось сохранить файл
     */
    public static function savePhoto($fileData, $entityType, $entityId, $photoId) {
        try {
            // Валидация файла
            self::validatePhotoFile($fileData);
            
            // Создание директории если не существует
            $uploadDir = self::getUploadDir($entityType);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Генерация имени файла с photoId
            $fileName = self::generateFileName($fileData, $entityType, $entityId, $photoId);
            $filePath = $uploadDir . '/' . $fileName;
            
            // Сохранение файла
            if (is_uploaded_file($fileData['tmp_name'])) {
                // Для загруженных файлов используем move_uploaded_file
                if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                    throw new Exception('Не удалось сохранить файл: ' . $fileData['name']);
                }
            } else {
                // Для временных файлов используем copy
                if (!copy($fileData['tmp_name'], $filePath)) {
                    throw new Exception('Не удалось сохранить файл: ' . $fileData['name']);
                }
            }
            
            // Логирование
            Logger::info("Photo saved: $filePath");
            
            // Возврат относительного пути для БД
            return self::getRelativePath($filePath);
            
        } catch (Exception $e) {
            Logger::error('FileHelper::savePhoto failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 📸 Сохранение фотографии из base64 данных
     * 
     * @param string $base64Data - Base64 кодированное изображение
     * @param string $entityType - Тип сущности (cars, users, events, etc.)
     * @param int $entityId - ID сущности
     * @param int $photoId - ID записи в таблице photos
     * @param string $originalName - Оригинальное имя файла
     * @return string - Относительный путь к сохраненному файлу
     * @throws Exception - Если не удалось сохранить файл
     */
    public static function savePhotoFromBase64($base64Data, $entityType, $entityId, $photoId, $originalName = 'photo.jpg') {
        try {
            // Создаем временный файл из base64
            $tempFileData = self::createTempFileFromBase64($base64Data, $originalName);
            
            // Создание директории если не существует
            $uploadDir = self::getUploadDir($entityType);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Генерация имени файла с photoId
            $fileName = self::generateFileName($tempFileData, $entityType, $entityId, $photoId);
            $filePath = $uploadDir . '/' . $fileName;
            
            // Сохранение файла
            if (!copy($tempFileData['tmp_name'], $filePath)) {
                throw new Exception('Не удалось сохранить файл: ' . $tempFileData['name']);
            }
            
            // Удаляем временный файл
            if (file_exists($tempFileData['tmp_name'])) {
                unlink($tempFileData['tmp_name']);
            }
            
            // Логирование
            Logger::info("Photo saved from base64: $filePath");
            
            // Возврат относительного пути для БД
            return self::getRelativePath($filePath);
            
        } catch (Exception $e) {
            Logger::error('FileHelper::savePhotoFromBase64 failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * ✅ Валидация фотографии
     * 
     * @param array $fileData - Данные файла из $_FILES
     * @throws ValidationException - Если файл не прошел валидацию
     */
    public static function validatePhotoFile($fileData) {
        // Проверка наличия файла
        if (!$fileData || !isset($fileData['tmp_name'])) {
            throw new ValidationException('Файл не был загружен');
        }
        
        // Проверяем, является ли это загруженным файлом или временным файлом
        if (!is_uploaded_file($fileData['tmp_name']) && !file_exists($fileData['tmp_name'])) {
            throw new ValidationException('Файл не был загружен');
        }
        
        // Проверка размера файла (максимум 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($fileData['size'] > $maxSize) {
            throw new ValidationException('Размер файла превышает 10MB');
        }
        
        // Проверка типа файла
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new ValidationException('Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF');
        }
        
        // Проверка расширения файла
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new ValidationException('Неподдерживаемое расширение файла');
        }
    }
    
    /**
     * 🗂️ Получение директории для загрузки
     * 
     * @param string $entityType - Тип сущности
     * @return string - Полный путь к директории
     */
    public static function getUploadDir($entityType) {
        $baseDir = __DIR__ . '/../../../uploads';
        return $baseDir . '/' . $entityType;
    }
    
    /**
     * 📝 Генерация имени файла
     * 
     * @param array $fileData - Данные файла
     * @param string $entityType - Тип сущности
     * @param int $entityId - ID сущности
     * @param int $photoId - ID записи в таблице photos
     * @return string - Уникальное имя файла
     */
    public static function generateFileName($fileData, $entityType, $entityId, $photoId) {
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        
        // Формат согласно документации: {entity_type}_{entity_id}_{photo_id}.{ext}
        return "{$entityType}_{$entityId}_{$photoId}.{$extension}";
    }
    
    /**
     * 📝 Генерация временного имени файла (для создания записи в БД)
     * 
     * @param array $fileData - Данные файла
     * @param string $entityType - Тип сущности
     * @return string - Временное имя файла
     */
    public static function generateTempFileName($fileData, $entityType) {
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        $timestamp = time();
        $random = uniqid();
        
        return "temp_{$entityType}_{$timestamp}_{$random}.{$extension}";
    }
    
    /**
     * 📝 Генерация правильного имени файла с photo_id
     * 
     * @param string $entityType - Тип сущности
     * @param int $entityId - ID сущности
     * @param int $photoId - ID записи в таблице photos
     * @param string $extension - Расширение файла
     * @return string - Правильное имя файла согласно документации
     */
    public static function generateCorrectFileName($entityType, $entityId, $photoId, $extension) {
        // Формат согласно документации: {entity_type}_{entity_id}_{photo_id}.{ext}
        return "{$entityType}_{$entityId}_{$photoId}.{$extension}";
    }
    
    /**
     * 🔗 Получение относительного пути для БД
     * 
     * @param string $fullPath - Полный путь к файлу
     * @return string - Относительный путь от корня проекта
     */
    public static function getRelativePath($fullPath) {
        $projectRoot = __DIR__ . '/../../../';
        return str_replace($projectRoot, '', $fullPath);
    }
    
    /**
     * 🗑️ Удаление файла
     * 
     * @param string $filePath - Путь к файлу (относительный или абсолютный)
     * @return bool - Успешность удаления
     */
    public static function deleteFile($filePath) {
        try {
            // Если путь относительный, преобразуем в абсолютный
            if (!file_exists($filePath)) {
                $filePath = __DIR__ . '/../../../' . $filePath;
            }
            
            if (file_exists($filePath)) {
                $result = unlink($filePath);
                if ($result) {
                    Logger::info("File deleted: $filePath");
                }
                return $result;
            }
            
            return false;
            
        } catch (Exception $e) {
            Logger::error('FileHelper::deleteFile failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📏 Получение размера файла в читаемом формате
     * 
     * @param int $bytes - Размер в байтах
     * @return string - Размер в KB, MB, GB
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 🔍 Проверка существования файла
     * 
     * @param string $filePath - Путь к файлу
     * @return bool - Существует ли файл
     */
    public static function fileExists($filePath) {
        // Если путь относительный, преобразуем в абсолютный
        if (!file_exists($filePath)) {
            $filePath = __DIR__ . '/../../../' . $filePath;
        }
        
        return file_exists($filePath);
    }
    
    /**
     * 🔍 Проверка и подготовка фото для сохранения
     * 
     * @param array $data - Данные запроса (может содержать 'photo' в base64)
     * @param array $files - Данные $_FILES (может содержать 'photo')
     * @return array|null - Подготовленные данные файла или null если фото нет
     * @throws ValidationException - Если фото не прошло валидацию
     */
    public static function preparePhotoForSaving($data = [], $files = []) {
        // Проверяем наличие фото в base64
        $hasBase64Photo = isset($data['photo']) && !empty($data['photo']);
        
        // Проверяем наличие фото в файле
        $hasFilePhoto = isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK;
        
        // Если фото нет нигде
        if (!$hasBase64Photo && !$hasFilePhoto) {
            return null;
        }
        
        // Если есть base64 фото - конвертируем в файл
        if ($hasBase64Photo) {
            return self::createTempFileFromBase64($data['photo'], 'photo.jpg');
        }
        
        // Если есть файл - возвращаем как есть
        if ($hasFilePhoto) {
            return $files['photo'];
        }
        
        return null;
    }
    
    /**
     * 🔄 Создание временного файла из base64 для совместимости с $_FILES
     * 
     * @param string $base64Data - Base64 кодированное изображение
     * @param string $originalName - Оригинальное имя файла
     * @return array - Данные файла в формате $_FILES
     * @throws Exception - Если не удалось создать временный файл
     */
    public static function createTempFileFromBase64($base64Data, $originalName = 'photo.jpg') {
        try {
            // Декодируем base64
            $imageBinary = base64_decode($base64Data, true);
            if ($imageBinary === false) {
                throw new Exception('Неверный формат base64 данных');
            }
            
            // Создаем временный файл
            $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
            if (file_put_contents($tempFile, $imageBinary) === false) {
                throw new Exception('Не удалось создать временный файл');
            }
            
            // Определяем MIME тип
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mimeType = 'image/jpeg'; // По умолчанию
            switch ($extension) {
                case 'png':
                    $mimeType = 'image/png';
                    break;
                case 'gif':
                    $mimeType = 'image/gif';
                    break;
            }
            
            // Возвращаем данные в формате $_FILES
            return [
                'name' => $originalName,
                'type' => $mimeType,
                'tmp_name' => $tempFile,
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($imageBinary)
            ];
            
        } catch (Exception $e) {
            Logger::error('FileHelper::createTempFileFromBase64 failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 