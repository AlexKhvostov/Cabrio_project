<?php
/**
 * Тест User Actions
 */
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
require_once __DIR__ . '/../actions/level1/_CheckUserByTelegramIdAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateUserAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateRoleUserAction.php';

echo "🧪 Тест User Actions\n";

$testTelegramId = time();
$testUsername = 'test_user_' . time();

// Тест 1: Создание пользователя
echo "\n1️⃣ Тест _CreateUserAction\n";
$createData = [
    'first_name' => 'Тест',
    'last_name' => 'Пользователь',
    'telegram_id' => $testTelegramId,
    'username' => $testUsername,
    'role_id' => 1 // guest
];

$result = _CreateUserAction::handle($createData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
    return; // Прерываем тест если создание не удалось
} else {
    echo "Создан пользователь ID: " . $result['data']['id'] . "\n";
    $userId = $result['data']['id'];
}

// Тест 2: Проверка пользователя
echo "\n2️⃣ Тест _CheckUserByTelegramIdAction\n";
$checkData = [
    'telegram_id' => $testTelegramId
];

$result = _CheckUserByTelegramIdAction::handle($checkData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Пользователь найден: " . ($result['data'] ? 'Да' : 'Нет') . "\n";
}

// Тест 3: Обновление пользователя
echo "\n3️⃣ Тест _UpdateUserAction\n";
$updateData = [
    'user_id' => $userId,
    'first_name' => 'Обновленное',
    'last_name' => 'Имя',
    'city' => 'Москва'
];

$result = _UpdateUserAction::handle($updateData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Пользователь обновлен\n";
}

// Тест 4: Обновление роли пользователя
echo "\n4️⃣ Тест _UpdateRoleUserAction\n";
$roleData = [
    'user_id' => $userId,
    'role_id' => 2 // member
];

$result = _UpdateRoleUserAction::handle($roleData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Роль пользователя обновлена\n";
}

echo "\n🏁 Тест User Actions завершен\n"; 