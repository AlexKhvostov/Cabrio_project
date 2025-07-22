<?php
// Автоматическая загрузка .env (только для backend)
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env');
    foreach ($lines as $line) {
        if (preg_match('/^([A-Z0-9_]+)=(.*)$/', trim($line), $matches)) {
            putenv(trim($matches[1]) . '=' . trim($matches[2]));
        }
    }
}
// Логируем значения для диагностики
error_log('DB_HOST=' . getenv('DB_HOST'));
error_log('DB_PORT=' . getenv('DB_PORT'));
error_log('DB_NAME=' . getenv('DB_NAME'));
error_log('DB_USER=' . getenv('DB_USER'));
error_log('DB_PASSWORD=' . getenv('DB_PASSWORD'));

if (!function_exists('getConfig')) {
    function getConfig($key, $default = null) {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/config.php';
        }
        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } elseif (is_object($value) && property_exists($value, $part)) {
                $value = $value->$part;
            } else {
                return $default;
            }
        }
        return $value;
    }
} 