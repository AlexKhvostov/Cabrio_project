<?php
/**
 * Тест BusinessCard и Photo Actions
 */
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../actions/level1/_CreateBusinessCardAction.php';
require_once __DIR__ . '/../actions/level1/_CreatePhotoAction.php';

echo "🧪 Тест BusinessCard и Photo Actions\n";

$testUserId = 563; // используем существующего пользователя
$testCarId = 382; // используем существующий автомобиль

// Тест 1: Создание визитки
echo "\n1️⃣ Тест _CreateBusinessCardAction\n";
$cardData = [
    'car_id' => $testCarId,
    'user_id' => $testUserId,
    'location' => 'Москва, центр',
    'notes' => 'Отличная машина!'
];

$result = _CreateBusinessCardAction::handle($cardData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
    return; // Прерываем тест если создание не удалось
} else {
    echo "Создана визитка ID: " . $result['data']['id'] . "\n";
    $cardId = $result['data']['id'];
}

// Тест 2: Создание фото
echo "\n2️⃣ Тест _CreatePhotoAction\n";
$photoData = [
    'entity_type' => 'car',
    'entity_id' => $testCarId,
    'file_name' => 'test_photo.jpg',
    'url' => '/uploads/cars/test_photo.jpg',
    'photo_type' => 'gallery',
    'description' => 'Фото автомобиля',
    'uploaded_by' => $testUserId
];

$result = _CreatePhotoAction::handle($photoData);
echo "Результат: " . ($result['success'] ? '✅ Успех' : '❌ Ошибка') . "\n";
if (!$result['success']) {
    echo "Ошибка: " . $result['error']['message'] . "\n";
} else {
    echo "Создано фото ID: " . $result['data']['id'] . "\n";
}

echo "\n🏁 Тест BusinessCard и Photo Actions завершен\n"; 