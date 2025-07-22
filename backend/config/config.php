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

return [
    'DB_HOST' => $_ENV['DB_HOST'] ?? '',
    'DB_PORT' => $_ENV['DB_PORT'] ?? '3306',
    'DB_NAME' => $_ENV['DB_NAME'] ?? '',
    'DB_USER' => $_ENV['DB_USER'] ?? '',
    'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'name' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    // ... другие параметры ...
]; 