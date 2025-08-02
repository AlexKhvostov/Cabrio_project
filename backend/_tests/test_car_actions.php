<?php
/**
 * Тест Car Actions
 */
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level1/_CreateCarAction.php';
require_once __DIR__ . '/../actions/level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateOwnerToCarAction.php';

echo "🧪 Тест Car Actions\n";

$testPlateNumber = 'TEST' . time();
$testUserId = 563; // используем существующего пользователя

// Тест 1: Создание автомобиля
echo "\n1️⃣ Тест _CreateCarAction\n";
$createData = [
    'model' => 'Z4',
    'color' => 'Красный',
    'year' => 2020,
    'reg_number' => $testPlateNumber,
    'create_user_id' => $testUserId,
    'owner_user_id' => $testUserId,
    'status_id' => 1 // noticed
];

$result = _CreateCarAction::handle($createData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
    return; // Прерываем тест если создание не удалось
} else {
    echo "Создан автомобиль ID: " . $result['data']['id'] . "\n";
    $carId = $result['data']['id'];
}

// Тест 2: Проверка автомобиля
echo "\n2️⃣ Тест _CheckCarInDbAction\n";
$checkData = [
    'plate_number' => $testPlateNumber
];

$result = _CheckCarInDbAction::handle($checkData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Автомобиль найден: " . ($result['data'] ? 'Да' : 'Нет') . "\n";
}

// Тест 3: Обновление статуса автомобиля
echo "\n3️⃣ Тест _UpdateStatusAction\n";
$statusData = [
    'entity_type' => 'car',
    'entity_id' => $carId,
    'status_id' => 2 // business_card
];

$result = _UpdateStatusAction::handle($statusData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Статус автомобиля обновлен\n";
}

// Тест 4: Обновление владельца автомобиля
echo "\n4️⃣ Тест _UpdateOwnerToCarAction\n";
$ownerData = [
    'car_id' => $carId,
    'user_id' => $testUserId
];

$result = _UpdateOwnerToCarAction::handle($ownerData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Владелец автомобиля обновлен\n";
}

echo "\n🏁 Тест Car Actions завершен\n"; 