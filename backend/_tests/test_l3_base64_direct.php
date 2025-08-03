<?php
/**
 * Прямой тест L3 Action с base64 данными
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../actions/level3/___CheckCarInClubAction.php';

echo "🔍 Прямой тест L3 Action с base64 данными\n";
echo "=" . str_repeat("=", 50) . "\n";

// Загружаем реальное фото
$photoPath = __DIR__ . '/../../uploads/car/car_1_237.jpg';
$photoBase64 = base64_encode(file_get_contents($photoPath));

echo "📸 Используем фото: car_1_237.jpg\n";
echo "📏 Размер base64: " . strlen($photoBase64) . " символов\n\n";

// Устанавливаем пользователя в контекст
$testUser = [
    'id' => 563,
    'telegram_id' => 123456789,
    'first_name' => 'Test',
    'last_name' => 'User',
    'username' => 'test_user',
    'role' => ['id' => 4, 'code' => 'member'],
    'role_id' => 4
];

AppContext::setCurrentUser($testUser);

echo "👤 Установлен пользователь: {$testUser['username']} (ID: {$testUser['id']})\n\n";

// Тестируем с base64 данными
$input = [
    'photo' => $photoBase64
];

echo "📦 Входные данные:\n";
echo "- photo: " . substr($photoBase64, 0, 50) . "...\n\n";

try {
    // Тестируем ___CheckCarInClubAction с base64
    echo "🔍 Тестируем ___CheckCarInClubAction с base64...\n";
    $result = ___CheckCarInClubAction::handle($input);
    echo "✅ Результат: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка в ___CheckCarInClubAction: " . $e->getMessage() . "\n";
    echo "📍 Файл: " . $e->getFile() . "\n";
    echo "📍 Строка: " . $e->getLine() . "\n";
}

echo "✅ Тестирование завершено!\n"; 