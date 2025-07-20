<?php
/**
 * Logger.php
 * 
 * Утилита для логирования в боте
 */

/**
 * Записывает сообщение в лог
 * 
 * @param string $message Сообщение
 * @param mixed $data Дополнительные данные
 */
function writeToLog($message, $data = null) {
    try {
        // Создаем директорию для логов если её нет
        $log_path = __DIR__ . '/../logs/';
        if (!file_exists($log_path)) {
            mkdir($log_path, 0777, true);
        }
        
        $log_file = $log_path . date('Y-m-d') . '.log';
        
        // Форматируем сообщение
        $log_entry = date('Y-m-d H:i:s') . " | " . $message;
        
        // Добавляем данные если есть
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                // Маскируем чувствительные данные
                $sensitive_keys = ['token', 'password', 'secret', 'key'];
                $data = json_decode(json_encode($data), true); // Преобразуем в массив
                array_walk_recursive($data, function(&$value, $key) use ($sensitive_keys) {
                    if (in_array(strtolower($key), $sensitive_keys)) {
                        $value = '***masked***';
                    }
                });
            }
            $log_entry .= "\nData: " . print_r($data, true);
        }
        
        // Добавляем разделитель
        $log_entry .= "\n" . str_repeat('-', 80) . "\n";
        
        // Записываем в файл с UTF-8 BOM для корректного отображения в Windows
        if (!file_exists($log_file)) {
            file_put_contents($log_file, "\xEF\xBB\xBF"); // UTF-8 BOM
        }
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
    } catch (Exception $e) {
        error_log("Error writing to log: " . $e->getMessage());
    }
} 