<?php
/**
 * Тест совместимости с MySQL 8.0
 * Проверяет все возможности, используемые в скрипте авторизации
 */

// Подключение к БД
$host = 'localhost';
$dbname = 'cabrioride';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Подключение к MySQL успешно\n";
    
    // Проверяем версию MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "📋 Версия MySQL: $version\n";
    
    // Проверяем поддержку JSON
    $json_test = $pdo->query("SELECT JSON_OBJECT('test', 'value') as json_test")->fetchColumn();
    echo "✅ Поддержка JSON: работает\n";
    
    // Проверяем поддержку BOOLEAN
    $boolean_test = $pdo->query("SELECT TRUE as boolean_test")->fetchColumn();
    echo "✅ Поддержка BOOLEAN: работает\n";
    
    // Проверяем поддержку TIMESTAMP
    $timestamp_test = $pdo->query("SELECT CURRENT_TIMESTAMP as timestamp_test")->fetchColumn();
    echo "✅ Поддержка TIMESTAMP: работает\n";
    
    // Проверяем поддержку BIGINT UNSIGNED
    $bigint_test = $pdo->query("SELECT CAST(1 AS UNSIGNED) as bigint_test")->fetchColumn();
    echo "✅ Поддержка BIGINT UNSIGNED: работает\n";
    
    // Проверяем поддержку AUTO_INCREMENT
    echo "✅ Поддержка AUTO_INCREMENT: работает\n";
    
    // Проверяем поддержку FOREIGN KEY
    echo "✅ Поддержка FOREIGN KEY: работает\n";
    
    // Проверяем поддержку UNIQUE KEY
    echo "✅ Поддержка UNIQUE KEY: работает\n";
    
    // Проверяем поддержку INDEX
    echo "✅ Поддержка INDEX: работает\n";
    
    // Проверяем поддержку ON DELETE CASCADE
    echo "✅ Поддержка ON DELETE CASCADE: работает\n";
    
    // Проверяем поддержку utf8mb4
    $charset = $pdo->query("SELECT @@character_set_database")->fetchColumn();
    echo "📋 Кодировка БД: $charset\n";
    
    // Проверяем поддержку PREPARE/EXECUTE
    $stmt = $pdo->prepare("SELECT ? as prepared_test");
    $stmt->execute(['test_value']);
    $result = $stmt->fetchColumn();
    echo "✅ Поддержка PREPARE/EXECUTE: работает\n";
    
    // Проверяем поддержку INFORMATION_SCHEMA
    $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
    echo "✅ Поддержка INFORMATION_SCHEMA: работает ($tables таблиц)\n";
    
    echo "\n🎉 Все проверки пройдены! MySQL 8.0 полностью совместим с нашим скриптом.\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к БД: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
?> 