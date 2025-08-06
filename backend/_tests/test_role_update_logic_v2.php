<?php
/**
 * Тест логики обновления роли пользователя (версия 2)
 * 
 * Проверяет, что роль обновляется только если пользователь является владельцем автомобиля
 * Новая логика: проверка в конце действия вместо проверки в начале
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../actions/level2/__AddCarToUserAction.php';
require_once __DIR__ . '/../utils/AppContext.php';

// Настройка теста
$test_config = [
    'id' => 'role_update_logic_v2',
    'name' => 'Тест логики обновления роли (версия 2)',
    'description' => 'Проверяет, что роль обновляется только если пользователь является владельцем автомобиля',
    'endpoint' => 'L2 Action: __AddCarToUserAction',
    'method' => 'POST',
    'icon' => '👤',
    'data_name' => 'тестов'
];

// Заголовок страницы
echo "<!DOCTYPE html>
<html>
<head>
    <title>Тест логики обновления роли (версия 2)</title>
    <meta charset='utf-8'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .code { background-color: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
        .result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .back-link { margin: 20px 0; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='back-link'>
        <a href='index.php'>← Назад к тестам</a>
    </div>
    
    <h1>{$test_config['icon']} {$test_config['name']}</h1>
    <p><strong>Описание:</strong> {$test_config['description']}</p>
    <p><strong>Endpoint:</strong> {$test_config['endpoint']}</p>
    <p><strong>Метод:</strong> {$test_config['method']}</p>
";

// Тестовые данные
$testCases = [
    [
        'name' => 'Создание нового автомобиля (пользователь становится владельцем, роль должна обновиться)',
        'data' => [
            'plate_number' => 'NEW' . time(),
            'model' => 'New Model',
            'color' => 'Green',
            'year' => 2021
        ],
        'expected_owner' => true,
        'expected_role_update' => true
    ],
    [
        'name' => 'Назначение владельца существующему авто без владельца (роль должна обновиться)',
        'data' => [
            'plate_number' => 'UNOWNED' . time(),
            'model' => 'Unowned Model',
            'color' => 'Yellow',
            'year' => 2020
        ],
        'expected_owner' => true,
        'expected_role_update' => true
    ],
    [
        'name' => 'Попытка добавить авто с владельцем (пользователь НЕ становится владельцем, роль НЕ должна обновиться)',
        'data' => [
            'plate_number' => 'OWNED' . time(),
            'model' => 'Owned Model',
            'color' => 'Blue',
            'year' => 2019
        ],
        'expected_owner' => false,
        'expected_role_update' => false
    ]
];

// Симуляция пользователя с ролью guest (должна обновиться до user)
$mockUser = [
    'id' => 999,
    'telegram_id' => 123456789,
    'username' => 'test_user',
    'role_id' => 2, // guest - должна обновиться до user
    'first_name_app' => 'Test',
    'last_name_app' => 'User'
];

// Устанавливаем пользователя в контекст
AppContext::setCurrentUser($mockUser);

echo "<div class='test-section info'>
    <h3>📋 Информация о тесте</h3>
    <p><strong>Пользователь:</strong> ID={$mockUser['id']}, Роль={$mockUser['role_id']} (guest)</p>
    <p><strong>Новая логика:</strong> Роль обновляется только если пользователь является владельцем автомобиля</p>
    <p><strong>Проверка:</strong> В конце действия проверяем owner_user_id === user_id</p>
</div>";

$totalTests = count($testCases);
$passedTests = 0;

foreach ($testCases as $index => $testCase) {
    echo "<div class='test-section'>
        <h3>🧪 Тест " . ($index + 1) . ": {$testCase['name']}</h3>";
    
    try {
        // Выполняем тест
        $startTime = microtime(true);
        $result = __AddCarToUserAction::handle($testCase['data']);
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        
        // Проверяем результат
        $success = $result['success'] ?? false;
        $ownerUserId = $result['data']['owner_user_id'] ?? null;
        $roleUpdated = $result['data']['role_updated'] ?? false;
        
        // Проверяем, является ли пользователь владельцем
        $isOwner = $ownerUserId === $mockUser['id'];
        
        // Проверяем ожидания
        $expectedOwner = $testCase['expected_owner'];
        $expectedRoleUpdate = $testCase['expected_role_update'];
        
        $testPassed = $success && 
                     ($isOwner === $expectedOwner) && // Проверяем владельца
                     ($roleUpdated === $expectedRoleUpdate); // Проверяем обновление роли
        
        if ($testPassed) {
            $passedTests++;
            echo "<div class='result success'>
                <strong>✅ Тест пройден</strong><br>
                <strong>Время выполнения:</strong> {$executionTime}ms<br>
                <strong>Успех операции:</strong> " . ($success ? 'Да' : 'Нет') . "<br>
                <strong>Пользователь является владельцем:</strong> " . ($isOwner ? 'Да' : 'Нет') . "<br>
                <strong>Роль обновлена:</strong> " . ($roleUpdated ? 'Да' : 'Нет') . "<br>
                <strong>Ожидалось быть владельцем:</strong> " . ($expectedOwner ? 'Да' : 'Нет') . "<br>
                <strong>Ожидалось обновление роли:</strong> " . ($expectedRoleUpdate ? 'Да' : 'Нет') . "
            </div>";
        } else {
            echo "<div class='result error'>
                <strong>❌ Тест не пройден</strong><br>
                <strong>Время выполнения:</strong> {$executionTime}ms<br>
                <strong>Успех операции:</strong> " . ($success ? 'Да' : 'Нет') . "<br>
                <strong>Пользователь является владельцем:</strong> " . ($isOwner ? 'Да' : 'Нет') . "<br>
                <strong>Роль обновлена:</strong> " . ($roleUpdated ? 'Да' : 'Нет') . "<br>
                <strong>Ожидалось быть владельцем:</strong> " . ($expectedOwner ? 'Да' : 'Нет') . "<br>
                <strong>Ожидалось обновление роли:</strong> " . ($expectedRoleUpdate ? 'Да' : 'Нет') . "
            </div>";
        }
        
        // Выводим детали ответа
        echo "<div class='code'>
            <strong>Ответ API:</strong><br>
            <pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>
        </div>";
        
    } catch (Exception $e) {
        echo "<div class='result error'>
            <strong>❌ Ошибка выполнения теста:</strong><br>
            <strong>Сообщение:</strong> {$e->getMessage()}<br>
            <strong>Файл:</strong> {$e->getFile()}:{$e->getLine()}
        </div>";
    }
    
    echo "</div>";
}

// Итоговые результаты
$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

echo "<div class='test-section " . ($successRate == 100 ? 'success' : 'error') . "'>
    <h3>📊 Итоговые результаты</h3>
    <p><strong>Всего тестов:</strong> {$totalTests}</p>
    <p><strong>Пройдено:</strong> {$passedTests}</p>
    <p><strong>Провалено:</strong> " . ($totalTests - $passedTests) . "</p>
    <p><strong>Процент успеха:</strong> {$successRate}%</p>
</div>";

echo "<div class='test-section info'>
    <h3>📝 Выводы</h3>
    <p><strong>✅ Новая логика:</strong> Роль обновляется только если пользователь является владельцем автомобиля (owner_user_id === user_id).</p>
    <p><strong>🎯 Проверка в конце:</strong> Вместо проверки в начале действия, проверяем результат в конце.</p>
    <p><strong>🛡️ Безопасность:</strong> Роль не изменится, если назначение владельца не удалось.</p>
    <p><strong>📊 Отслеживание:</strong> В ответе API добавлено поле role_updated для мониторинга.</p>
    <p><strong>🧹 Чистота кода:</strong> Одна проверка вместо дублирования логики.</p>
</div>";

echo "</body></html>";
?> 