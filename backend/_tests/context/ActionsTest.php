<?php

require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../actions/level2/__AddCarToUserAction.php';
require_once __DIR__ . '/../../actions/level2/__SearchCarAction.php';
require_once __DIR__ . '/../../actions/level2/__DropBusinessCardAction.php';

/**
 * 🧪 Тест для обновленных Actions с глобальным контекстом
 * 
 * Проверяет работу L2 Actions с AppContext:
 * - Получение пользователя из контекста
 * - Обработка ошибок при отсутствии пользователя
 * - Корректная работа с данными
 */
class ActionsTest
{
    /**
     * Запустить все тесты
     */
    public static function runAllTests()
    {
        echo "🧪 Запуск тестов Actions с глобальным контекстом...\n\n";
        
        $tests = [
            'testAddCarToUserWithContext',
            'testSearchCarWithContext',
            'testDropBusinessCardWithContext',
            'testActionsWithoutUser'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                self::$test();
                echo "✅ {$test}: PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ {$test}: FAILED - {$e->getMessage()}\n";
                $failed++;
            }
        }
        
        echo "\n📊 Результаты тестов:\n";
        echo "✅ Успешно: {$passed}\n";
        echo "❌ Ошибок: {$failed}\n";
        echo "📈 Всего: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    }

    /**
     * Тест __AddCarToUserAction с контекстом
     */
    private static function testAddCarToUserWithContext()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Устанавливаем пользователя в контекст
        $testUser = [
            'id' => 123,
            'telegram_id' => 456789,
            'first_name_tg' => 'Иван',
            'last_name_tg' => 'Иванов',
            'username' => 'ivan_user',
            'role' => 'member'
        ];
        AppContext::setCurrentUser($testUser);
        
        // Тестируем Action
        $data = [
            'plate_number' => 'A123BC77'
        ];
        
        $result = __AddCarToUserAction::handle($data);
        
        // Проверяем, что Action получил пользователя из контекста
        if (!$result['success']) {
            // Это нормально, так как Action может не найти автомобиль в БД
            // Главное, что он не вернул ошибку NO_USER
            if (isset($result['error']['code']) && $result['error']['code'] === 'NO_USER') {
                throw new Exception('Action не получил пользователя из контекста');
            }
        }
        
        echo "  - __AddCarToUserAction корректно работает с контекстом\n";
    }

    /**
     * Тест __SearchCarAction с контекстом
     */
    private static function testSearchCarWithContext()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Устанавливаем пользователя в контекст
        $testUser = [
            'id' => 456,
            'telegram_id' => 789123,
            'first_name_tg' => 'Петр',
            'last_name_tg' => 'Петров',
            'username' => 'petr_user',
            'role' => 'member'
        ];
        AppContext::setCurrentUser($testUser);
        
        // Тестируем Action
        $data = [
            'plate_number' => 'B456DE78'
        ];
        
        $result = __SearchCarAction::handle($data);
        
        // Проверяем, что Action получил пользователя из контекста
        if (!$result['success']) {
            // Это нормально, так как Action может не найти автомобиль в БД
            // Главное, что он не вернул ошибку NO_USER
            if (isset($result['error']['code']) && $result['error']['code'] === 'NO_USER') {
                throw new Exception('Action не получил пользователя из контекста');
            }
        }
        
        echo "  - __SearchCarAction корректно работает с контекстом\n";
    }

    /**
     * Тест __DropBusinessCardAction с контекстом
     */
    private static function testDropBusinessCardWithContext()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Устанавливаем пользователя в контекст
        $testUser = [
            'id' => 789,
            'telegram_id' => 123456,
            'first_name_tg' => 'Сергей',
            'last_name_tg' => 'Сергеев',
            'username' => 'sergey_user',
            'role' => 'member'
        ];
        AppContext::setCurrentUser($testUser);
        
        // Тестируем Action
        $data = [
            'plate_number' => 'C789FG90'
        ];
        
        $result = __DropBusinessCardAction::handle($data);
        
        // Проверяем, что Action получил пользователя из контекста
        if (!$result['success']) {
            // Это нормально, так как Action может не найти автомобиль в БД
            // Главное, что он не вернул ошибку NO_USER
            if (isset($result['error']['code']) && $result['error']['code'] === 'NO_USER') {
                throw new Exception('Action не получил пользователя из контекста');
            }
        }
        
        echo "  - __DropBusinessCardAction корректно работает с контекстом\n";
    }

    /**
     * Тест Actions без пользователя в контексте
     */
    private static function testActionsWithoutUser()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Проверяем, что Actions возвращают ошибку NO_USER
        $data = [
            'plate_number' => 'TEST123'
        ];
        
        // Тестируем __AddCarToUserAction
        $result = __AddCarToUserAction::handle($data);
        if ($result['success'] || $result['error']['code'] !== 'NO_USER') {
            throw new Exception('Action должен вернуть ошибку NO_USER при отсутствии пользователя');
        }
        
        // Тестируем __SearchCarAction
        $result = __SearchCarAction::handle($data);
        if ($result['success'] || $result['error']['code'] !== 'NO_USER') {
            throw new Exception('Action должен вернуть ошибку NO_USER при отсутствии пользователя');
        }
        
        // Тестируем __DropBusinessCardAction
        $result = __DropBusinessCardAction::handle($data);
        if ($result['success'] || $result['error']['code'] !== 'NO_USER') {
            throw new Exception('Action должен вернуть ошибку NO_USER при отсутствии пользователя');
        }
        
        echo "  - Actions корректно обрабатывают отсутствие пользователя\n";
    }
}

// Запуск тестов, если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    ActionsTest::runAllTests();
} 