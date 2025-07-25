<?php
/**
 * Эндпоинт для отправки сообщений в Telegram-группу через бота
 *
 * === ИНСТРУКЦИЯ ===
 *
 * 1. В .env должен быть задан ключ:
 *    BOT_SEND_MESSAGE_API_KEY=your_super_secret_key
 *
 * 2. Запрос:
 *    POST /bot/api/send_message.php
 *    Параметры (POST):
 *      - api_key  (строка, обяз.) — ваш секретный API-ключ
 *      - chat_id  (строка/число, обяз.) — ID чата (например, -1002873258290)
 *      - text     (строка, обяз.) — текст сообщения
 *
 * 3. Пример запроса через curl:
 *    curl -X POST http://localhost/app/bot/api/send_message.php \
 *      -d "api_key=your_super_secret_key" \
 *      -d "chat_id=-1002873258290" \
 *      -d "text=Тестовое сообщение из API"
 *
 * 4. Ответ:
 *    {"success":true} — если сообщение отправлено
 *    {"success":false, "error":"..."} — если ошибка
 *
 * 5. Ограничение частоты: не чаще 1 раза в 10 секунд с одного IP
 *
 * 6. Безопасность:
 *    - Никогда не публикуйте ключ в открытом доступе!
 *    - Не используйте токен бота в качестве ключа — только отдельный секрет.
 */
// bot/api/send_message.php

require_once __DIR__ . '/../services/BotService.php';

header('Content-Type: application/json');

// --- ПРОСТАЯ ЗАЩИТА ОТ СПАМА ---
// 1. Проверка API-ключа (секретный ключ должен быть только у вашего фронта)
$apiKey = $_POST['api_key'] ?? '';
$envFile = __DIR__ . '/../../.env';
$validApiKey = null;
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'BOT_SEND_MESSAGE_API_KEY=') === 0) {
            $validApiKey = trim(explode('=', $line, 2)[1]);
            break;
        }
    }
}
if (!$validApiKey || $apiKey !== $validApiKey) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid API key']);
    exit;
}

// 2. Ограничение частоты (rate limit) — например, не чаще 1 раза в 10 секунд с одного IP
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = sys_get_temp_dir() . '/tg_sendmsg_' . md5($ip) . '.lock';
$now = time();
$last = (file_exists($rateLimitFile)) ? (int)file_get_contents($rateLimitFile) : 0;
if ($now - $last < 10) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many requests']);
    exit;
}
file_put_contents($rateLimitFile, $now);

// 3. Проверка входных данных
$chat_id = $_POST['chat_id'] ?? null;
$text = $_POST['text'] ?? null;

if (!$chat_id || !$text) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'chat_id or text missing']);
    exit;
}

// 4. Отправка сообщения
$botService = new BotService();
$botService->sendMessage($chat_id, $text);

echo json_encode(['success' => true]); 