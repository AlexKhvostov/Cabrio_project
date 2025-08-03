<?php
/**
 * webhook.php
 * 
 * Точка входа для Telegram webhook
 * Обрабатывает входящие обновления от Telegram
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/handlers/MessageHandler.php';
require_once __DIR__ . '/utils/Logger.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

try {
    // Получаем данные от Telegram
    $input = file_get_contents('php://input');
    $update = json_decode($input, true);
    
    if (!$update) {
        writeToLog("Webhook: Invalid JSON input", ['input' => $input]);
        http_response_code(400);
        exit('Invalid JSON');
    }
    
    writeToLog("Webhook: Received update", [
        'update_id' => $update['update_id'] ?? null,
        'has_message' => isset($update['message']),
        'has_callback' => isset($update['callback_query']),
        'has_chat_member' => isset($update['chat_member'])
    ]);
    
    // Создаем обработчик
    $handler = new MessageHandler();
    
    // Обрабатываем обновление по типу
    if (isset($update['message'])) {
        // Обычное сообщение
        $handler->handle($update['message']);
        
    } elseif (isset($update['callback_query'])) {
        // Callback от inline-кнопки
        $handler->handleCallback($update['callback_query']);
        
    } elseif (isset($update['chat_member'])) {
        // Изменение участника чата
        $handler->handleChatMemberUpdate($update['chat_member']);
        
    } else {
        writeToLog("Webhook: Unknown update type", ['update' => $update]);
    }
    
    // Отвечаем успехом
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    writeToLog("Webhook: Critical error", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo 'Internal Server Error';
} 