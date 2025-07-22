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
 *   - registered, guest, external, new — не имеют доступа
 */

/**
 * Список всех ролей по возрастанию прав
 */
class Roles {
    const EXTERNAL = 'external';     // Внешний пользователь, не в чате
    const GUEST = 'guest';           // Гость, только что добавился в чат
    const NEW = 'new';               // Новый, начал регистрацию
    const REGISTERED = 'registered'; // Завершил базовую регистрацию
    const MEMBER = 'member';         // Участник клуба
    const MODERATOR = 'moderator';   // Модератор
    const ADMIN = 'admin';           // Администратор

    /**
     * Получить массив всех ролей в порядке возрастания прав
     */
    public static function getAll() {
        return [
            self::EXTERNAL,
            self::GUEST,
            self::NEW,
            self::REGISTERED,
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
}

/**
 * Привязка функций/эндпоинтов к минимальной роли доступа
 */
class FunctionRoles {
    // landing
    const VIEW_LANDING = 'external';
    const VIEW_ABOUT = 'external';
    const VIEW_INVITE = 'external';

    // auth
    const LOGIN = 'guest';
    const REGISTRATION = 'guest';
    const RESTORE_PASSWORD = 'guest';

    // profile
    const PROFILE_VIEW = 'registered';
    const PROFILE_EDIT = 'registered';
    const PROFILE_SETTINGS = 'registered';

    // dashboard
    const DASHBOARD_VIEW = 'registered';
    const NOTIFICATIONS_VIEW = 'registered';

    // cars
    const CAR_LIST = 'member';
    const CAR_ADD = 'member';
    const CAR_EDIT = 'member';
    const CAR_VIEW = 'member';

    // events
    const EVENT_VIEW = 'member';
    const EVENT_JOIN = 'member';

    // map
    const MAP_VIEW = 'member';

    // businessCard
    const BUSINESS_CARD = 'member';

    // moderation
    const MODERATION_PANEL = 'moderator';

    // admin
    const ADMIN_PANEL = 'admin';

    // support
    const SUPPORT = 'guest';
    const FAQ = 'guest';
    const FEEDBACK = 'guest';

    // bot commands
    const BOT_START = 'guest';
    const BOT_HELP = 'guest';
    const BOT_MENU = 'guest';
    const BOT_TEST = 'guest';
    const BOT_OCR = 'member';
    const BOT_SEARCH = 'member';

    // users
    const USER_ROLE_SET = 'moderator';

    /**
     * Получить массив всех функций с их минимальными ролями
     */
    public static function getAll() {
        return [
            // landing
            'viewLanding' => self::VIEW_LANDING,
            'viewAbout' => self::VIEW_ABOUT,
            'viewInvite' => self::VIEW_INVITE,

            // auth
            'login' => self::LOGIN,
            'registration' => self::REGISTRATION,
            'restorePassword' => self::RESTORE_PASSWORD,

            // profile
            'profileView' => self::PROFILE_VIEW,
            'profileEdit' => self::PROFILE_EDIT,
            'profileSettings' => self::PROFILE_SETTINGS,

            // dashboard
            'dashboardView' => self::DASHBOARD_VIEW,
            'notificationsView' => self::NOTIFICATIONS_VIEW,

            // cars
            'carList' => self::CAR_LIST,
            'carAdd' => self::CAR_ADD,
            'carEdit' => self::CAR_EDIT,
            'carView' => self::CAR_VIEW,

            // events
            'eventView' => self::EVENT_VIEW,
            'eventJoin' => self::EVENT_JOIN,

            // map
            'mapView' => self::MAP_VIEW,

            // businessCard
            'businessCard' => self::BUSINESS_CARD,

            // moderation
            'moderationPanel' => self::MODERATION_PANEL,

            // admin
            'adminPanel' => self::ADMIN_PANEL,

            // support
            'support' => self::SUPPORT,
            'faq' => self::FAQ,
            'feedback' => self::FEEDBACK,

            // bot commands
            'botStart' => self::BOT_START,
            'botHelp' => self::BOT_HELP,
            'botMenu' => self::BOT_MENU,
            'botTest' => self::BOT_TEST,
            'botOcr' => self::BOT_OCR,
            'botSearch' => self::BOT_SEARCH,

            // users
            'userRoleSet' => self::USER_ROLE_SET,
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
} 