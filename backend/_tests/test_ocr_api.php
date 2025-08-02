<?php
/**
 * API endpoint для тестирования OCR
 * Обрабатывает POST запросы для распознавания номеров автомобилей
 */

// Подключаем необходимые файлы
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../actions/helpers/RecognizeCarNumberFromPhotoAction.php';

// Устанавливаем заголовки для CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Метод не поддерживается. Используйте POST.'
    ]);
    exit();
}

try {
    // Проверяем наличие файла
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Файл не загружен или произошла ошибка загрузки');
    }
    
    $file = $_FILES['photo'];
    
    // Проверяем тип файла
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Неподдерживаемый тип файла. Разрешены только JPEG и PNG');
    }
    
    // Проверяем размер файла (3MB)
    if ($file['size'] > 3 * 1024 * 1024) {
        throw new Exception('Размер файла превышает 3MB');
    }
    
    // Вызываем действие распознавания
    $plateNumber = RecognizeCarNumberFromPhotoAction::handle($_FILES['photo']);
    
    // Возвращаем результат
    echo json_encode([
        'success' => true,
        'data' => [
            'plate_number' => $plateNumber,
            'message' => 'Номер автомобиля успешно распознан'
        ]
    ]);
    
} catch (Exception $e) {
    // Логируем ошибку
    Logger::log('ocr_error', 'OCR API Error: ' . $e->getMessage());
    
    // Возвращаем ошибку
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 