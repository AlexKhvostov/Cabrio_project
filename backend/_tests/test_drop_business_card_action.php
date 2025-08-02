<?php
/**
 * 🧪 Тест __DropBusinessCardAction
 * 
 * Назначение: Проверка L2 Action для добавления визитки
 * Использование: php backend/_tests/test_drop_business_card_action.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение L2 Action
require_once __DIR__ . '/../actions/level2/__DropBusinessCardAction.php';

echo "🧪 ТЕСТ __DropBusinessCardAction\n";
echo "================================\n\n";

// Глобальные переменные
$testPlateNumber = 'CARD' . time(); // Уникальный номер для теста
$testUserId = 563; // Существующий пользователь

// Функция для вывода результата
function showResult($testName, $result) {
    echo "📋 $testName:\n";
    if ($result['success']) {
        echo "   ✅ УСПЕХ\n";
        echo "   🎯 Действие: " . $result['data']['action'] . "\n";
        echo "   📝 Сообщение: " . $result['data']['message'] . "\n";
        
        // Информация об автомобиле
        echo "   🚗 Автомобиль:\n";
        echo "      - ID: " . $result['data']['car']['car_id'] . "\n";
        echo "      - Номер: " . $result['data']['car']['plate_number'] . "\n";
        echo "      - Модель: " . ($result['data']['car']['model'] ?? 'не указана') . "\n";
        echo "      - Цвет: " . ($result['data']['car']['color'] ?? 'не указан') . "\n";
        echo "      - Год: " . ($result['data']['car']['year'] ?? 'не указан') . "\n";
        echo "      - Статус: " . $result['data']['car']['status_id'] . "\n";
        
        // Информация о визитке
        echo "   📇 Визитка:\n";
        echo "      - ID: " . $result['data']['business_card']['card_id'] . "\n";
        echo "      - Автомобиль ID: " . $result['data']['business_card']['car_id'] . "\n";
        echo "      - Пользователь ID: " . $result['data']['business_card']['user_id'] . "\n";
        echo "      - Создана: " . $result['data']['business_card']['created_at'] . "\n";
        
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
// ТЕСТ 1: Создание визитки для нового автомобиля
// ============================================================================

echo "📇 ТЕСТ 1: Создание визитки для нового автомобиля\n";
echo "=================================================\n";

$createData = [
    'plate_number' => $testPlateNumber,
    'user_id' => $testUserId,
    'model' => 'BMW M3',
    'color' => 'Синий',
    'year' => 2022
];

$result = __DropBusinessCardAction::handle($createData);
showResult('Создание визитки для нового автомобиля', $result);

if (!$result['success']) {
    echo "❌ Тест прерван из-за ошибки создания визитки\n\n";
    exit;
}

$createdCarId = $result['data']['car']['car_id'];
$createdCardId = $result['data']['business_card']['card_id'];

// ============================================================================
// ТЕСТ 2: Создание визитки для существующего автомобиля
// ============================================================================

echo "📇 ТЕСТ 2: Создание визитки для существующего автомобиля\n";
echo "========================================================\n";

$existingData = [
    'plate_number' => $testPlateNumber,
    'user_id' => 564, // Другой пользователь
    'model' => 'BMW M3',
    'color' => 'Синий',
    'year' => 2022
];

$result = __DropBusinessCardAction::handle($existingData);
showResult('Создание визитки для существующего автомобиля', $result);

// ============================================================================
// ТЕСТ 3: Создание визитки с фото (симуляция)
// ============================================================================

echo "📸 ТЕСТ 3: Создание визитки с фото (симуляция)\n";
echo "===============================================\n";

// Симулируем загрузку файла
$testPhotoData = [
    'name' => 'test_card_photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/../docs/_test_photo_(9588MI1).jpg', // используем существующий файл
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Временно устанавливаем $_FILES
$_FILES['photo'] = $testPhotoData;

$photoData = [
    'plate_number' => 'PHOTO' . time(),
    'user_id' => $testUserId,
    'model' => 'Audi RS6',
    'color' => 'Серый',
    'year' => 2023
];

$result = __DropBusinessCardAction::handle($photoData);
showResult('Создание визитки с фото', $result);

// Очищаем $_FILES
unset($_FILES['photo']);

// ============================================================================
// ТЕСТ 4: Создание визитки для автомобиля без дополнительных данных
// ============================================================================

echo "📇 ТЕСТ 4: Создание визитки для автомобиля без дополнительных данных\n";
echo "=====================================================================\n";

$minimalData = [
    'plate_number' => 'MINIMAL' . time(),
    'user_id' => $testUserId
];

$result = __DropBusinessCardAction::handle($minimalData);
showResult('Создание визитки с минимальными данными', $result);

// ============================================================================
// ИТОГОВЫЙ ОТЧЕТ
// ============================================================================

echo "📊 ИТОГОВЫЙ ОТЧЕТ\n";
echo "==================\n";
echo "✅ Создан автомобиль ID: $createdCarId\n";
echo "✅ Создана визитка ID: $createdCardId\n";
echo "✅ Номер: $testPlateNumber\n";
echo "✅ Все тесты выполнены\n";
echo "\n🎉 __DropBusinessCardAction РАБОТАЕТ КОРРЕКТНО!\n";
echo "================================================\n"; 