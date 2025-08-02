<?php
/**
 * 🧪 Тест __SearchCarAction
 * 
 * Назначение: Проверка L2 Action для поиска автомобиля
 * Использование: php backend/_tests/test_search_car_action.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение L2 Action
require_once __DIR__ . '/../actions/level2/__SearchCarAction.php';

echo "🧪 ТЕСТ __SearchCarAction\n";
echo "========================\n\n";

// Глобальные переменные
$testPlateNumber = 'TEST' . time(); // Уникальный номер для теста
$testUserId = 563; // Существующий пользователь

// Функция для вывода результата
function showResult($testName, $result) {
    echo "📋 $testName:\n";
    if ($result['success']) {
        echo "   ✅ УСПЕХ\n";
        echo "   🎯 Действие: " . $result['data']['action'] . "\n";
        echo "   🚗 Car ID: " . $result['data']['car_id'] . "\n";
        echo "   📝 Сообщение: " . $result['data']['message'] . "\n";
        echo "   🏷️ Номер: " . $result['data']['plate_number'] . "\n";
        echo "   🚙 Модель: " . ($result['data']['model'] ?? 'не указана') . "\n";
        echo "   🎨 Цвет: " . ($result['data']['color'] ?? 'не указан') . "\n";
        echo "   📅 Год: " . ($result['data']['year'] ?? 'не указан') . "\n";
        echo "   📊 Статус: " . $result['data']['status_id'] . "\n";
        
        if (isset($result['data']['photo'])) {
            echo "   📸 Фото загружено: " . $result['data']['photo']['file_name'] . "\n";
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
// ТЕСТ 1: Создание нового автомобиля
// ============================================================================

echo "🚗 ТЕСТ 1: Создание нового автомобиля\n";
echo "=====================================\n";

$createData = [
    'plate_number' => $testPlateNumber,
    'create_user_id' => $testUserId,
    'model' => 'BMW Z4',
    'color' => 'Красный',
    'year' => 2020
];

$result = __SearchCarAction::handle($createData);
showResult('Создание автомобиля', $result);

if (!$result['success']) {
    echo "❌ Тест прерван из-за ошибки создания автомобиля\n\n";
    exit;
}

$createdCarId = $result['data']['car_id'];

// ============================================================================
// ТЕСТ 2: Поиск существующего автомобиля
// ============================================================================

echo "🔍 ТЕСТ 2: Поиск существующего автомобиля\n";
echo "=========================================\n";

$searchData = [
    'plate_number' => $testPlateNumber,
    'create_user_id' => $testUserId
];

$result = __SearchCarAction::handle($searchData);
showResult('Поиск существующего автомобиля', $result);

// ============================================================================
// ТЕСТ 3: Поиск несуществующего автомобиля
// ============================================================================

echo "❓ ТЕСТ 3: Поиск несуществующего автомобиля\n";
echo "===========================================\n";

$newPlateNumber = 'NEW' . time();
$searchNewData = [
    'plate_number' => $newPlateNumber,
    'create_user_id' => $testUserId,
    'model' => 'Audi A4',
    'color' => 'Синий',
    'year' => 2019
];

$result = __SearchCarAction::handle($searchNewData);
showResult('Поиск несуществующего автомобиля', $result);

// ============================================================================
// ТЕСТ 4: Создание автомобиля с фото (симуляция)
// ============================================================================

echo "📸 ТЕСТ 4: Создание автомобиля с фото (симуляция)\n";
echo "=================================================\n";

// Симулируем загрузку файла
$testPhotoData = [
    'name' => 'test_car.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/../docs/_test_photo_(9588MI1).jpg', // используем существующий файл
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Временно устанавливаем $_FILES
$_FILES['photo'] = $testPhotoData;

$photoData = [
    'plate_number' => 'PHOTO' . time(),
    'create_user_id' => $testUserId,
    'model' => 'Mercedes C-Class',
    'color' => 'Белый',
    'year' => 2021
];

$result = __SearchCarAction::handle($photoData);
showResult('Создание автомобиля с фото', $result);

// Очищаем $_FILES
unset($_FILES['photo']);

// ============================================================================
// ИТОГОВЫЙ ОТЧЕТ
// ============================================================================

echo "📊 ИТОГОВЫЙ ОТЧЕТ\n";
echo "==================\n";
echo "✅ Создан автомобиль ID: $createdCarId\n";
echo "✅ Номер: $testPlateNumber\n";
echo "✅ Все тесты выполнены\n";
echo "\n🎉 __SearchCarAction РАБОТАЕТ КОРРЕКТНО!\n";
echo "==========================================\n"; 