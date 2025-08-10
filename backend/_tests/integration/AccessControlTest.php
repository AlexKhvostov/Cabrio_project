<?php
/**
 * AccessControlTest — тест централизованной системы доступа
 * 
 * Назначение: Проверка работы конфигурации sectionGroups.php
 * и интеграции с контроллерами
 */

require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../../config/sectionGroups.php';

class AccessControlTest {
    
    public static function runAllTests() {
        echo "🧪 Запуск тестов централизованной системы доступа...\n\n";
        
        self::testRolesMapping();
        self::testFunctionRoles();
        self::testAccessUtils();
        self::testControllerIntegration();
        
        echo "\n✅ Все тесты централизованной системы доступа пройдены!\n";
    }
    
    /**
     * Тест маппинга ролей
     */
    private static function testRolesMapping() {
        echo "📋 Тест маппинга ролей...\n";
        
        // Тест ID -> Code
        assert(Roles::getRoleById(1) === 'external', 'ID 1 должен быть external');
        assert(Roles::getRoleById(4) === 'member', 'ID 4 должен быть member');
        assert(Roles::getRoleById(6) === 'admin', 'ID 6 должен быть admin');
        
        // Тест Code -> ID
        assert(Roles::getRoleId('external') === 1, 'external должен быть ID 1');
        assert(Roles::getRoleId('member') === 4, 'member должен быть ID 4');
        assert(Roles::getRoleId('admin') === 6, 'admin должен быть ID 6');
        
        // Тест проверки доступа по ID
        assert(Roles::hasAccessById(4, 4) === true, 'member должен иметь доступ к member');
        assert(Roles::hasAccessById(6, 4) === true, 'admin должен иметь доступ к member');
        assert(Roles::hasAccessById(2, 4) === false, 'guest не должен иметь доступ к member');
        
        echo "✅ Маппинг ролей работает корректно\n";
    }
    
    /**
     * Тест конфигурации функций
     */
    private static function testFunctionRoles() {
        echo "🔧 Тест конфигурации функций...\n";
        
        // Проверяем, что все API эндпоинты определены
        $functions = FunctionRoles::getAll();
        
        $requiredFunctions = [
            'api.users.getList',
            'api.users.create', 
            'api.users.getProfile',
            'api.cars.getList',
            'api.cars.getById',
            'api.cars.create',
            'api.events.getList',
            'api.events.create',
            'api.guide-objects.getList',
            'api.guide-objects.create',
            'api.businessCards.getList',
            'api.businessCards.create',
            'api.photos.getList',
            'api.photos.upload',
            'api.reviews.getList',
            'api.reviews.create',
            'api.health',
            'api.status'
        ];
        
        foreach ($requiredFunctions as $function) {
            assert(isset($functions[$function]), "Функция {$function} должна быть определена");
        }
        
        // Проверяем роли для ключевых функций
        assert(FunctionRoles::getRequiredRole('api.users.getList') === 'member', 'users.getList требует member');
        assert(FunctionRoles::getRequiredRole('api.users.create') === 'admin', 'users.create требует admin');
        assert(FunctionRoles::getRequiredRole('api.events.create') === 'moderator', 'events.create требует moderator');
        assert(FunctionRoles::getRequiredRole('api.health') === 'external', 'health требует external');
        
        echo "✅ Конфигурация функций корректна\n";
    }
    
    /**
     * Тест утилит доступа
     */
    private static function testAccessUtils() {
        echo "🔐 Тест утилит доступа...\n";
        
        // Тест без пользователя в контексте
        AppContext::clear();
        assert(AccessUtils::checkApiAccess('api.users.getList') === false, 'Без пользователя доступ должен быть запрещен');
        
        // Тест с пользователем member
        $memberUser = ['id' => 1, 'role' => 4]; // member
        AppContext::setCurrentUser($memberUser);
        AppContext::setRequestId('test123');
        AppContext::setStartTime(microtime(true));
        
        assert(AccessUtils::checkApiAccess('api.users.getList') === true, 'member должен иметь доступ к users.getList');
        assert(AccessUtils::checkApiAccess('api.users.create') === false, 'member не должен иметь доступ к users.create');
        assert(AccessUtils::checkApiAccess('api.events.create') === false, 'member не должен иметь доступ к events.create');
        
        // Тест с пользователем admin
        $adminUser = ['id' => 2, 'role' => 6]; // admin
        AppContext::setCurrentUser($adminUser);
        
        assert(AccessUtils::checkApiAccess('api.users.getList') === true, 'admin должен иметь доступ к users.getList');
        assert(AccessUtils::checkApiAccess('api.users.create') === true, 'admin должен иметь доступ к users.create');
        assert(AccessUtils::checkApiAccess('api.events.create') === true, 'admin должен иметь доступ к events.create');
        
        // Тест с пользователем guest
        $guestUser = ['id' => 3, 'role' => 2]; // guest
        AppContext::setCurrentUser($guestUser);
        
        assert(AccessUtils::checkApiAccess('api.users.getList') === false, 'guest не должен иметь доступ к users.getList');
        assert(AccessUtils::checkApiAccess('api.health') === true, 'guest должен иметь доступ к health');
        
        echo "✅ Утилиты доступа работают корректно\n";
    }
    
    /**
     * Тест интеграции с контроллерами
     */
    private static function testControllerIntegration() {
        echo "🎛️ Тест интеграции с контроллерами...\n";
        
        // Подключаем BaseController для тестирования
        require_once __DIR__ . '/../../controllers/BaseController.php';
        
        // Создаем тестовый контроллер
        $controller = new class extends BaseController {
            public function testAccess($function) {
                return $this->checkAccess($function);
            }
            
            public function testRequireAccess($function) {
                return $this->requireAccess($function);
            }
        };
        
        // Тестируем с пользователем member
        $memberUser = ['id' => 1, 'role' => 4];
        AppContext::setCurrentUser($memberUser);
        
        assert($controller->testAccess('api.users.getList') === true, 'member должен иметь доступ к users.getList');
        assert($controller->testAccess('api.users.create') === false, 'member не должен иметь доступ к users.create');
        
        // Тестируем requireAccess (должен вернуть true для разрешенного доступа)
        assert($controller->testRequireAccess('api.users.getList') === true, 'requireAccess должен вернуть true для разрешенного доступа');
        
        echo "✅ Интеграция с контроллерами работает корректно\n";
    }
}

// Запускаем тесты если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    AccessControlTest::runAllTests();
} 