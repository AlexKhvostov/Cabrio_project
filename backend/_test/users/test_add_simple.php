<?php
/**
 * Простой тест endpoint добавления пользователя
 */

// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

// Загружаем конфигурацию
require_once __DIR__ . '/../../config/config.php';

// Функция для получения значения из конфига (если не определена)
if (!function_exists('getConfig')) {
    function getConfig($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

// Тестовые данные
$testData = [
    'auth' => [],
    'data' => [
        'telegram_id' => 123456789,
        'username' => 'test_user',
        'first_name' => 'Test',
        'last_name' => 'User'
    ]
];

// Устанавливаем заголовки
header('Content-Type: application/json; charset=utf-8');

try {
    // Проверяем подключение к БД
    $db = Database::getInstance()->getConnection();
    echo "✅ База данных подключена успешно\n";
    
    // Проверяем таблицу users
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "✅ Таблица users доступна, записей: {$userCount}\n";
    
    // Проверяем таблицу ref_roles
    $stmt = $db->query("SELECT COUNT(*) FROM ref_roles");
    $roleCount = $stmt->fetchColumn();
    echo "✅ Таблица ref_roles доступна, записей: {$roleCount}\n";
    
    // Проверяем роль guest
    $stmt = $db->prepare("SELECT id, code FROM ref_roles WHERE code = 'guest'");
    $stmt->execute();
    $guestRole = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($guestRole) {
        echo "✅ Роль guest найдена: ID={$guestRole['id']}\n";
    } else {
        echo "❌ Роль guest не найдена\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
?> 