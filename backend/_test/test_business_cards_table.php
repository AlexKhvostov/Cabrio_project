<?php
/**
 * Тест таблицы business_cards
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

echo "<h1>🔍 Тест таблицы business_cards</h1>";

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>📊 Проверка таблицы business_cards:</h2>";
    
    // Проверяем существование таблицы
    try {
        $stmt = $connection->query("SELECT COUNT(*) FROM business_cards");
        $count = $stmt->fetchColumn();
        echo "<p style='color: green;'>✅ Таблица 'business_cards' существует: $count записей</p>";
        
        // Показываем структуру таблицы
        $stmt = $connection->query("DESCRIBE business_cards");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Структура таблицы:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Поле</th><th>Тип</th><th>NULL</th><th>Ключ</th><th>По умолчанию</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Показываем последние записи
        $stmt = $connection->query("SELECT * FROM business_cards ORDER BY id DESC LIMIT 5");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($records) {
            echo "<h3>📝 Последние записи:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Car ID</th><th>Location</th><th>Notes</th><th>Inviter</th><th>Created</th></tr>";
            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>{$record['id']}</td>";
                echo "<td>{$record['car_id']}</td>";
                echo "<td>{$record['location']}</td>";
                echo "<td>{$record['notes']}</td>";
                echo "<td>{$record['inviter_user_id']}</td>";
                echo "<td>{$record['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ Записей в таблице нет</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка таблицы 'business_cards': " . $e->getMessage() . "</p>";
    }
    
    // Проверяем связанные таблицы
    echo "<h2>🔗 Проверка связанных таблиц:</h2>";
    
    $tables = ['cars', 'users'];
    foreach ($tables as $table) {
        try {
            $stmt = $connection->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Таблица '$table': $count записей</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Таблица '$table': " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка подключения: " . $e->getMessage() . "</p>";
}
?> 