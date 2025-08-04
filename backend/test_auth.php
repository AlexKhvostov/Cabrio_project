<?php
/**
 * Тестовый файл для проверки авторизации через Telegram WebApp
 * ТОЛЬКО ДЛЯ РАЗРАБОТКИ!
 */
require_once __DIR__ . '/utils/load_env.php';
require_once __DIR__ . '/utils/ResponseHelper.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/utils/Logger.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Telegram-User-Id, X-Telegram-First-Name, X-Telegram-Last-Name, X-Telegram-Username, X-Telegram-Photo-URL, X-Telegram-Auth-Date, X-Telegram-Hash');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    Logger::info('Test Auth: Starting authentication test');
    
    // Тестируем извлечение Telegram данных
    require_once __DIR__ . '/utils/AuthHelper.php';
    $telegramData = AuthHelper::extractTelegramData();
    
    $result = [
        'success' => true,
        'data' => [
            'telegram_data_found' => $telegramData !== null,
            'telegram_data' => $telegramData,
            'headers' => function_exists('getallheaders') ? getallheaders() : [],
            'server_vars' => [
                'HTTP_X_TELEGRAM_USER_ID' => $_SERVER['HTTP_X_TELEGRAM_USER_ID'] ?? 'not_set',
                'HTTP_X_TELEGRAM_FIRST_NAME' => $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] ?? 'not_set',
                'HTTP_X_TELEGRAM_LAST_NAME' => $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] ?? 'not_set',
                'HTTP_X_TELEGRAM_USERNAME' => $_SERVER['HTTP_X_TELEGRAM_USERNAME'] ?? 'not_set',
                'HTTP_X_TELEGRAM_PHOTO_URL' => $_SERVER['HTTP_X_TELEGRAM_PHOTO_URL'] ?? 'not_set',
                'HTTP_X_TELEGRAM_AUTH_DATE' => $_SERVER['HTTP_X_TELEGRAM_AUTH_DATE'] ?? 'not_set',
                'HTTP_X_TELEGRAM_HASH' => $_SERVER['HTTP_X_TELEGRAM_HASH'] ?? 'not_set'
            ],
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set'
        ]
    ];
    
    if ($telegramData) {
        // Тестируем валидацию данных
        $validationResult = AuthHelper::validateTelegramData($telegramData);
        $result['data']['validation_result'] = $validationResult;
        
        if ($validationResult['success']) {
            // Тестируем полный процесс авторизации
            $authResult = AuthMiddleware::processPublic();
            $result['data']['auth_result'] = $authResult;
        }
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    Logger::error('Test Auth: Error during authentication test', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'TEST_ERROR',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 