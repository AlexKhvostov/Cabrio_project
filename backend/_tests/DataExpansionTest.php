<?php
/**
 * DataExpansionTest — тест для проверки развертывания данных в API ответах
 * 
 * Проверяет, что API возвращает развернутые данные вместо простых ID
 * согласно стандартам CONVENTIONS.md п.6
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/ReferenceData.php';
require_once __DIR__ . '/../utils/ExpandHelper.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/BusinessCard.php';

class DataExpansionTest
{
    /**
     * Обработка POST запросов от HTML интерфейса
     */
    public static function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'testReferenceData':
                    self::testReferenceDataMethods();
                    break;
                case 'testExpandHelper':
                    self::testExpandHelperMethods();
                    break;
                case 'testModels':
                    self::testModelMethods();
                    break;
                default:
                    echo "Неизвестное действие: $action";
            }
        } else {
            // Запуск всех тестов при прямом вызове
            self::runAllTests();
        }
    }
    
    /**
     * Запустить все тесты развертывания данных
     */
    public static function runAllTests()
    {
        echo "🧪 Запуск тестов развертывания данных...\n\n";
        
        $tests = [
            'testUserDataExpansion',
            'testCarDataExpansion', 
            'testBusinessCardDataExpansion',
            'testReferenceDataMethods',
            'testExpandHelperMethods'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                $result = self::$test();
                if ($result) {
                    echo "✅ $test - ПРОЙДЕН\n";
                    $passed++;
                } else {
                    echo "❌ $test - ПРОВАЛЕН\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ $test - ОШИБКА: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 Результаты тестов:\n";
        echo "✅ Пройдено: $passed\n";
        echo "❌ Провалено: $failed\n";
        echo "📈 Всего: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    }
    
    /**
     * Тест методов ReferenceData
     */
    public static function testReferenceDataMethods()
    {
        echo "📚 Тестирование ReferenceData...\n\n";
        
        $tests = [
            'testCarStatusDetails',
            'testUserRoleDetails',
            'testValidationMethods',
            'testMappingMethods'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                $result = self::$test();
                if ($result) {
                    echo "✅ $test - ПРОЙДЕН\n";
                    $passed++;
                } else {
                    echo "❌ $test - ПРОВАЛЕН\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ $test - ОШИБКА: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 ReferenceData результаты:\n";
        echo "✅ Пройдено: $passed\n";
        echo "❌ Провалено: $failed\n";
        
        return $failed === 0;
    }
    
    /**
     * Тест методов ExpandHelper
     */
    public static function testExpandHelperMethods()
    {
        echo "🔄 Тестирование ExpandHelper...\n\n";
        
        $tests = [
            'testUserDataExpansion',
            'testCarDataExpansion',
            'testBusinessCardDataExpansion',
            'testAutoExpand',
            'testNeedsExpansion'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                $result = self::$test();
                if ($result) {
                    echo "✅ $test - ПРОЙДЕН\n";
                    $passed++;
                } else {
                    echo "❌ $test - ПРОВАЛЕН\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ $test - ОШИБКА: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 ExpandHelper результаты:\n";
        echo "✅ Пройдено: $passed\n";
        echo "❌ Провалено: $failed\n";
        
        return $failed === 0;
    }
    
    /**
     * Тест методов моделей
     */
    public static function testModelMethods()
    {
        echo "🏗️ Тестирование моделей...\n\n";
        
        $tests = [
            'testUserModelMethods',
            'testCarModelMethods',
            'testBusinessCardModelMethods'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                $result = self::$test();
                if ($result) {
                    echo "✅ $test - ПРОЙДЕН\n";
                    $passed++;
                } else {
                    echo "❌ $test - ПРОВАЛЕН\n";
                    $failed++;
                }
            } catch (Exception $e) {
                echo "❌ $test - ОШИБКА: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 Модели результаты:\n";
        echo "✅ Пройдено: $passed\n";
        echo "❌ Провалено: $failed\n";
        
        return $failed === 0;
    }
    
    // ========================================
    // ТЕСТЫ REFERENCEDATA
    // ========================================
    
    private static function testCarStatusDetails()
    {
        $statusDetails = ReferenceData::getCarStatusDetails(2);
        return $statusDetails && 
               $statusDetails['code'] === 'business_card' &&
               $statusDetails['name'] === 'Визитка';
    }
    
    private static function testUserRoleDetails()
    {
        $roleDetails = ReferenceData::getUserRoleDetails(4);
        return $roleDetails && 
               $roleDetails['code'] === 'member' &&
               $roleDetails['name'] === 'Участник';
    }
    
    private static function testValidationMethods()
    {
        return ReferenceData::isValidCarStatus(2) &&
               ReferenceData::isValidUserRole(4) &&
               !ReferenceData::isValidCarStatus(999) &&
               !ReferenceData::isValidUserRole(999);
    }
    
    private static function testMappingMethods()
    {
        return ReferenceData::getCarStatusCode(2) === 'business_card' &&
               ReferenceData::getUserRoleCode(4) === 'member' &&
               ReferenceData::getCarStatusId('business_card') === 2 &&
               ReferenceData::getUserRoleId('member') === 4;
    }
    
    // ========================================
    // ТЕСТЫ EXPANDHELPER
    // ========================================
    
    private static function testUserDataExpansion()
    {
        $userData = [
            'id' => 1,
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'role_id' => 4
        ];
        
        $expanded = ExpandHelper::expandUserData($userData);
        
        return isset($expanded['role']) &&
               !isset($expanded['role_id']) &&
               $expanded['role']['code'] === 'member';
    }
    
    private static function testCarDataExpansion()
    {
        $carData = [
            'id' => 1,
            'reg_number' => 'A123BC',
            'model' => 'BMW Z4',
            'status_id' => 2
        ];
        
        $expanded = ExpandHelper::expandCarData($carData);
        
        return isset($expanded['status']) &&
               !isset($expanded['status_id']) &&
               $expanded['status']['code'] === 'business_card';
    }
    
    private static function testBusinessCardDataExpansion()
    {
        $cardData = [
            'id' => 1,
            'car_id' => 1,
            'user_id' => 1,
            'location' => 'Москва'
        ];
        
        $expanded = ExpandHelper::expandBusinessCardData($cardData);
        
        return isset($expanded['car']) &&
               isset($expanded['user']) &&
               !isset($expanded['car_id']) &&
               !isset($expanded['user_id']);
    }
    
    private static function testAutoExpand()
    {
        $carData = ['reg_number' => 'A123BC', 'status_id' => 1];
        $expanded = ExpandHelper::autoExpand($carData);
        
        return isset($expanded['status']);
    }
    
    private static function testNeedsExpansion()
    {
        $carData = ['reg_number' => 'A123BC', 'status_id' => 1];
        $simpleData = ['name' => 'test'];
        
        return ExpandHelper::needsExpansion($carData) &&
               !ExpandHelper::needsExpansion($simpleData);
    }
    
    // ========================================
    // ТЕСТЫ МОДЕЛЕЙ
    // ========================================
    
    private static function testUserModelMethods()
    {
        // Проверяем, что методы с развернутыми данными существуют
        return method_exists('User', 'findByIdWithDetails') &&
               method_exists('User', 'findByTelegramIdWithDetails') &&
               method_exists('User', 'createWithDetails') &&
               method_exists('User', 'updateWithDetails');
    }
    
    private static function testCarModelMethods()
    {
        // Проверяем, что методы с развернутыми данными существуют
        return method_exists('Car', 'findByIdWithDetails') &&
               method_exists('Car', 'findByPlateNumberWithDetails') &&
               method_exists('Car', 'createWithDetails') &&
               method_exists('Car', 'updateWithDetails');
    }
    
    private static function testBusinessCardModelMethods()
    {
        // Проверяем, что методы с развернутыми данными существуют
        return method_exists('BusinessCard', 'findByIdWithDetails') &&
               method_exists('BusinessCard', 'createWithDetails');
    }
}

// Обработка запросов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    DataExpansionTest::handleRequest();
} 