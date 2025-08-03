<?php
/**
 * 🧪 Тест обновления статуса сущности через системный эндпоинт
 * 
 * Назначение: Проверка работы _UpdateStatusAction
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../utils/AppContext.php';

// Настраиваем тестовый контекст
AppContext::clear();

echo "🧪 Тест обновления статуса сущности через системный эндпоинт\n";
echo "==========================================================\n\n";

// Тест 1: Обновление статуса автомобиля
echo "Тест 1: Обновление статуса автомобиля на 'business_card' (ID = 2)\n";
$testData1 = [
    'entity_type' => 'car',
    'entity_id' => 1, // Предполагаем, что есть автомобиль с ID = 1
    'status_id' => 2  // business_card
];

$result1 = _UpdateStatusAction::handle($testData1);
showResult('Обновление статуса автомобиля', $result1);

// Тест 2: Обновление статуса события
echo "\nТест 2: Обновление статуса события на 'active' (ID = 7)\n";
$testData2 = [
    'entity_type' => 'event',
    'entity_id' => 1, // Предполагаем, что есть событие с ID = 1
    'status_id' => 7  // active
];

$result2 = _UpdateStatusAction::handle($testData2);
showResult('Обновление статуса события', $result2);

// Тест 3: Проверка с несуществующей сущностью
echo "\nТест 3: Проверка с несуществующим автомобилем (ID = 999999)\n";
$testData3 = [
    'entity_type' => 'car',
    'entity_id' => 999999,
    'status_id' => 2
];

$result3 = _UpdateStatusAction::handle($testData3);
showResult('Обновление статуса несуществующего автомобиля', $result3);

// Тест 4: Проверка с некорректным типом сущности
echo "\nТест 4: Проверка с некорректным типом сущности\n";
$testData4 = [
    'entity_type' => 'invalid_type',
    'entity_id' => 1,
    'status_id' => 2
];

$result4 = _UpdateStatusAction::handle($testData4);
showResult('Обновление статуса с некорректным типом', $result4);

echo "\n✅ Тест завершен!\n";

/**
 * Показывает результат теста
 */
function showResult($testName, $result) {
    if ($result['success']) {
        echo "✅ УСПЕХ: $testName\n";
        echo "Данные: " . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ ОШИБКА: $testName\n";
        echo "Код: " . ($result['error']['code'] ?? 'UNKNOWN') . "\n";
        echo "Сообщение: " . ($result['error']['message'] ?? 'Неизвестная ошибка') . "\n";
    }
} 