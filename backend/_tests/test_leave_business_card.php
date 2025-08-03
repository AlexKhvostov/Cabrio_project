<?php
/**
 * Тест эндпоинта leave-business-card
 * Проверяет исправленную логику сохранения фото
 */

// Имитируем запрос к API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '5625181605';

// Создаем тестовое base64 изображение (1x1 пиксель)
$testBase64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A';

echo "🧪 Тестирование эндпоинта leave-business-card...\n";

try {
    // Подключаем необходимые файлы
    require_once __DIR__ . '/../utils/AppContext.php';
    require_once __DIR__ . '/../utils/Database.php';
    require_once __DIR__ . '/../utils/Logger.php';
    require_once __DIR__ . '/../models/User.php';
    
    // Имитируем пользователя в контексте
    $user = [
        'id' => 622,
        'telegram_id' => 5625181605,
        'username' => 'fotokubikby',
        'first_name_tg' => 'fotokubik.by'
    ];
    AppContext::setCurrentUser($user);
    
    echo "✅ Пользователь установлен в контексте\n";
    
    // Тестируем L3 Action напрямую
    require_once __DIR__ . '/../actions/level3/___LeaveBusinessCardAction.php';
    
    $result = ___LeaveBusinessCardAction::handle([
        'photo' => $testBase64
    ]);
    
    echo "✅ L3 Action выполнен\n";
    echo "Результат: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($result['success']) {
        echo "🎉 Тест прошел успешно!\n";
    } else {
        echo "❌ Тест не прошел: " . ($result['error']['message'] ?? 'Неизвестная ошибка') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
} 