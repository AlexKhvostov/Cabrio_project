<?php
/**
 * Простой тест endpoint
 */

// Включаем вывод ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Тест endpoint</h1>";

// Проверяем существование файлов
$files = [
    '../utils/Database.php',
    '../utils/ApiHandler.php', 
    '../config/config.php',
    '../api/cars/add.php'
];

echo "<h2>Проверка файлов:</h2>";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} - существует<br>";
    } else {
        echo "❌ {$file} - НЕ НАЙДЕН<br>";
    }
}

// Тестируем подключение к БД
echo "<h2>Тест подключения к БД:</h2>";
try {
    require_once '../utils/Database.php';
    require_once '../config/config.php';
    
    $db = Database::getInstance()->getConnection();
    echo "✅ Подключение к БД успешно<br>";
    
    // Проверяем таблицу cars
    $stmt = $db->query("SHOW TABLES LIKE 'cars'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Таблица cars существует<br>";
    } else {
        echo "❌ Таблица cars НЕ НАЙДЕНА<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "<br>";
}

// Тестируем ApiHandler
echo "<h2>Тест ApiHandler:</h2>";
try {
    require_once '../utils/ApiHandler.php';
    echo "✅ ApiHandler загружен<br>";
} catch (Exception $e) {
    echo "❌ Ошибка ApiHandler: " . $e->getMessage() . "<br>";
}
?> 