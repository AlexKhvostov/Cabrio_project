<?php
/**
 * Конфигурация приложения CabrioRide
 * Все параметры и лимиты централизованы здесь
 * PHP-версия конфигурации для совместимости с ботом
 */

/**
 * Конфигурация системы активности пользователя
 */
class ActivityRewardsConfig {
    public $registration = 20;      // Баллы за регистрацию (только один раз)
    public $firstActivation = 20;   // Баллы за первую активацию профиля
    public $addCar = 30;            // За каждое добавление авто
    public $addHost = 10;           // За каждого нового гостя
    public $addEvent = 30;          // За каждое добавление события
    public $addReview = 10;         // За каждый отзыв
    public $addService = 20;        // За каждый сервис/продукт
}

/**
 * Конфигурация отзывов о гид-объектах
 */
class ReviewsConfig {
    public $maxPerGuideObject = 1;  // Один пользователь — один отзыв на гид-объект
}

/**
 * Конфигурация событий (мероприятий)
 */
class EventsConfig {
    public $maxPerUser = 10;        // Максимум событий, которые может создать пользователь
}

/**
 * Конфигурация приглашений (визиток) и проверок авто
 */
class InvitesConfig {
    public $maxCarChecksPerDay = 6;         // Лимит проверок авто для визитки в сутки
    public $minPlateLengthForCheck = 3;     // Минимум символов для поиска авто
    public $minPlateLengthForInvite = 6;    // Минимум символов для отправки визитки
}

/**
 * Конфигурация подсказок на карте
 */
class MapHintsConfig {
    public $maxPerUser = 3;         // Максимум активных подсказок на пользователя
}

/**
 * Конфигурация раздела "Карта"
 */
class MapConfig {
    public $maxLocationLifetimeMinutes = 20; // Координаты хранятся не более 20 минут
}

/**
 * Конфигурация голосовых сообщений на карте
 */
class VoiceMessagesConfig {
    public $maxDurationSeconds = 30;    // Максимальная длительность голосового сообщения (сек)
    public $maxLifetimeMinutes = 60;    // Сообщения хранятся не более 60 минут
    public $maxPerUser = 30;            // Максимум активных голосовых сообщений на пользователя
}

/**
 * Конфигурация лимитов уведомлений
 */
class NotificationsConfig {
    public $maxPerUserPerDay = 100;     // Максимум уведомлений в сутки на пользователя
}

/**
 * Конфигурация прав админов
 */
class AdminToolsConfig {
    public $editRights = [
        'allowRootEdit' => true,         // Только root может менять root-аккаунты
    ];
}

/**
 * Главная конфигурация приложения CabrioRide
 */
class AppConfig {
    public $activityRewards;
    public $reviews;
    public $events;
    public $invites;
    public $mapHints;
    public $map;
    public $voiceMessages;
    public $notifications;
    public $adminTools;

    public function __construct() {
        $this->activityRewards = new ActivityRewardsConfig();
        $this->reviews = new ReviewsConfig();
        $this->events = new EventsConfig();
        $this->invites = new InvitesConfig();
        $this->mapHints = new MapHintsConfig();
        $this->map = new MapConfig();
        $this->voiceMessages = new VoiceMessagesConfig();
        $this->notifications = new NotificationsConfig();
        $this->adminTools = new AdminToolsConfig();
    }

    /**
     * Получить значение конфигурации по пути
     * Пример: get('invites.maxCarChecksPerDay')
     */
    public function get($path) {
        $keys = explode('.', $path);
        $current = $this;
        
        foreach ($keys as $key) {
            if (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
            } elseif (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }
        
        return $current;
    }
}

// Создаем глобальный экземпляр конфигурации
return new AppConfig(); 