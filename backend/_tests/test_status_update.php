<?php
/**
 * 🧪 Тест обновления статуса автомобиля
 * 
 * Назначение: Проверка работы _UpdateStatusAction
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../utils/AppContext.php';

// Настраиваем тестовый контекст
AppContext::clear();
AppContext::setCurrentUser([
    'id' => 563,
    'telegram_id' => '123456789',
    'first_name' => 'Test',
    'last_name' => 'User',
    'role' => 4
]);

echo "🧪 Тест обновления статуса автомобиля\n";
echo "=====================================\n\n";

// Тест 1: Обновление статуса на business_card
echo "Тест 1: Обновление статуса на business_card (ID = 2)\n";
$result1 = _UpdateStatusAction::handle([
    'entity_type' => 'car',
    'entity_id' => 1, // Предполагаем, что есть автомобиль с ID = 1
    'status_id' => 2
]);

if ($result1['success']) {
    echo "✅ УСПЕХ: Статус обновлен на business_card\n";
    echo "Данные: " . json_encode($result1['data'], JSON_UNESCAPED_UNICODE) . "\n\n";
} else {
    echo "❌ ОШИБКА: " . $result1['error']['message'] . "\n\n";
}

// Тест 2: Проверка с несуществующим автомобилем
echo "Тест 2: Проверка с несуществующим автомобилем (ID = 999999)\n";
$result2 = _UpdateStatusAction::handle([
    'entity_type' => 'car',
    'entity_id' => 999999,
    'status_id' => 2
]);

if ($result2['success']) {
    echo "✅ УСПЕХ: Статус обновлен\n";
} else {
    echo "❌ ОШИБКА: " . $result2['error']['message'] . "\n";
}

echo "\n�� Тест завершен!\n"; 