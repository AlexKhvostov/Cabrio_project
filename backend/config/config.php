<?php
/**
 * Конфиг только для параметров Telegram-бота
 */

// Загружаем .env (если не загружен)
$env_path = __DIR__ . '/../../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

return [
    'telegram' => [
        'bot_token' => $_ENV['BOT_TOKEN'] ?? '',
        'main_chat_id' => $_ENV['MAIN_CHAT_ID'] ?? '',
    ],
]; 