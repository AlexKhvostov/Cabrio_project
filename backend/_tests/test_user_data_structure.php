<?php
/**
 * Тест структуры данных пользователя
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../models/User.php';

echo "🔍 Тест структуры данных пользователя\n";
echo "=" . str_repeat("=", 50) . "\n";

// Тестируем создание пользователя
$testData = [
    'telegram_id' => 999999999,
    'username' => 'test_structure',
    'first_name' => 'Test',
    'last_name' => 'Structure',
    'role_id' => 1
];

echo "📦 Тестовые данные: " . json_encode($testData, JSON_UNESCAPED_UNICODE) . "\n\n";

try {
    // Создаем пользователя
    $userId = User::create($testData);
    echo "✅ Пользователь создан с ID: $userId\n";
    
    // Получаем данные пользователя
    $userData = User::findByIdWithDetails($userId);
    echo "📄 Данные пользователя:\n";
    echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // Проверяем ключи
    echo "\n🔍 Проверка ключей:\n";
    $expectedKeys = ['id', 'telegram_id', 'username', 'first_name_tg', 'last_name_tg', 'role_id'];
    foreach ($expectedKeys as $key) {
        $exists = isset($userData[$key]);
        $value = $userData[$key] ?? 'NULL';
        echo "- $key: " . ($exists ? "✅ ($value)" : "❌") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
} 