<?php
/**
 * Простой тест для проверки работы Actions
 * Запуск: http://localhost/app/backend/_tests/test_actions.php
 */

// Подключаем утилиты
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/ResponseHelper.php';
require_once __DIR__ . '/../utils/Logger.php';

// Подключаем модели
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/CarBrand.php';
require_once __DIR__ . '/../models/Status.php';

// Подключаем Actions
require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
require_once __DIR__ . '/../actions/level1/_CreateCarAction.php';

echo "<h1>Тест Actions CabrioRide</h1>";

try {
    // Проверяем подключение к БД
    $pdo = Database::getInstance();
    echo "<p>✅ Подключение к БД: OK</p>";
    
    // Проверяем справочники
    echo "<h2>Проверка справочников:</h2>";
    
    // Роли
    $roles = $pdo->query('SELECT * FROM ref_roles LIMIT 5')->fetchAll();
    echo "<p>Роли: " . count($roles) . " записей</p>";
    
    // Марки авто
    $carBrands = $pdo->query('SELECT * FROM ref_car_brands LIMIT 5')->fetchAll();
    echo "<p>Марки авто: " . count($carBrands) . " записей</p>";
    
    // Статусы
    $statuses = $pdo->query('SELECT * FROM ref_statuses LIMIT 5')->fetchAll();
    echo "<p>Статусы: " . count($statuses) . " записей</p>";
    
    echo "<h2>Тест создания пользователя:</h2>";
    
    // Тестируем создание пользователя
    $userData = [
        'first_name' => 'Тест',
        'last_name' => 'Пользователь',
        'telegram_id' => 123456789,
        'username' => 'testuser',
        'role' => 'guest',
        'city' => 'Москва',
        'email' => 'test@example.com'
    ];
    
    $result = _CreateUserAction::handle($userData);
    
    if ($result['success']) {
        echo "<p>✅ Пользователь создан: ID = " . $result['data']['id'] . "</p>";
        echo "<pre>" . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        // Тестируем создание автомобиля
        echo "<h2>Тест создания автомобиля:</h2>";
        
        $carData = [
            'car_brand' => 'BMW',
            'model' => 'Z4',
            'color' => 'Красный',
            'year' => 2020,
            'owner_user_id' => $result['data']['id'],
            'status' => 'active'
        ];
        
        $carResult = _CreateCarAction::handle($carData);
        
        if ($carResult['success']) {
            echo "<p>✅ Автомобиль создан: ID = " . $carResult['data']['id'] . "</p>";
            echo "<pre>" . json_encode($carResult['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<p>❌ Ошибка создания автомобиля: " . $carResult['error']['message'] . "</p>";
        }
        
    } else {
        echo "<p>❌ Ошибка создания пользователя: " . $result['error']['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 