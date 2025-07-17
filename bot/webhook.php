<?php
// --- Логирование всех входящих запросов от Telegram ---
file_put_contents(
    __DIR__ . '/webhook.log',
    date('c') . ' ' . file_get_contents('php://input') . PHP_EOL,
    FILE_APPEND
);

// --- Подключаем обработчик сообщений ---
require_once __DIR__ . '/handlers/MessageHandler.php';

// --- Получаем входящий JSON и декодируем его в массив ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Если пришло сообщение, передаём его в MessageHandler ---
if (isset($data['message'])) {
    $handler = new MessageHandler();
    $handler->handle($data['message']);
}
