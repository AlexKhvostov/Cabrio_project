<?php
/**
 * Класс для логирования событий в системе
 * Записывает логи в файл с указанием времени и уровня
 */

class Logger {
    private $logFile;
    private $logLevel;
    
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    public function __construct($logFile = null, $logLevel = 'INFO') {
        $this->logFile = $logFile ?? __DIR__ . '/../logs/app.log';
        $this->logLevel = $logLevel;
        
        // Создаём директорию для логов если её нет
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Записывает debug сообщение
     */
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Записывает info сообщение
     */
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Записывает warning сообщение
     */
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Записывает error сообщение
     */
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Записывает critical сообщение
     */
    public function critical($message, $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * Основной метод логирования
     */
    private function log($level, $message, $context = []) {
        // Проверяем уровень логирования
        if (!$this->shouldLog($level)) {
            return;
        }
        
        // Формируем строку лога
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}";
        
        // Добавляем контекст если есть
        if (!empty($context)) {
            $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE);
            $logEntry .= " Context: {$contextStr}";
        }
        
        $logEntry .= PHP_EOL;
        
        // Записываем в файл
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Проверяет, нужно ли логировать на данном уровне
     */
    private function shouldLog($level) {
        $levels = [
            self::LEVEL_DEBUG => 0,
            self::LEVEL_INFO => 1,
            self::LEVEL_WARNING => 2,
            self::LEVEL_ERROR => 3,
            self::LEVEL_CRITICAL => 4
        ];
        
        $currentLevel = $levels[$this->logLevel] ?? 1;
        $messageLevel = $levels[$level] ?? 0;
        
        return $messageLevel >= $currentLevel;
    }
    
    /**
     * Очищает старые логи (старше 30 дней)
     */
    public function cleanOldLogs($days = 30) {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        $lines = file($this->logFile);
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $newLines = [];
        
        foreach ($lines as $line) {
            // Извлекаем timestamp из строки лога
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $logTime = strtotime($matches[1]);
                if ($logTime >= $cutoffTime) {
                    $newLines[] = $line;
                }
            } else {
                // Если не можем распарсить время, оставляем строку
                $newLines[] = $line;
            }
        }
        
        file_put_contents($this->logFile, implode('', $newLines));
    }
    
    /**
     * Получает размер файла лога в байтах
     */
    public function getLogSize() {
        return file_exists($this->logFile) ? filesize($this->logFile) : 0;
    }
    
    /**
     * Получает последние N строк лога
     */
    public function getLastLines($count = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile);
        return array_slice($lines, -$count);
    }
} 