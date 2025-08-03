<?php
/**
 * Тест проверки роли по умолчанию для новых пользователей
 * Проверяет, что новые пользователи создаются с ролью guest (2)
 */

echo "🧪 Тестирование роли по умолчанию для новых пользователей...\n";

try {
    // Подключаем необходимые файлы
    require_once __DIR__ . '/../utils/AppContext.php';
    require_once __DIR__ . '/../utils/Database.php';
    require_once __DIR__ . '/../utils/Logger.php';
    require_once __DIR__ . '/../models/User.php';
    require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
    
    // Тестируем создание пользователя без указания роли
    $testTelegramId = 999999999; // Уникальный ID для теста
    
    echo "✅ Тестируем создание пользователя без указания роли...\n";
    
    $result = _CreateUserAction::handle([
        'telegram_id' => $testTelegramId,
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser'
    ]);
    
    if ($result['success']) {
        $userData = $result['data'];
        $roleId = $userData['role_id'] ?? $userData['role']['id'];
        
        echo "✅ Пользователь создан успешно!\n";
        echo "   ID: {$userData['id']}\n";
        echo "   Telegram ID: {$userData['telegram_id']}\n";
        echo "   Роль ID: $roleId\n";
        echo "   Роль: {$userData['role']['name']}\n";
        
        if ($roleId == 2) {
            echo "🎉 Тест прошел успешно! Пользователь создан с ролью guest (2)\n";
        } else {
            echo "❌ Тест не прошел! Пользователь создан с ролью ID: $roleId, ожидалось: 2\n";
        }
        
        // Удаляем тестового пользователя
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM users WHERE telegram_id = ?');
        $stmt->execute([$testTelegramId]);
        echo "🗑️ Тестовый пользователь удален\n";
        
    } else {
        echo "❌ Ошибка создания пользователя: " . ($result['error']['message'] ?? 'Неизвестная ошибка') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
} 