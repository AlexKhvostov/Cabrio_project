<?php
/**
 * Logger — простой класс для логирования событий и ошибок в backend CabrioRide.
 * Логи пишутся в backend/logs/app.log и backend/logs/error.log.
 * Используйте Logger::info() и Logger::error() для записи информации.
 */
class Logger {
    public static function info($message) {
        file_put_contents(__DIR__ . '/../logs/app.log', date('c') . " [INFO] $message\n", FILE_APPEND);
    }
    public static function error($message) {
        file_put_contents(__DIR__ . '/../logs/error.log', date('c') . " [ERROR] $message\n", FILE_APPEND);
    }
} 