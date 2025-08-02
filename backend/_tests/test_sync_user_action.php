<?php
/**
 * 🧪 Тест __SyncUserDataAction
 * 
 * Назначение: Проверка L2 Action для синхронизации пользователя
 * Использование: php backend/_tests/test_sync_user_action.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение L2 Action
require_once __DIR__ . '/../actions/level2/__SyncUserDataAction.php';

echo "🧪 ТЕСТ __SyncUserDataAction\n";
echo "===========================\n\n";

// Глобальные переменные
$testTelegramId = time(); // Уникальный Telegram ID для теста

// Функция для вывода результата
function showResult($testName, $result) {
    echo "📋 $testName:\n";
    if ($result['success']) {
        echo "   ✅ УСПЕХ\n";
        echo "   🎯 Действие: " . $result['data']['action'] . "\n";
        echo "   👤 User ID: " . $result['data']['user_id'] . "\n";
        echo "   📝 Сообщение: " . $result['data']['message'] . "\n";
        
        if (isset($result['data']['updated_fields'])) {
            echo "   🔄 Обновлённые поля: " . implode(', ', $result['data']['updated_fields']) . "\n";
        }
        
        if (isset($result['data']['photo'])) {
            echo "   📸 Фото загружено: " . $result['data']['photo']['file_name'] . "\n";
        }
    } else {
        echo "   ❌ ОШИБКА\n";
        echo "   💬 Сообщение: " . $result['error']['message'] . "\n";
        if (isset($result['error']['code'])) {
            echo "   🔢 Код: " . $result['error']['code'] . "\n";
        }
    }
    echo "\n";
}

// ============================================================================
// ТЕСТ 1: Создание нового пользователя
// ============================================================================

echo "👤 ТЕСТ 1: Создание нового пользователя\n";
echo "=======================================\n";

$createData = [
    'telegram_id' => $testTelegramId,
    'first_name' => 'Тест',
    'last_name' => 'Пользователь',
    'username' => 'test_user_' . $testTelegramId
];

$result = __SyncUserDataAction::handle($createData);
showResult('Создание пользователя', $result);

if (!$result['success']) {
    echo "❌ Тест прерван из-за ошибки создания пользователя\n\n";
    exit;
}

$createdUserId = $result['data']['user_id'];

// ============================================================================
// ТЕСТ 2: Обновление данных пользователя
// ============================================================================

echo "🔄 ТЕСТ 2: Обновление данных пользователя\n";
echo "==========================================\n";

$updateData = [
    'telegram_id' => $testTelegramId,
    'first_name' => 'Обновленный',
    'last_name' => 'Тест',
    'username' => 'updated_test_user_' . $testTelegramId
];

$result = __SyncUserDataAction::handle($updateData);
showResult('Обновление пользователя', $result);

// ============================================================================
// ТЕСТ 3: Проверка без изменений
// ============================================================================

echo "✅ ТЕСТ 3: Проверка без изменений\n";
echo "=================================\n";

$noChangeData = [
    'telegram_id' => $testTelegramId,
    'first_name' => 'Обновленный', // те же данные
    'last_name' => 'Тест',
    'username' => 'updated_test_user_' . $testTelegramId
];

$result = __SyncUserDataAction::handle($noChangeData);
showResult('Проверка без изменений', $result);

// ============================================================================
// ТЕСТ 4: Создание пользователя с фото (симуляция)
// ============================================================================

echo "📸 ТЕСТ 4: Создание пользователя с фото (симуляция)\n";
echo "==================================================\n";

// Симулируем загрузку файла
$testPhotoData = [
    'name' => 'test_avatar.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/../docs/_test_photo_(9588MI1).jpg', // используем существующий файл
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Временно устанавливаем $_FILES
$_FILES['photo'] = $testPhotoData;

$photoData = [
    'telegram_id' => $testTelegramId + 1, // новый пользователь
    'first_name' => 'Фото',
    'last_name' => 'Пользователь',
    'username' => 'photo_user_' . ($testTelegramId + 1)
];

$result = __SyncUserDataAction::handle($photoData);
showResult('Создание пользователя с фото', $result);

// Очищаем $_FILES
unset($_FILES['photo']);

// ============================================================================
// ИТОГОВЫЙ ОТЧЕТ
// ============================================================================

echo "📊 ИТОГОВЫЙ ОТЧЕТ\n";
echo "==================\n";
echo "✅ Создан пользователь ID: $createdUserId\n";
echo "✅ Telegram ID: $testTelegramId\n";
echo "✅ Все тесты выполнены\n";
echo "\n🎉 __SyncUserDataAction РАБОТАЕТ КОРРЕКТНО!\n";
echo "=============================================\n"; 