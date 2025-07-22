<?php
/**
 * Конфигурация Telegram-бота CabrioRide
 * Все тексты уведомлений централизованы и могут быть отредактированы без изменения кода
 * PHP-версия конфигурации для совместимости с ботом
 */

/**
 * Конфигурация уведомлений бота
 */
class BotNotificationsConfig {
    public $user;
    public $group;
    public $welcome;

    public function __construct() {
        $this->user = new UserNotificationsConfig();
        $this->group = new GroupNotificationsConfig();
        $this->welcome = new WelcomeConfig();
    }
}

/**
 * Личные уведомления пользователю
 */
class UserNotificationsConfig {
    public $moderation;
    public $respect;
    public $registration;

    public function __construct() {
        $this->moderation = new ModerationConfig();
        $this->respect = new RespectConfig();
        $this->registration = new RegistrationConfig();
    }
}

/**
 * Уведомления о модерации профиля
 */
class ModerationConfig {
    public $enabled = true;
    public $approved = 'Ваш профиль одобрен! Добро пожаловать в CabrioRide 🚗';
    public $rejected = 'Ваш профиль отклонён. Причина: {reason}';
    public $sent = 'Ваш профиль отправлен на модерацию.';
}

/**
 * Уведомления о получении respect
 */
class RespectConfig {
    public $enabled = true;
    public $received = 'Вам начислен respect от участника клуба!';
}

/**
 * Уведомления о регистрации пользователя
 */
class RegistrationConfig {
    public $enabled = true;
    public $approved = 'Ваш профиль одобрен! Добро пожаловать в CabrioRide 🚗';
    public $rejected = 'Ваш профиль отклонён. Причина: {reason}';
    public $sent = 'Ваш профиль отправлен на модерацию.';
    public $notRegistered = 'Добро пожаловать в CabrioRide! Для доступа к клубу и всем функциям, пожалуйста, зарегистрируйтесь в приложении.\n[Зарегистрироваться](https://app.cabrioride.ru/?register=1)';
}

/**
 * Уведомления в групповой чат клуба
 */
class GroupNotificationsConfig {
    public $newUser;
    public $newCar;

    public function __construct() {
        $this->newUser = new NewUserConfig();
        $this->newCar = new NewCarConfig();
    }
}

/**
 * Приветствие нового участника
 */
class NewUserConfig {
    public $enabled = true;
    public $text = 'В клубе новый участник: {user}';
}

/**
 * Уведомление о новом авто
 */
class NewCarConfig {
    public $enabled = true;
    public $text = 'В клубе новое авто: {car}';
}

/**
 * Приветственное сообщение при /start
 */
class WelcomeConfig {
    public $enabled = true;
    public $text = 'Привет! Я бот CabrioRide. Я умею проверять авто по фото или номеру, присылать уведомления и переводить тебя в приложение. Для начала — нажми кнопку ниже!';
}

/**
 * Конфигурация WebApp
 */
class WebAppConfig {
    public $baseUrl = 'https://app.cabrioride.by';
    public $registrationPath = '/?register=1';
    public $mainPath = '/';
}

/**
 * Главная конфигурация бота
 */
class BotConfig {
    public $notifications;
    public $webApp;

    public function __construct() {
        $this->notifications = new BotNotificationsConfig();
        $this->webApp = new WebAppConfig();
    }

    /**
     * Получить текст уведомления с подстановкой переменных
     * Пример: getNotificationText('user.registration.approved', ['user' => 'Иван'])
     */
    public function getNotificationText($path, $variables = []) {
        $text = $this->get($path);
        if (!$text) return '';

        // Подставляем переменные в фигурных скобках
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }

    /**
     * Получить значение конфигурации по пути
     * Пример: get('user.registration.approved')
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

    /**
     * Проверить, включено ли уведомление
     * Пример: isEnabled('user.moderation')
     */
    public function isEnabled($path) {
        $enabled = $this->get($path . '.enabled');
        return $enabled === true || $enabled === 1;
    }
}

// Создаем глобальный экземпляр конфигурации бота
return new BotConfig(); 