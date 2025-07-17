<?php
// --- Сервис для работы с Telegram API (отправка сообщений) ---
class BotService {
    private $token;
    public function __construct() {
        // Получаем токен из .env при создании сервиса
        $this->token = $this->getToken();
    }
    // Метод для получения токена из .env
    private function getToken() {
        $env = [];
        $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!strpos($line, '=')) continue;
            list($k, $v) = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
        return $env['BOT_TOKEN'] ?? null;
    }
    // Метод для отправки сообщения пользователю
    public function sendMessage($chat_id, $text) {
        if (!$this->token) return;
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage?chat_id={$chat_id}&text=" . urlencode($text);
        file_get_contents($url);
    }
}
