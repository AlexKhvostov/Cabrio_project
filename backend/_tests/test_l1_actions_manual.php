<?php
/**
 * 🧪 Ручной тест L1 Actions
 * 
 * Назначение: Проверка всех L1 Actions в консольном режиме
 * Использование: php backend/_tests/test_l1_actions_manual.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение всех L1 Actions
require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
require_once __DIR__ . '/../actions/level1/_CheckUserByTelegramIdAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateUserAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateRoleUserAction.php';
require_once __DIR__ . '/../actions/level1/_CreateCarAction.php';
require_once __DIR__ . '/../actions/level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateOwnerToCarAction.php';
require_once __DIR__ . '/../actions/level1/_CreateBusinessCardAction.php';
require_once __DIR__ . '/../actions/level1/_CreatePhotoAction.php';

echo "🧪 РУЧНОЙ ТЕСТ L1 ACTIONS\n";
echo "========================\n\n";

// Глобальные переменные для хранения результатов
$createdUserId = null;
$createdCarId = null;
$testTelegramId = time(); // Уникальный Telegram ID для теста
$testPlateNumber = 'TEST' . time(); // Уникальный номер для теста

// Функция для вывода результата
function showResult($actionName, $result) {
    echo "📋 $actionName:\n";
    if ($result['success']) {
        echo "   ✅ УСПЕХ\n";
        if (isset($result['data'])) {
            if (is_array($result['data'])) {
                echo "   📊 Данные: " . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo "   📊 Данные: " . $result['data'] . "\n";
            }
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
// ТЕСТ 1: USER ACTIONS
// ============================================================================

echo "👤 ТЕСТ 1: USER ACTIONS\n";
echo "=======================\n";

// 1.1 Создание пользователя
echo "1️⃣ Создание пользователя (_CreateUserAction)\n";
$userData = [
    'telegram_id' => $testTelegramId,
    'username' => 'test_user_' . time(),
    'first_name' => 'Тест',
    'last_name' => 'Пользователь',
    'role_id' => 1 // guest
];

$result = _CreateUserAction::handle($userData);
showResult('_CreateUserAction', $result);

if ($result['success']) {
    $createdUserId = $result['data']['id'];
    echo "   💾 Создан пользователь с ID: $createdUserId\n\n";
} else {
    echo "   ❌ Тест прерван из-за ошибки создания пользователя\n\n";
    exit;
}

// 1.2 Проверка пользователя по Telegram ID
echo "2️⃣ Проверка пользователя (_CheckUserByTelegramIdAction)\n";
$checkData = [
    'telegram_id' => $testTelegramId
];

$result = _CheckUserByTelegramIdAction::handle($checkData);
showResult('_CheckUserByTelegramIdAction', $result);

// 1.3 Обновление пользователя
echo "3️⃣ Обновление пользователя (_UpdateUserAction)\n";
$updateData = [
    'user_id' => $createdUserId,
    'username' => 'updated_test_user',
    'first_name' => 'Обновленный',
    'last_name' => 'Тест',
    'city' => 'Москва',
    'email' => 'test@example.com'
];

$result = _UpdateUserAction::handle($updateData);
showResult('_UpdateUserAction', $result);

// 1.4 Обновление роли пользователя
echo "4️⃣ Обновление роли (_UpdateRoleUserAction)\n";
$roleData = [
    'user_id' => $createdUserId,
    'role_id' => 2 // member
];

$result = _UpdateRoleUserAction::handle($roleData);
showResult('_UpdateRoleUserAction', $result);

// ============================================================================
// ТЕСТ 2: CAR ACTIONS
// ============================================================================

echo "🚗 ТЕСТ 2: CAR ACTIONS\n";
echo "======================\n";

// 2.1 Создание автомобиля
echo "1️⃣ Создание автомобиля (_CreateCarAction)\n";
$carData = [
    'create_user_id' => $createdUserId, // Обязательное поле
    'model' => 'BMW Z4',
    'color' => 'Красный',
    'year' => 2020,
    'reg_number' => $testPlateNumber,
    'owner_user_id' => null, // Сначала без владельца
    'status_id' => 1 // noticed - замеченная
];

$result = _CreateCarAction::handle($carData);
showResult('_CreateCarAction', $result);

if ($result['success']) {
    $createdCarId = $result['data']['id'];
    echo "   💾 Создан автомобиль с ID: $createdCarId\n\n";
} else {
    echo "   ❌ Тест прерван из-за ошибки создания автомобиля\n\n";
    exit;
}

// 2.2 Проверка автомобиля в базе
echo "2️⃣ Проверка автомобиля (_CheckCarInDbAction)\n";
$checkCarData = [
    'plate_number' => $testPlateNumber
];

$result = _CheckCarInDbAction::handle($checkCarData);
showResult('_CheckCarInDbAction', $result);

// 2.3 Обновление статуса автомобиля
echo "3️⃣ Обновление статуса (_UpdateStatusAction)\n";
$statusData = [
    'entity_type' => 'car',
    'entity_id' => $createdCarId,
    'status_id' => 2 // business_card
];

$result = _UpdateStatusAction::handle($statusData);
showResult('_UpdateStatusAction', $result);

// 2.4 Обновление владельца автомобиля
echo "4️⃣ Обновление владельца (_UpdateOwnerToCarAction)\n";
$ownerData = [
    'car_id' => $createdCarId,
    'user_id' => $createdUserId
];

$result = _UpdateOwnerToCarAction::handle($ownerData);
showResult('_UpdateOwnerToCarAction', $result);

// ============================================================================
// ТЕСТ 3: BUSINESS CARD ACTIONS
// ============================================================================

echo "📇 ТЕСТ 3: BUSINESS CARD ACTIONS\n";
echo "================================\n";

// 3.1 Создание визитки
echo "1️⃣ Создание визитки (_CreateBusinessCardAction)\n";
$cardData = [
    'car_id' => $createdCarId,
    'user_id' => $createdUserId,
    'location' => 'Москва, центр',
    'notes' => 'Отличная машина! Привет от участника клуба.'
];

$result = _CreateBusinessCardAction::handle($cardData);
showResult('_CreateBusinessCardAction', $result);

if ($result['success']) {
    $createdCardId = $result['data']['id'];
    echo "   💾 Создана визитка с ID: $createdCardId\n\n";
} else {
    echo "   ❌ Ошибка создания визитки\n\n";
}

// ============================================================================
// ТЕСТ 4: PHOTO ACTIONS
// ============================================================================

echo "📸 ТЕСТ 4: PHOTO ACTIONS\n";
echo "========================\n";

// 4.1 Создание фото
echo "1️⃣ Создание фото (_CreatePhotoAction)\n";
$photoData = [
    'entity_type' => 'car',
    'entity_id' => $createdCarId,
    'file_name' => 'car_' . $createdCarId . '_' . time() . '.jpg',
    'url' => '/uploads/cars/car_' . $createdCarId . '_' . time() . '.jpg',
    'photo_type' => 'gallery',
    'description' => 'Фото автомобиля для теста',
    'uploaded_by' => $createdUserId
];

$result = _CreatePhotoAction::handle($photoData);
showResult('_CreatePhotoAction', $result);

if ($result['success']) {
    $createdPhotoId = $result['data']['id'];
    echo "   💾 Создано фото с ID: $createdPhotoId\n\n";
} else {
    echo "   ❌ Ошибка создания фото\n\n";
}

// ============================================================================
// ИТОГОВЫЙ ОТЧЕТ
// ============================================================================

echo "📊 ИТОГОВЫЙ ОТЧЕТ\n";
echo "==================\n";
echo "✅ Создан пользователь ID: $createdUserId\n";
echo "✅ Создан автомобиль ID: $createdCarId\n";
echo "✅ Создана визитка ID: " . ($createdCardId ?? 'N/A') . "\n";
echo "✅ Создано фото ID: " . ($createdPhotoId ?? 'N/A') . "\n";
echo "\n🎉 ВСЕ L1 ACTIONS ПРОТЕСТИРОВАНЫ!\n";
echo "====================================\n";

// Дополнительная проверка - поиск созданных данных
echo "\n🔍 ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА:\n";
echo "============================\n";

// Проверяем пользователя
$checkUser = _CheckUserByTelegramIdAction::handle(['telegram_id' => $testTelegramId]);
echo "👤 Пользователь найден: " . ($checkUser['success'] ? 'Да' : 'Нет') . "\n";

// Проверяем автомобиль
$checkCar = _CheckCarInDbAction::handle(['plate_number' => $testPlateNumber]);
echo "🚗 Автомобиль найден: " . ($checkCar['success'] ? 'Да' : 'Нет') . "\n";

echo "\n�� ТЕСТ ЗАВЕРШЕН!\n"; 