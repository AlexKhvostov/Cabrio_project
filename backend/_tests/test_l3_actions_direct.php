<?php
/**
 * Прямой тест L3 Actions
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../actions/level3/___CheckCarInClubAction.php';
require_once __DIR__ . '/../actions/level3/___LeaveBusinessCardAction.php';
require_once __DIR__ . '/../actions/level3/___AddCarToGarageAction.php';

echo "🔍 Прямой тест L3 Actions\n";
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

// Создаем временный файл из base64
$tempFile = tempnam(sys_get_temp_dir(), 'test_photo_');
file_put_contents($tempFile, base64_decode($photoBase64));

// Симулируем $_FILES
$_FILES['photo'] = [
    'name' => 'test_photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tempFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($tempFile)
];

echo "📦 Симулированные $_FILES:\n";
echo json_encode($_FILES, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

try {
    // Тестируем ___CheckCarInClubAction
    echo "🔍 Тестируем ___CheckCarInClubAction...\n";
    $result = ___CheckCarInClubAction::handle([]);
    echo "✅ Результат: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка в ___CheckCarInClubAction: " . $e->getMessage() . "\n";
    echo "📍 Файл: " . $e->getFile() . "\n";
    echo "📍 Строка: " . $e->getLine() . "\n";
}

// Очищаем временный файл
unlink($tempFile);

echo "✅ Тестирование завершено!\n"; 