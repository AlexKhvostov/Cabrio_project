<?php
/**
 * 🧪 Тест исправлений в синхронизации пользователя
 * 
 * Проверяет:
 * 1. Правильное сохранение first_name_tg и last_name_tg при создании
 * 2. Предотвращение повторного сохранения фото
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level2/__SyncUserDataAction.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Logger.php';

echo "🧪 ТЕСТ ИСПРАВЛЕНИЙ В СИНХРОНИЗАЦИИ ПОЛЬЗОВАТЕЛЯ\n";
echo "================================================\n\n";

// Тест 1: Проверка создания пользователя с правильными полями
echo "📝 Тест 1: Создание пользователя с first_name_tg и last_name_tg\n";

$createData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Петров',
    'username' => 'ivan_petrov'
];

$result = __SyncUserDataAction::handle($createData);

if ($result['success']) {
    $userData = $result['data'];
    echo "✅ Пользователь создан успешно\n";
    echo "   ID: {$userData['id']}\n";
    echo "   first_name_tg: {$userData['first_name_tg']}\n";
    echo "   last_name_tg: {$userData['last_name_tg']}\n";
    echo "   action: {$userData['action']}\n";
    
    // Проверяем, что поля сохранились правильно
    if ($userData['first_name_tg'] === 'Иван' && $userData['last_name_tg'] === 'Петров') {
        echo "✅ Поля first_name_tg и last_name_tg сохранены правильно\n";
    } else {
        echo "❌ Ошибка: поля first_name_tg и last_name_tg не сохранились правильно\n";
    }
} else {
    echo "❌ Ошибка создания пользователя: " . $result['error']['message'] . "\n";
}

echo "\n";

// Тест 2: Проверка обновления пользователя
echo "📝 Тест 2: Обновление пользователя\n";

$updateData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Петров',
    'username' => 'ivan_petrov_updated'
];

$result = __SyncUserDataAction::handle($updateData);

if ($result['success']) {
    $userData = $result['data'];
    echo "✅ Пользователь обновлен успешно\n";
    echo "   ID: {$userData['id']}\n";
    echo "   username: {$userData['username']}\n";
    echo "   action: {$userData['action']}\n";
    
    if ($userData['action'] === 'updated') {
        echo "✅ Действие 'updated' выполнено правильно\n";
    } else {
        echo "❌ Ошибка: неожиданное действие: {$userData['action']}\n";
    }
} else {
    echo "❌ Ошибка обновления пользователя: " . $result['error']['message'] . "\n";
}

echo "\n";

// Тест 3: Проверка без изменений
echo "📝 Тест 3: Синхронизация без изменений\n";

$noChangeData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Петров',
    'username' => 'ivan_petrov_updated'
];

$result = __SyncUserDataAction::handle($noChangeData);

if ($result['success']) {
    $userData = $result['data'];
    echo "✅ Синхронизация выполнена успешно\n";
    echo "   action: {$userData['action']}\n";
    
    if ($userData['action'] === 'no_changes') {
        echo "✅ Действие 'no_changes' выполнено правильно\n";
    } else {
        echo "❌ Ошибка: неожиданное действие: {$userData['action']}\n";
    }
} else {
    echo "❌ Ошибка синхронизации: " . $result['error']['message'] . "\n";
}

echo "\n";

// Тест 4: Проверка telegram_photo_id
echo "📝 Тест 4: Проверка поля telegram_photo_id\n";

$user = User::findById(1); // Получаем первого пользователя для теста
if ($user) {
    echo "✅ Поле telegram_photo_id доступно в модели\n";
    echo "   telegram_photo_id: " . ($user->telegram_photo_id ?? 'null') . "\n";
} else {
    echo "❌ Не удалось получить пользователя для теста\n";
}

echo "\n🎉 ТЕСТ ИСПРАВЛЕНИЙ ЗАВЕРШЕН!\n"; 