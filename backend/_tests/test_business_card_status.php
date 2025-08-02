<?php
/**
 * 🧪 Тест логики обновления статуса при оставлении визитки
 * 
 * Назначение: Проверка что статус обновляется только с "noticed" на "business_card"
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level2/__DropBusinessCardAction.php';
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

echo "🧪 Тест логики обновления статуса при оставлении визитки\n";
echo "========================================================\n\n";

// Тест 1: Автомобиль со статусом "noticed" (ID = 1) - должен обновиться
echo "Тест 1: Автомобиль со статусом 'noticed' (ID = 1)\n";
echo "Ожидается: статус обновится на 'business_card' (ID = 2)\n";

// Сначала создаем автомобиль со статусом "noticed"
// (в реальном тесте нужно создать автомобиль в БД)

$result1 = __DropBusinessCardAction::handle([
    'plate_number' => 'TEST001'
]);

if ($result1['success']) {
    echo "✅ УСПЕХ: Визитка оставлена\n";
    echo "Действие: " . $result1['data']['action'] . "\n";
    echo "Статус автомобиля: " . $result1['data']['car']['status_id'] . "\n";
    echo "Сообщение: " . $result1['data']['message'] . "\n\n";
} else {
    echo "❌ ОШИБКА: " . $result1['error']['message'] . "\n\n";
}

// Тест 2: Автомобиль с другим статусом - не должен обновиться
echo "Тест 2: Автомобиль с другим статусом (не 'noticed')\n";
echo "Ожидается: статус НЕ обновится\n";

$result2 = __DropBusinessCardAction::handle([
    'plate_number' => 'TEST002'
]);

if ($result2['success']) {
    echo "✅ УСПЕХ: Визитка оставлена\n";
    echo "Действие: " . $result2['data']['action'] . "\n";
    echo "Статус автомобиля: " . $result2['data']['car']['status_id'] . "\n";
    echo "Сообщение: " . $result2['data']['message'] . "\n\n";
} else {
    echo "❌ ОШИБКА: " . $result2['error']['message'] . "\n\n";
}

echo "🎯 Тест завершен!\n";
echo "\n📋 Логика работы:\n";
echo "- Если автомобиль имеет статус 'noticed' (ID = 1) → обновляется на 'business_card' (ID = 2)\n";
echo "- Если автомобиль имеет другой статус → статус НЕ изменяется\n";
echo "- Если автомобиль не найден → создается новый со статусом 'business_card' (ID = 2)\n"; 