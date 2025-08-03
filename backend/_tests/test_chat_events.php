<?php
/**
 * Тест обработчиков событий чата
 * 
 * Проверяет работу L2 Actions для обработки входа/выхода пользователей
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../actions/level2/__HandleUserJoinedAction.php';
require_once __DIR__ . '/../actions/level2/__HandleUserLeftAction.php';

echo "🧪 Тест обработчиков событий чата\n";
echo "================================\n\n";

// Тест 1: Пользователь присоединяется к клубу
echo "1️⃣ Тест входа пользователя в клуб\n";
$joinData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan_test'
];

$joinResult = __HandleUserJoinedAction::handle($joinData);
echo "Результат: " . ($joinResult['success'] ? '✅ Успешно' : '❌ Ошибка') . "\n";
if ($joinResult['success']) {
    echo "Действие: " . ($joinResult['data']['action'] ?? 'unknown') . "\n";
    echo "Сообщение: " . ($joinResult['data']['message'] ?? 'no message') . "\n";
} else {
    echo "Ошибка: " . ($joinResult['error']['message'] ?? 'unknown error') . "\n";
}
echo "\n";

// Тест 2: Пользователь покидает клуб
echo "2️⃣ Тест выхода пользователя из клуба\n";
$leaveData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan_test'
];

$leaveResult = __HandleUserLeftAction::handle($leaveData);
echo "Результат: " . ($leaveResult['success'] ? '✅ Успешно' : '❌ Ошибка') . "\n";
if ($leaveResult['success']) {
    echo "Действие: " . ($leaveResult['data']['action'] ?? 'unknown') . "\n";
    echo "Сообщение: " . ($leaveResult['data']['message'] ?? 'no message') . "\n";
} else {
    echo "Ошибка: " . ($leaveResult['error']['message'] ?? 'unknown error') . "\n";
}
echo "\n";

// Тест 3: Повторный вход пользователя
echo "3️⃣ Тест повторного входа пользователя\n";
$rejoinData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Петров', // Измененная фамилия
    'username' => 'ivan_test'
];

$rejoinResult = __HandleUserJoinedAction::handle($rejoinData);
echo "Результат: " . ($rejoinResult['success'] ? '✅ Успешно' : '❌ Ошибка') . "\n";
if ($rejoinResult['success']) {
    echo "Действие: " . ($rejoinResult['data']['action'] ?? 'unknown') . "\n";
    echo "Сообщение: " . ($rejoinResult['data']['message'] ?? 'no message') . "\n";
    if (isset($rejoinResult['data']['updated_fields'])) {
        echo "Обновленные поля: " . implode(', ', $rejoinResult['data']['updated_fields']) . "\n";
    }
} else {
    echo "Ошибка: " . ($rejoinResult['error']['message'] ?? 'unknown error') . "\n";
}
echo "\n";

echo "✅ Тест завершен!\n"; 