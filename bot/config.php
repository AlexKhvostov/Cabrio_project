<?php
/**
 * bot/config.php
 * 
 * Конфигурация Telegram-бота CabrioRide
 */

// Загружаем переменные окружения из .env файла
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die('ERROR: .env file not found');
}

// Читаем и парсим .env файл
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }
}

// Функция для разделения строки с ID на массив
function parseIds($str) {
    if (empty($str)) return [];
    return array_filter(array_map('trim', explode(',', $str)));
}

// Функция для формирования URL WebApp
function getWebAppUrl() {
    global $env;
    $baseUrl = $env['APP_URL'] ?? 'https://app.cabrioride.by/';
    // Добавляем https:// если нет протокола
    if (!preg_match('/^https?:\/\//', $baseUrl)) {
        $baseUrl = 'https://' . $baseUrl;
    }
    // Убираем trailing slash если есть
    $baseUrl = rtrim($baseUrl, '/');
    // Добавляем параметр expand=true для открытия на весь экран
    return $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . 'expand=true';
}

// Функция для получения базового URL API (без параметров)
function getApiUrl() {
    global $env;
    $baseUrl = $env['APP_URL'] ?? 'https://app.cabrioride.by/';
    // Добавляем https:// если нет протокола
    if (!preg_match('/^https?:\/\//', $baseUrl)) {
        $baseUrl = 'https://' . $baseUrl;
    }
    // Убираем trailing slash если есть
    $baseUrl = rtrim($baseUrl, '/');
    return $baseUrl;
}

// Функция для получения ссылки на чат
function getChatInviteLink() {
    global $env;
    return $env['CHAT_INVITE_LINK'] ?? 'https://t.me/+your_chat_invite_link';
}

// Получить имя бота из .env (BOT_NAME)
function getBotName() {
    global $env;
    // Если в .env не задано, используем дефолтное имя
    return $env['BOT_NAME'] ?? 'CabrioControl_bot';
}

// Конфигурация бота
$config = [
    // Основные параметры бота
    'bot_token' => $env['BOT_TOKEN'] ?? null,
    'main_chat_id' => $env['MAIN_CHAT_ID'] ?? null,
    'app_url' => getWebAppUrl(),
    'api_url' => getApiUrl(),
    'chat_invite_link' => getChatInviteLink(),
    
    // Администраторы и модераторы
    'admin_ids' => parseIds($env['ADMIN_IDS'] ?? ''),
    'root_ids' => parseIds($env['ROOT_IDS'] ?? ''),
    'moderator_ids' => parseIds($env['MODERATOR_IDS'] ?? ''),
    
    // Настройки логирования
    'log_file' => __DIR__ . '/webhook.log',
    'debug_mode' => true,
    
    // Тексты сообщений
    'messages' => [
        'start' => [
            'welcome' => "👋 Привет! Я бот клуба CabrioRide.\n\nЯ помогу вам с поиском автомобилей и доступом к функциям клуба.",
            'non_member' => "❌ К сожалению, я работаю только с участниками клубного чата.\n\n📱 Для доступа к функциям бота вступите в наш чат:",
            'admin' => "🛡 Вы вошли как администратор.\n\nДоступные действия:",
            'moderator' => "🛡 Вы вошли как модератор.\n\nДоступные действия:",
            'member' => "🚗 Выберите действие:",
        ],
        'help' => [
            'main' => "🤖 Справка по командам бота\n\n" .
                     "📋 Основные команды:\n" .
                     "/start - Запустить бота\n" .
                     "/help - Показать эту справку\n\n" .
                     "🔍 Поиск автомобилей:\n" .
                     "• Отправьте фото номера для распознавания\n" .
                     "• Отправьте номер текстом для поиска\n" .
                     "• Используйте /search А123БВ77 для поиска\n" .
                     "• В групповом чате отправьте фото с '?' для распознавания",
        ],
        'error' => [
            'general' => "❌ Произошла ошибка. Пожалуйста, попробуйте позже.",
            'not_configured' => "❌ Ошибка: бот не настроен",
            'no_access' => "❌ У вас нет доступа к этой команде",
        ]
    ],
    
    // Кнопки
    'buttons' => [
        'admin' => [
            [
                ['text' => '👥 Управление пользователями', 'callback_data' => 'admin_users'],
                ['text' => '⚙️ Настройки', 'callback_data' => 'admin_settings']
            ],
            [
                ['text' => '🔍 Проверить авто', 'callback_data' => 'check_car'],
                ['text' => '📋 Справка', 'callback_data' => 'help']
            ],
            [
                ['text' => '🌐 Перейти в приложение', 'web_app' => ['url' => getWebAppUrl()]]
            ]
        ],
        'moderator' => [
            [
                ['text' => '👥 Модерация пользователей', 'callback_data' => 'mod_users'],
                ['text' => '🚗 Модерация авто', 'callback_data' => 'mod_cars']
            ],
            [
                ['text' => '🔍 Проверить авто', 'callback_data' => 'check_car'],
                ['text' => '📋 Справка', 'callback_data' => 'help']
            ],
            [
                ['text' => '🌐 Перейти в приложение', 'web_app' => ['url' => getWebAppUrl()]]
            ]
        ],
        'member' => [
            [
                ['text' => '🔍 Проверить авто', 'callback_data' => 'check_car'],
                ['text' => '📋 Справка', 'callback_data' => 'help']
            ],
            [
                ['text' => '🌐 Перейти в приложение', 'web_app' => ['url' => getWebAppUrl()]]
            ]
        ]
    ]
];

// Проверяем обязательные параметры
if (empty($config['bot_token'])) {
    die('ERROR: BOT_TOKEN not set in .env file');
}

if (empty($config['main_chat_id'])) {
    die('ERROR: MAIN_CHAT_ID not set in .env file');
}

// Функции для работы с конфигурацией
function getConfig($key = null) {
    global $config;
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? null;
}

function getMessage($path) {
    $config = getConfig();
    $keys = explode('.', $path);
    $current = $config['messages'];
    
    foreach ($keys as $key) {
        if (!isset($current[$key])) {
            return null;
        }
        $current = $current[$key];
    }
    
    return $current;
}

function getButtons($type) {
    $config = getConfig();
    return $config['buttons'][$type] ?? [];
}

function isAdmin($userId) {
    $config = getConfig();
    return in_array($userId, $config['admin_ids']) || 
           in_array($userId, $config['root_ids']);
}

function isRoot($userId) {
    $config = getConfig();
    return in_array($userId, $config['root_ids']);
}

function isModerator($userId) {
    $config = getConfig();
    return in_array($userId, $config['moderator_ids']) || 
           isAdmin($userId);
}

function isMainChatMember($chatId) {
    $config = getConfig();
    $mainChatIds = parseIds($config['main_chat_id']);
    return in_array($chatId, $mainChatIds);
} 