<?php
/**
 * car.php - API прокси для получения данных об автомобилях
 * 
 * Этот файл служит промежуточным звеном между frontend и backend API
 * для обхода CORS и обеспечения безопасности
 */

// Загружаем переменные окружения
require_once __DIR__ . '/../backend/utils/load_env.php';

// Устанавливаем заголовки для CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем BASE_URL из переменных окружения
$baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/app';

try {
    // Формируем URL для API запроса
    $apiUrl = $baseUrl . '/api/cars';
    
    // Выполняем запрос к backend API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: CabrioRide-Frontend/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("cURL error: " . $curlError);
    }
    
    if ($response === false) {
        throw new Exception("Failed to make API request");
    }
    
    // Декодируем JSON ответ
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response: " . json_last_error_msg());
    }
    
    // Преобразуем данные для frontend
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as &$car) {
            // Маппинг полей для frontend
            $car['plate_number'] = $car['reg_number'] ?? null;
            $car['brand'] = $car['brand']['name'] ?? null;
            $car['model'] = $car['model'] ?? null;
            $car['owner_name'] = null;
            
            // Формируем имя владельца
            if (isset($car['owner']) && $car['owner']) {
                $firstName = $car['owner']['first_name'] ?? '';
                $lastName = $car['owner']['last_name'] ?? '';
                $car['owner_name'] = trim($firstName . ' ' . $lastName) ?: null;
            }
            
            // Обрабатываем фото
            if (isset($car['photo']) && $car['photo'] && isset($car['photo']['url'])) {
                $photoUrl = $car['photo']['url'];
                // Если URL не полный, добавляем BASE_URL
                if (!preg_match('/^https?:\/\//', $photoUrl)) {
                    $car['photo_url'] = $baseUrl . '/' . ltrim($photoUrl, '/');
                } else {
                    $car['photo_url'] = $photoUrl;
                }
            } else {
                $car['photo_url'] = null;
            }
            
            // Статус
            $car['status'] = $car['status']['code'] ?? 'unknown';
        }
    }
    
    // Возвращаем результат
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Обработка ошибок
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'FRONTEND_ERROR',
            'message' => $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?> 