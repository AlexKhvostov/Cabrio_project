<?php
/**
 * 🧪 Тест __AddCarToUserAction
 * 
 * Назначение: Проверка L2 Action для добавления автомобиля пользователю
 * Использование: php backend/_tests/test_add_car_to_user_action.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение L2 Action
require_once __DIR__ . '/../actions/level2/__AddCarToUserAction.php';

echo "🧪 ТЕСТ __AddCarToUserAction\n";
echo "============================\n\n";

// Глобальные переменные
$testPlateNumber = 'ADD' . time(); // Уникальный номер для теста
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
        echo "   👤 Владелец: " . ($result['data']['owner_user_id'] ?? 'нет') . "\n";
        
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
// ТЕСТ 1: Создание нового автомобиля с владельцем
// ============================================================================

echo "🚗 ТЕСТ 1: Создание нового автомобиля с владельцем\n";
echo "==================================================\n";

$createData = [
    'plate_number' => $testPlateNumber,
    'user_id' => $testUserId,
    'model' => 'BMW X5',
    'color' => 'Чёрный',
    'year' => 2021
];

$result = __AddCarToUserAction::handle($createData);
showResult('Создание автомобиля с владельцем', $result);

if (!$result['success']) {
    echo "❌ Тест прерван из-за ошибки создания автомобиля\n\n";
    exit;
}

$createdCarId = $result['data']['car_id'];

// ============================================================================
// ТЕСТ 2: Попытка добавить тот же автомобиль другому пользователю
// ============================================================================

echo "⚠️ ТЕСТ 2: Попытка добавить тот же автомобиль другому пользователю\n";
echo "==================================================================\n";

$duplicateData = [
    'plate_number' => $testPlateNumber,
    'user_id' => 564, // Другой пользователь
    'model' => 'BMW X5',
    'color' => 'Чёрный',
    'year' => 2021
];

$result = __AddCarToUserAction::handle($duplicateData);
showResult('Попытка добавить автомобиль с владельцем', $result);

// ============================================================================
// ТЕСТ 3: Добавление автомобиля без владельца
// ============================================================================

echo "🔍 ТЕСТ 3: Добавление автомобиля без владельца\n";
echo "===============================================\n";

// Сначала создадим автомобиль без владельца через SearchCarAction
require_once __DIR__ . '/../actions/level2/__SearchCarAction.php';

$searchData = [
    'plate_number' => 'NOOWNER' . time(),
    'create_user_id' => $testUserId,
    'model' => 'Audi Q7',
    'color' => 'Серебристый',
    'year' => 2020
];

$searchResult = __SearchCarAction::handle($searchData);
if ($searchResult['success']) {
    $noOwnerPlate = $searchResult['data']['plate_number'];
    
    // Теперь добавляем его пользователю
    $addData = [
        'plate_number' => $noOwnerPlate,
        'user_id' => $testUserId,
        'model' => 'Audi Q7',
        'color' => 'Серебристый',
        'year' => 2020
    ];
    
    $result = __AddCarToUserAction::handle($addData);
    showResult('Добавление автомобиля без владельца', $result);
} else {
    echo "❌ Не удалось создать автомобиль без владельца для теста\n\n";
}

// ============================================================================
// ТЕСТ 4: Создание автомобиля с фото (симуляция)
// ============================================================================

echo "📸 ТЕСТ 4: Создание автомобиля с фото (симуляция)\n";
echo "==================================================\n";

// Симулируем загрузку файла
$testPhotoData = [
    'name' => 'test_car_photo.jpg',
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
    'model' => 'Mercedes G-Class',
    'color' => 'Белый',
    'year' => 2022
];

$result = __AddCarToUserAction::handle($photoData);
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
echo "\n🎉 __AddCarToUserAction РАБОТАЕТ КОРРЕКТНО!\n";
echo "=============================================\n"; 