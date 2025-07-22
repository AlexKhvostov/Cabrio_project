<?php
/**
 * API Endpoint: Распознавание номера по фото
 * POST /api/ocr/recognize.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "image": "base64_encoded_image"
 *   }
 * }
 * 
 * Требования к изображению:
 * - Размер файла: максимум 3MB
 * - Рекомендуемое разрешение: 1024×768
 * - Рекомендуемая ориентация: портретная
 * - Автомобиль должен занимать минимум 15% площади изображения
 * - Номер должен быть читаемым человеком
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Номер распознан",
 *     "data": {
 *       "plate": "A123BC",
 *       "confidence": 0.95,
 *       "region": "ru"
 *     }
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

class OcrRecognizeEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (доступно всем)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем изображение
        $image = $this->requireField('image', 'Изображение обязательно');
        $image = $this->validateImage($image);
        
        try {
            // Получаем токен OCR
            $token = getConfig('OCR_TOKEN');
            if (!$token) {
                return $this->error('OCR_TOKEN не настроен', 500, 'CONFIG_ERROR');
            }
            
            // Сохраняем изображение во временный файл
            $tempFile = $this->saveTempImage($image);
            
            // Отправляем запрос к API распознавания
            $result = $this->recognizePlate($tempFile, $token);
            
            // Удаляем временный файл
            unlink($tempFile);
            
            return $this->success($result, 'Номер распознан');
            
        } catch (Exception $e) {
            return $this->error('Ошибка распознавания: ' . $e->getMessage(), 500, 'OCR_ERROR');
        }
    }
    
    /**
     * Валидирует изображение (base64)
     */
    protected function validateImage($image) {
        // Проверяем, что это base64 с data URL
        if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $image)) {
            $this->error('Неверный формат изображения. Ожидается base64 data URL (data:image/...;base64,...)', 400, 'VALIDATION_ERROR', [
                'field' => 'image',
                'rule' => 'base64_image',
                'received_format' => substr($image, 0, 50) . '...'
            ]);
        }
        
        // Проверяем размер (максимум 3MB для OCR)
        $base64Data = substr($image, strpos($image, ',') + 1);
        $size = strlen($base64Data) * 0.75; // Примерный размер в байтах
        
        if ($size > 3 * 1024 * 1024) {
            $this->error('Изображение слишком большое. Максимум 3MB для OCR', 400, 'VALIDATION_ERROR', [
                'field' => 'image',
                'rule' => 'max_size',
                'size_mb' => round($size / 1024 / 1024, 2),
                'requirements' => [
                    'max_size' => '3MB',
                    'recommended_resolution' => '1024×768',
                    'recommended_orientation' => 'portrait',
                    'vehicle_area' => 'минимум 15% изображения',
                    'plate_readability' => 'номер должен быть читаемым человеком'
                ]
            ]);
        }
        
        return $image;
    }
    
    /**
     * Сохраняет изображение во временный файл
     */
    protected function saveTempImage($base64Image) {
        $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
        $imageData = base64_decode($base64Data);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
        file_put_contents($tempFile, $imageData);
        
        return $tempFile;
    }
    
    /**
     * Распознаёт номер по изображению
     */
    protected function recognizePlate($imageFile, $token) {
        // Подготавливаем файл для отправки
        $image = curl_file_create($imageFile, 'image/jpeg', 'plate.jpg');
        
        // Отправляем запрос к API
        $ch = curl_init('https://api.platerecognizer.com/v1/plate-reader/');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'upload' => $image,
                'regions' => 'ru'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Token ' . $token
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception("Ошибка CURL: " . $curlError);
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception("API вернул код: " . $httpCode . ", ответ: " . $response);
        }
        
        // Декодируем ответ
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg());
        }
        
        // Извлекаем результат
        $results = $data['results'] ?? [];
        
        if (empty($results)) {
            return [
                'plate' => null,
                'confidence' => 0,
                'region' => 'ru',
                'message' => 'Номер не распознан'
            ];
        }
        
        // Берём первый результат
        $firstResult = $results[0];
        $plate = $firstResult['plate'] ?? null;
        $confidence = $firstResult['score'] ?? 0;
        return [
            'plate' => $plate,
            'confidence' => $confidence,
            'region' => 'ru',
            'message' => $plate ? 'Номер распознан' : 'Номер не распознан'
        ];
    }
}

// Запускаем обработку
$endpoint = new OcrRecognizeEndpoint();
$endpoint->handle();
?> 