<?php
/**
 * Основной конфигурационный файл
 * Загружает параметры из .env и предоставляет доступ к ним
 */

// Загружаем .env
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

// Функция для получения значения из конфига
function getConfig($key, $default = null) {
    return $_ENV[$key] ?? $default;
} 