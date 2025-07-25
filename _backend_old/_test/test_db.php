<?php
/**
 * Простой тест подключения к базе данных
 */

// Загружаем конфигурацию
require_once __DIR__ . '/../config/config.php';

// Функция для получения значения из конфига (если не определена)
if (!function_exists('getConfig')) {
    function getConfig($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

require_once __DIR__ . '/../utils/Database.php';

echo "<h1>🔍 Тест подключения к базе данных</h1>";

try {
    // Проверяем конфигурацию
    echo "<h2>📋 Конфигурация:</h2>";
    echo "<p><strong>DB_HOST:</strong> " . getConfig('DB_HOST', 'НЕ НАЙДЕНО') . "</p>";
    echo "<p><strong>DB_PORT:</strong> " . getConfig('DB_PORT', 'НЕ НАЙДЕНО') . "</p>";
    echo "<p><strong>DB_NAME:</strong> " . getConfig('DB_NAME', 'НЕ НАЙДЕНО') . "</p>";
    echo "<p><strong>DB_USER:</strong> " . getConfig('DB_USER', 'НЕ НАЙДЕНО') . "</p>";
    echo "<p><strong>DB_PASSWORD:</strong> " . (getConfig('DB_PASSWORD') ? 'УСТАНОВЛЕН' : 'НЕ УСТАНОВЛЕН') . "</p>";
    
    // Пытаемся подключиться
    echo "<h2>🔌 Подключение к БД:</h2>";
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Подключение успешно!</p>";
    
    // Проверяем таблицы
    echo "<h2>📊 Проверка таблиц:</h2>";
    
    $tables = ['cars', 'statuses', 'users'];
    foreach ($tables as $table) {
        try {
            $stmt = $connection->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Таблица '$table': $count записей</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Таблица '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    // Тестируем запрос из check.php
    echo "<h2>🧪 Тест запроса check.php:</h2>";
    try {
        $sql = "SELECT c.id, c.reg_number, c.status_id, s.name as status_name 
                FROM cars c 
                LEFT JOIN statuses s ON c.status_id = s.id 
                WHERE c.reg_number = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute(['A123BC']);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($car) {
            echo "<p style='color: green;'>✅ Запрос выполнен успешно!</p>";
            echo "<p><strong>Найден автомобиль:</strong></p>";
            echo "<pre>" . json_encode($car, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<p style='color: orange;'>⚠️ Автомобиль с номером A123BC не найден</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка запроса: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка подключения: " . $e->getMessage() . "</p>";
    
    // Показываем детали ошибки
    echo "<h2>🔍 Детали ошибки:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 