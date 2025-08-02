<?php
/**
 * sectionGroups.php
 * 
 * Единая точка правды для схемы доступа (roles/functions).
 * Для каждой функции/эндпоинта указывается минимальная роль для доступа.
 * Используется и на frontend, и на backend. Все изменения — только здесь!
 *
 * Визуальная схема и подробное описание принципа доступа — см. docs/ACCESS_SCHEME.md
 *
 * ---
 *
 * 📋 Что такое минимальная роль?
 * Минимальная роль — это наименьшая роль, с которой разрешён доступ к функции.
 * Если у пользователя роль равна или выше минимальной (по порядку в ROLES), доступ разрешён.
 *
 * Например, если минимальная роль "member":
 *   - member, moderator, admin — имеют доступ
 *   - user, guest, external — не имеют доступа
 */

// Подключаем AppContext для интеграции с глобальным контекстом
require_once __DIR__ . '/../backend/utils/AppContext.php';

/**
 * Список всех ролей по возрастанию прав
 * Соответствует таблице ref_roles в БД
 */
class Roles {
    const EXTERNAL = 'external';     // Внешний пользователь, не в чате (ID: 1)
    const GUEST = 'guest';           // Гость, только что добавился в чат (ID: 2)
    const USER = 'user';             // Завершил базовую регистрацию (ID: 3)
    const MEMBER = 'member';         // Участник клуба (ID: 4)
    const MODERATOR = 'moderator';   // Модератор (ID: 5)
    const ADMIN = 'admin';           // Администратор (ID: 6)
    
    /**
     * Маппинг строковых кодов на числовые ID из БД
     */
    const ROLE_IDS = [
        self::EXTERNAL => 1,
        self::GUEST => 2,
        self::USER => 3,
        self::MEMBER => 4,
        self::MODERATOR => 5,
        self::ADMIN => 6
    ];
    
    /**
     * Маппинг числовых ID на строковые коды
     */
    const ID_ROLES = [
        1 => self::EXTERNAL,
        2 => self::GUEST,
        3 => self::USER,
        4 => self::MEMBER,
        5 => self::MODERATOR,
        6 => self::ADMIN
    ];

    /**
     * Получить массив всех ролей в порядке возрастания прав
     */
    public static function getAll() {
        return [
            self::EXTERNAL,
            self::GUEST,
            self::USER,
            self::MEMBER,
            self::MODERATOR,
            self::ADMIN
        ];
    }

    /**
     * Получить индекс роли (для сравнения прав)
     */
    public static function getIndex($role) {
        $roles = self::getAll();
        return array_search($role, $roles);
    }

    /**
     * Проверить, имеет ли пользователь доступ к функции
     */
    public static function hasAccess($userRole, $requiredRole) {
        $userIndex = self::getIndex($userRole);
        $requiredIndex = self::getIndex($requiredRole);
        
        if ($userIndex === false || $requiredIndex === false) {
            return false;
        }
        
        return $userIndex >= $requiredIndex;
    }
    
    /**
     * Проверить доступ по числовым ID ролей (для работы с БД)
     */
    public static function hasAccessById($userRoleId, $requiredRoleId) {
        return $userRoleId >= $requiredRoleId;
    }
    
    /**
     * Получить строковый код роли по числовому ID
     */
    public static function getRoleByCode($roleId) {
        return self::ID_ROLES[$roleId] ?? self::GUEST;
    }
    
    /**
     * Получить числовой ID роли по строковому коду
     */
    public static function getRoleId($roleCode) {
        return self::ROLE_IDS[$roleCode] ?? 2; // По умолчанию guest
    }
}

/**
 * Привязка функций/эндпоинтов к минимальной роли доступа
 */
class FunctionRoles {
    // users
    const USER_ROLE_SET = 'moderator';
    
    // API endpoints - Users
    const API_USERS_GET_LIST = 'member';
    const API_USERS_CREATE = 'admin';
    const API_USERS_GET_PROFILE = 'member';
    
    // API endpoints - Cars
    const API_CARS_GET_LIST = 'member';
    const API_CARS_GET_BY_ID = 'member';
    const API_CARS_CREATE = 'member';
    
    // API endpoints - Events
    const API_EVENTS_GET_LIST = 'member';
    const API_EVENTS_CREATE = 'moderator';
    
    // API endpoints - Guide Objects
    const API_GUIDE_OBJECTS_GET_LIST = 'member';
    const API_GUIDE_OBJECTS_CREATE = 'moderator';
    
    // API endpoints - Business Cards
    const API_BUSINESS_CARDS_GET_LIST = 'member';
    const API_BUSINESS_CARDS_CREATE = 'member';
    
    // API endpoints - Photos
    const API_PHOTOS_GET_LIST = 'member';
    const API_PHOTOS_UPLOAD = 'member';
    
    // API endpoints - Reviews
    const API_REVIEWS_GET_LIST = 'member';
    const API_REVIEWS_CREATE = 'member';
    
    // API endpoints - System
    const API_HEALTH = 'external';
    const API_STATUS = 'external';

    /**
     * Получить массив всех функций с их минимальными ролями
     */
    public static function getAll() {
        return [
            // users
            'userRoleSet' => self::USER_ROLE_SET,
            
            // API endpoints - Users
            'api.users.getList' => self::API_USERS_GET_LIST,
            'api.users.create' => self::API_USERS_CREATE,
            'api.users.getProfile' => self::API_USERS_GET_PROFILE,
            
            // API endpoints - Cars
            'api.cars.getList' => self::API_CARS_GET_LIST,
            'api.cars.getById' => self::API_CARS_GET_BY_ID,
            'api.cars.create' => self::API_CARS_CREATE,
            
            // API endpoints - Events
            'api.events.getList' => self::API_EVENTS_GET_LIST,
            'api.events.create' => self::API_EVENTS_CREATE,
            
            // API endpoints - Guide Objects
            'api.guide-objects.getList' => self::API_GUIDE_OBJECTS_GET_LIST,
            'api.guide-objects.create' => self::API_GUIDE_OBJECTS_CREATE,
            
            // API endpoints - Business Cards
            'api.businessCards.getList' => self::API_BUSINESS_CARDS_GET_LIST,
            'api.businessCards.create' => self::API_BUSINESS_CARDS_CREATE,
            
            // API endpoints - Photos
            'api.photos.getList' => self::API_PHOTOS_GET_LIST,
            'api.photos.upload' => self::API_PHOTOS_UPLOAD,
            
            // API endpoints - Reviews
            'api.reviews.getList' => self::API_REVIEWS_GET_LIST,
            'api.reviews.create' => self::API_REVIEWS_CREATE,
            
            // API endpoints - System
            'api.health' => self::API_HEALTH,
            'api.status' => self::API_STATUS,
        ];
    }

    /**
     * Получить минимальную роль для функции
     */
    public static function getRequiredRole($function) {
        $functions = self::getAll();
        return $functions[$function] ?? null;
    }

    /**
     * Проверить доступ пользователя к функции
     */
    public static function checkAccess($userRole, $function) {
        $requiredRole = self::getRequiredRole($function);
        if (!$requiredRole) {
            return false;
        }
        
        return Roles::hasAccess($userRole, $requiredRole);
    }
}

/**
 * Утилиты для работы с ролями и правами доступа
 */
class AccessUtils {
    /**
     * Получить роль пользователя из Telegram данных
     * (упрощённая версия, в реальности нужно проверять через API)
     */
    public static function getUserRole($telegramUser) {
        // По умолчанию - гость
        $role = Roles::GUEST;
        
        // Если пользователь в группе - проверяем статус
        if (isset($telegramUser['id'])) {
            // TODO: Здесь должна быть проверка через API backend
            // Пока возвращаем базовую роль
            $role = Roles::GUEST;
        }
        
        return $role;
    }

    /**
     * Проверить, может ли пользователь выполнить команду бота
     */
    public static function canExecuteBotCommand($telegramUser, $command) {
        $userRole = self::getUserRole($telegramUser);
        return FunctionRoles::checkAccess($userRole, $command);
    }
    
    /**
     * Проверить доступ к API эндпоинту через AppContext
     */
    public static function checkApiAccess($function) {
        // Получаем пользователя из глобального контекста
        $user = AppContext::getCurrentUser();
        if (!$user) {
            return false;
        }
        
        $userRoleId = $user['role'] ?? 2; // По умолчанию guest
        $userRoleCode = Roles::getRoleByCode($userRoleId);
        
        return FunctionRoles::checkAccess($userRoleCode, $function);
    }
    
    /**
     * Получить минимальную роль для API эндпоинта
     */
    public static function getRequiredRoleForApi($function) {
        return FunctionRoles::getRequiredRole($function);
    }
    
    /**
     * Проверить доступ по числовым ID ролей (для работы с БД)
     */
    public static function checkAccessById($userRoleId, $function) {
        $requiredRole = FunctionRoles::getRequiredRole($function);
        if (!$requiredRole) {
            return false;
        }
        
        $requiredRoleId = Roles::getRoleId($requiredRole);
        return Roles::hasAccessById($userRoleId, $requiredRoleId);
    }
} 