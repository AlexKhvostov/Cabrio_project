<?php
/**
 * config.php
 * 
 * Конфигурация Telegram бота
 * Загружает переменные окружения из .env файла
 * Использует тот же подход что и backend
 */

// Загружаем переменные окружения из корневого .env файла
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

/**
 * Получает значение конфигурации
 */
function getConfig($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Проверяем обязательные настройки
if (empty($_ENV['BOT_TOKEN'])) {
    throw new Exception('BOT_TOKEN не настроен в .env файле');
}

// Проверяем дополнительные настройки
if (empty($_ENV['CLUB_CHAT_ID'])) {
    writeToLog("WARNING: CLUB_CHAT_ID не настроен, используем значение по умолчанию");
}

if (empty($_ENV['CHAT_INVITE_LINK'])) {
    writeToLog("WARNING: CHAT_INVITE_LINK не настроен, используем значение по умолчанию");
} 