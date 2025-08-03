<?php
/**
 * Logger.php
 * 
 * Утилита для логирования действий бота
 */

/**
 * Записывает сообщение в лог
 * 
 * @param string $message Сообщение для записи
 * @param array $context Дополнительные данные
 */
function writeToLog($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    
    // Создаем директорию для логов если её нет
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/bot_' . date('Y-m-d') . '.log';
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message";
    
    if (!empty($context)) {
        $logEntry .= " " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    
    $logEntry .= PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
} 