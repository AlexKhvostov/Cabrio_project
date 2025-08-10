<?php
/**
 * FullMVPTest — полное тестирование MVP бекенда
 * 
 * Назначение: Проверка всех компонентов системы в рамках MVP
 * - Аутентификация и авторизация
 * - Все API эндпоинты
 * - Работа с базой данных
 * - Обработка ошибок
 * - Логирование
 */

require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/AuthHelper.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../config/sectionGroups.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Car.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/GuideObject.php';
require_once __DIR__ . '/../../models/BusinessCard.php';
require_once __DIR__ . '/../../models/Photo.php';
require_once __DIR__ . '/../../models/Review.php';

class FullMVPTest {
    
    private static $testResults = [];
    private static $testCount = 0;
    private static $passedCount = 0;
    
    public static function runAllTests() {
        echo "🧪 ЗАПУСК ПОЛНОГО ТЕСТИРОВАНИЯ MVP БЕКЕНДА\n";
        echo "================================================\n\n";
        
        // Очищаем результаты
        self::$testResults = [];
        self::$testCount = 0;
        self::$passedCount = 0;
        
        // 1. Тестирование базовой архитектуры
        self::testArchitecture();
        
        // 2. Тестирование аутентификации
        self::testAuthentication();
        
        // 3. Тестирование авторизации
        self::testAuthorization();
        
        // 4. Тестирование моделей
        self::testModels();
        
        // 5. Тестирование API эндпоинтов
        self::testAPIEndpoints();
        
        // 6. Тестирование обработки ошибок
        self::testErrorHandling();
        
        // 7. Тестирование логирования
        self::testLogging();
        
        // Выводим итоговый отчет
        self::printFinalReport();
    }
    
    /**
     * Тестирование базовой архитектуры
     */
    private static function testArchitecture() {
        echo "🏗️ ТЕСТИРОВАНИЕ АРХИТЕКТУРЫ\n";
        echo "-----------------------------\n";
        
        // AppContext
        self::runTest('AppContext создание и очистка', function() {
            AppContext::setCurrentUser(['id' => 1, 'role' => 4]);
            $user = AppContext::getCurrentUser();
            assert($user['id'] === 1, 'Пользователь должен быть установлен');
            
            AppContext::clear();
            $user = AppContext::getCurrentUser();
            assert($user === null, 'Контекст должен быть очищен');
            return true;
        });
        
        // AuthHelper
        self::runTest('AuthHelper извлечение Telegram данных', function() {
            $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '123456789';
            $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Test';
            $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'User';
            
            $data = AuthHelper::extractTelegramData();
            assert($data['telegram_id'] === '123456789', 'Telegram ID должен быть извлечен');
            assert($data['first_name'] === 'Test', 'Имя должно быть извлечено');
            
            return true;
        });
        
        // Roles
        self::runTest('Система ролей', function() {
            assert(Roles::getRoleById(4) === 'member', 'ID 4 должен быть member');
            assert(Roles::getRoleId('admin') === 6, 'admin должен быть ID 6');
            assert(Roles::hasAccessById(6, 4) === true, 'admin должен иметь доступ к member');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование аутентификации
     */
    private static function testAuthentication() {
        echo "🔐 ТЕСТИРОВАНИЕ АУТЕНТИФИКАЦИИ\n";
        echo "--------------------------------\n";
        
        // AuthMiddleware с валидными данными
        self::runTest('AuthMiddleware с валидными данными', function() {
            $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '123456789';
            $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Test';
            $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'User';
            
            $result = AuthMiddleware::process();
            assert($result['success'] === true, 'Аутентификация должна пройти успешно');
            return true;
        });
        
        // AuthMiddleware без данных
        self::runTest('AuthMiddleware без данных', function() {
            unset($_SERVER['HTTP_X_TELEGRAM_USER_ID']);
            unset($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME']);
            unset($_SERVER['HTTP_X_TELEGRAM_LAST_NAME']);
            
            $result = AuthMiddleware::process();
            assert($result['success'] === false, 'Аутентификация должна провалиться');
            return true;
        });
        
        // Публичные эндпоинты
        self::runTest('Публичные эндпоинты', function() {
            $result = AuthMiddleware::processPublic();
            assert($result['success'] === true, 'Публичные эндпоинты должны работать');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование авторизации
     */
    private static function testAuthorization() {
        echo "🔑 ТЕСТИРОВАНИЕ АВТОРИЗАЦИИ\n";
        echo "-----------------------------\n";
        
        // Проверка доступа member
        self::runTest('Доступ member к users.getList', function() {
            AppContext::setCurrentUser(['id' => 1, 'role' => 4]); // member
            $hasAccess = AccessUtils::checkApiAccess('api.users.getList');
            assert($hasAccess === true, 'member должен иметь доступ к users.getList');
            return true;
        });
        
        // Проверка доступа guest
        self::runTest('Доступ guest к users.getList', function() {
            AppContext::setCurrentUser(['id' => 2, 'role' => 2]); // guest
            $hasAccess = AccessUtils::checkApiAccess('api.users.getList');
            assert($hasAccess === false, 'guest не должен иметь доступ к users.getList');
            return true;
        });
        
        // Проверка доступа admin
        self::runTest('Доступ admin к users.create', function() {
            AppContext::setCurrentUser(['id' => 3, 'role' => 6]); // admin
            $hasAccess = AccessUtils::checkApiAccess('api.users.create');
            assert($hasAccess === true, 'admin должен иметь доступ к users.create');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование моделей
     */
    private static function testModels() {
        echo "📊 ТЕСТИРОВАНИЕ МОДЕЛЕЙ\n";
        echo "------------------------\n";
        
        // User модель
        self::runTest('User модель - getAll', function() {
            $users = User::getAll();
            assert(is_array($users), 'getAll должен возвращать массив');
            return true;
        });
        
        // Car модель
        self::runTest('Car модель - getAll', function() {
            $cars = Car::getAll();
            assert(is_array($cars), 'getAll должен возвращать массив');
            return true;
        });
        
        // Event модель
        self::runTest('Event модель - getAll', function() {
            $events = Event::getAll();
            assert(is_array($events), 'getAll должен возвращать массив');
            return true;
        });
        
        // GuideObject модель
        self::runTest('GuideObject модель - getAll', function() {
            $guideObjects = GuideObject::getAll();
            assert(is_array($guideObjects), 'getAll должен возвращать массив');
            return true;
        });
        
        // BusinessCard модель
        self::runTest('BusinessCard модель - getAll', function() {
            $businessCards = BusinessCard::getAll();
            assert(is_array($businessCards), 'getAll должен возвращать массив');
            return true;
        });
        
        // Photo модель
        self::runTest('Photo модель - getAll', function() {
            $photos = Photo::getAll();
            assert(is_array($photos), 'getAll должен возвращать массив');
            return true;
        });
        
        // Review модель
        self::runTest('Review модель - getAll', function() {
            $reviews = Review::getAll();
            assert(is_array($reviews), 'getAll должен возвращать массив');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование API эндпоинтов
     */
    private static function testAPIEndpoints() {
        echo "🌐 ТЕСТИРОВАНИЕ API ЭНДПОИНТОВ\n";
        echo "-------------------------------\n";
        
        // Системные эндпоинты
        self::runTest('GET /api/health', function() {
            $response = self::makeRequest('GET', '/api/health');
            assert($response['status'] === 200, 'Health должен возвращать 200');
            return true;
        });
        
        self::runTest('GET /api/status', function() {
            $response = self::makeRequest('GET', '/api/status');
            assert($response['status'] === 200, 'Status должен возвращать 200');
            return true;
        });
        
        // Пользователи (с авторизацией)
        self::runTest('GET /api/users (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/users', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Users должен возвращать 200 или 403');
            return true;
        });
        
        self::runTest('GET /api/users/profile (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/users/profile', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Profile должен возвращать 200 или 403');
            return true;
        });
        
        // Автомобили
        self::runTest('GET /api/cars (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/cars', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Cars должен возвращать 200 или 403');
            return true;
        });
        
        // События
        self::runTest('GET /api/events (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/events', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Events должен возвращать 200 или 403');
            return true;
        });
        
        // Гид-объекты
        self::runTest('GET /api/guide-objects (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/guide-objects', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Guide objects должен возвращать 200 или 403');
            return true;
        });
        
        // Визитки
        self::runTest('GET /api/business-cards (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/business-cards', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Business cards должен возвращать 200 или 403');
            return true;
        });
        
        // Фото
        self::runTest('GET /api/photos (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/photos', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Photos должен возвращать 200 или 403');
            return true;
        });
        
        // Отзывы
        self::runTest('GET /api/reviews (с авторизацией)', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('GET', '/api/reviews', null, $headers);
            assert($response['status'] === 200 || $response['status'] === 403, 'Reviews должен возвращать 200 или 403');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование обработки ошибок
     */
    private static function testErrorHandling() {
        echo "⚠️ ТЕСТИРОВАНИЕ ОБРАБОТКИ ОШИБОК\n";
        echo "----------------------------------\n";
        
        // Несуществующий маршрут
        self::runTest('404 - несуществующий маршрут', function() {
            $response = self::makeRequest('GET', '/api/nonexistent');
            assert($response['status'] === 404, 'Несуществующий маршрут должен возвращать 404');
            return true;
        });
        
        // Неавторизованный доступ
        self::runTest('401 - неавторизованный доступ', function() {
            $response = self::makeRequest('GET', '/api/users');
            assert($response['status'] === 401, 'Неавторизованный доступ должен возвращать 401');
            return true;
        });
        
        // Недостаточно прав
        self::runTest('403 - недостаточно прав', function() {
            $headers = [
                'X-Telegram-User-Id' => '123456789',
                'X-Telegram-First-Name' => 'Test',
                'X-Telegram-Last-Name' => 'User'
            ];
            $response = self::makeRequest('POST', '/api/users', '{"name":"test"}', $headers);
            assert($response['status'] === 403, 'Недостаточно прав должно возвращать 403');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Тестирование логирования
     */
    private static function testLogging() {
        echo "📝 ТЕСТИРОВАНИЕ ЛОГИРОВАНИЯ\n";
        echo "----------------------------\n";
        
        // Info логирование
        self::runTest('Logger::info', function() {
            $result = Logger::info('Test info message', ['test' => 'data']);
            assert($result === true, 'Logger::info должен возвращать true');
            return true;
        });
        
        // Warning логирование
        self::runTest('Logger::warning', function() {
            $result = Logger::warning('Test warning message', ['test' => 'data']);
            assert($result === true, 'Logger::warning должен возвращать true');
            return true;
        });
        
        // Error логирование
        self::runTest('Logger::error', function() {
            $result = Logger::error('Test error message', ['test' => 'data']);
            assert($result === true, 'Logger::error должен возвращать true');
            return true;
        });
        
        echo "\n";
    }
    
    /**
     * Вспомогательные методы
     */
    private static function runTest($name, $testFunction) {
        self::$testCount++;
        echo "  " . self::$testCount . ". {$name}... ";
        
        try {
            $result = $testFunction();
            if ($result === true) {
                echo "✅ PASSED\n";
                self::$passedCount++;
                self::$testResults[] = ['name' => $name, 'status' => 'PASSED'];
            } else {
                echo "❌ FAILED\n";
                self::$testResults[] = ['name' => $name, 'status' => 'FAILED'];
            }
        } catch (Exception $e) {
            echo "❌ FAILED (Exception: {$e->getMessage()})\n";
            self::$testResults[] = ['name' => $name, 'status' => 'FAILED', 'error' => $e->getMessage()];
        }
    }
    
    private static function makeRequest($method, $route, $body = null, $headers = []) {
        // Симуляция HTTP запроса
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $route;
        $_GET['route'] = $route;
        
        if ($body) {
            $_SERVER['CONTENT_TYPE'] = 'application/json';
            file_put_contents('php://input', $body);
        }
        
        // Устанавливаем заголовки
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
        
        // Захватываем вывод
        ob_start();
        
        try {
            require_once __DIR__ . '/../../routes/api.php';
            $output = ob_get_contents();
        } catch (Exception $e) {
            $output = json_encode(['error' => $e->getMessage()]);
        }
        
        ob_end_clean();
        
        // Парсим ответ
        $response = json_decode($output, true);
        $status = http_response_code();
        
        return [
            'status' => $status,
            'data' => $response,
            'raw' => $output
        ];
    }
    
    private static function printFinalReport() {
        echo "📊 ИТОГОВЫЙ ОТЧЕТ\n";
        echo "==================\n";
        echo "Всего тестов: " . self::$testCount . "\n";
        echo "Пройдено: " . self::$passedCount . "\n";
        echo "Провалено: " . (self::$testCount - self::$passedCount) . "\n";
        echo "Процент успеха: " . round((self::$passedCount / self::$testCount) * 100, 2) . "%\n\n";
        
        if (self::$passedCount === self::$testCount) {
            echo "🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ! MVP БЕКЕНД ГОТОВ К ИСПОЛЬЗОВАНИЮ!\n";
        } else {
            echo "⚠️ ЕСТЬ ПРОБЛЕМЫ, ТРЕБУЕТСЯ ДОРАБОТКА\n";
            
            echo "\nДетали проваленных тестов:\n";
            foreach (self::$testResults as $result) {
                if ($result['status'] === 'FAILED') {
                    echo "  ❌ {$result['name']}";
                    if (isset($result['error'])) {
                        echo " - {$result['error']}";
                    }
                    echo "\n";
                }
            }
        }
    }
}

// Запускаем тесты если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    FullMVPTest::runAllTests();
} 