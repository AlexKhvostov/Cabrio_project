<?php
/**
 * webhook.php
 * 
 * Точка входа для Telegram Webhook
 * Получает и обрабатывает все входящие сообщения от Telegram
 */

// Включаем отображение всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию бота и логирование
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/services/BotService.php';

// Получаем входящие данные
$content = file_get_contents('php://input');

// Логируем все входящие запросы
writeToLog("Received update:", $content);

// Декодируем JSON от Telegram
$update = json_decode($content, true);

// Проверяем что данные получены
if (empty($update)) {
    writeToLog('Error: Empty update received');
    die('Empty update');
}

try {
    // Подключаем обработчик сообщений
    require_once __DIR__ . '/handlers/MessageHandler.php';
    
    // Создаем сервисы
    $botService = new BotService();
    $handler = new MessageHandler();
    
    // Логируем для отладки
    writeToLog("Update type check:", $update);
    
    // Обрабатываем разные типы обновлений
    if (isset($update['message'])) {
        // Обычное сообщение
        writeToLog("Processing message:", $update['message']);
        
        // Проверяем специальные типы сообщений
        if (isset($update['message']['new_chat_members'])) {
            writeToLog("Processing new chat members:", $update['message']['new_chat_members']);
            $handler->handle($update['message']);
        } elseif (isset($update['message']['left_chat_member'])) {
            writeToLog("Processing left chat member:", $update['message']['left_chat_member']);
            $handler->handle($update['message']);
        } else {
            // Обычное сообщение
            $handler->handle($update['message']);
        }
        
    } elseif (isset($update['callback_query'])) {
        // Callback от inline-кнопок
        writeToLog("Processing callback:", $update['callback_query']);
        $handler->handleCallback($update['callback_query']);
        
    } elseif (isset($update['chat_member'])) {
        // Изменения в статусе участника чата
        writeToLog("Processing chat member update:", $update['chat_member']);
        $handler->handleChatMemberUpdate($update['chat_member']);
        
    } elseif (isset($update['my_chat_member'])) {
        // Изменения в статусе бота в чате
        writeToLog("Processing my chat member update:", $update['my_chat_member']);
        $handler->handleChatMemberUpdate($update['my_chat_member']);
    }
    
} catch (Exception $e) {
    writeToLog('Error processing update: ' . $e->getMessage());
    writeToLog('Update data:', $update);
    writeToLog('Stack trace: ' . $e->getTraceAsString());
}
