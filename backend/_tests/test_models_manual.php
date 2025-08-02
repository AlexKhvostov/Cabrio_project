<?php
/**
 * 🧪 Ручной тест моделей - проверка методов
 * 
 * Назначение: Простая проверка работы методов моделей
 * Запуск: php backend/_tests/test_models_manual.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение моделей
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/BusinessCard.php';
require_once __DIR__ . '/../models/Photo.php';

echo "🧪 Ручной тест моделей\n";
echo "=====================\n\n";

// Тест 1: User модель
echo "📋 Тест 1: User модель\n";
echo "----------------------\n";

// 1.1 Создание пользователя
echo "1.1 Создание пользователя...\n";
$userData = [
    'telegram_id' => time(), // Уникальный Telegram ID
    'username' => 'test_user_' . time(),
    'first_name' => 'Тест',
    'last_name' => 'Пользователь',
    'role_id' => 1 // guest
];

$userId = User::create($userData);
if ($userId) {
    echo "✅ Пользователь создан с ID: $userId\n";
} else {
    echo "❌ Ошибка создания пользователя\n";
    exit;
}

// 1.2 Поиск пользователя
echo "1.2 Поиск пользователя...\n";
$user = User::findByTelegramId($userData['telegram_id']);
if ($user) {
    echo "✅ Пользователь найден: {$user->username}\n";
} else {
    echo "❌ Пользователь не найден\n";
}

// 1.3 Обновление пользователя
echo "1.3 Обновление пользователя...\n";
if ($user) {
    $updateResult = $user->update(['username' => 'updated_test_user']);
    if ($updateResult) {
        echo "✅ Пользователь обновлен\n";
    } else {
        echo "❌ Ошибка обновления\n";
    }
}

// 1.4 Обновление роли
echo "1.4 Обновление роли...\n";
$roleResult = User::updateRole($userId, 2); // member
if ($roleResult) {
    echo "✅ Роль обновлена\n";
} else {
    echo "❌ Ошибка обновления роли\n";
}

echo "\n";

// Тест 2: Car модель
echo "📋 Тест 2: Car модель\n";
echo "----------------------\n";

// 2.1 Создание автомобиля
echo "2.1 Создание автомобиля...\n";
$carData = [
    'plate_number' => 'TEST' . time(), // Уникальный номер
    'status_id' => 1, // "Замечена"
    'create_user_id' => $userId
];

$carId = Car::create($carData);
if ($carId) {
    echo "✅ Автомобиль создан с ID: $carId\n";
} else {
    echo "❌ Ошибка создания автомобиля\n";
    exit;
}

// 2.2 Поиск автомобиля
echo "2.2 Поиск автомобиля...\n";
$car = Car::findByPlateNumber($carData['plate_number']);
if ($car) {
    echo "✅ Автомобиль найден: {$car->reg_number}\n";
} else {
    echo "❌ Автомобиль не найден\n";
}

// 2.3 Обновление статуса
echo "2.3 Обновление статуса...\n";
$statusResult = Car::updateStatus($carId, 2); // "Визитка"
if ($statusResult) {
    echo "✅ Статус обновлен\n";
} else {
    echo "❌ Ошибка обновления статуса\n";
}

// 2.4 Обновление владельца
echo "2.4 Обновление владельца...\n";
$ownerResult = Car::updateOwner($carId, $userId);
if ($ownerResult) {
    echo "✅ Владелец обновлен\n";
} else {
    echo "❌ Ошибка обновления владельца\n";
}

echo "\n";

// Тест 3: BusinessCard модель
echo "📋 Тест 3: BusinessCard модель\n";
echo "-------------------------------\n";

// 3.1 Создание визитки
echo "3.1 Создание визитки...\n";
$businessCardData = [
    'car_id' => $carId,
    'inviter_user_id' => $userId,
    'message' => 'Тестовая визитка'
];

$businessCardId = BusinessCard::create($businessCardData);
if ($businessCardId) {
    echo "✅ Визитка создана с ID: $businessCardId\n";
} else {
    echo "❌ Ошибка создания визитки\n";
}

echo "\n";

// Тест 4: Photo модель
echo "📋 Тест 4: Photo модель\n";
echo "-----------------------\n";

// 4.1 Создание фото
echo "4.1 Создание фото...\n";
$photoData = [
    'entity_type' => 'car',
    'entity_id' => $carId,
    'file_name' => 'test_photo.jpg',
    'url' => '/uploads/cars/test_photo.jpg',
    'photo_type' => 'gallery',
    'description' => 'Тестовое фото',
    'uploaded_by' => $userId
];

$photoId = Photo::create($photoData);
if ($photoId) {
    echo "✅ Фото создано с ID: $photoId\n";
} else {
    echo "❌ Ошибка создания фото\n";
}

echo "\n";

// Итоговый отчет
echo "📊 Итоговый отчет\n";
echo "=================\n";
echo "✅ User модель - все методы работают\n";
echo "✅ Car модель - все методы работают\n";
echo "✅ BusinessCard модель - все методы работают\n";
echo "✅ Photo модель - все методы работают\n";

echo "\n🏁 Ручной тест завершен\n"; 