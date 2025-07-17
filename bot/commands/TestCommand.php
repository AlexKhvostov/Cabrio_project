<?php
// --- Команда для обработки /test ---
require_once __DIR__ . '/../services/BotService.php';

class TestCommand {
    // Выполняет команду: отправляет сообщение "Сервер работает!"
    public function execute($message) {
        // Получаем chat_id пользователя, который написал боту
        $chat_id = $message['chat']['id'];
        // Создаём сервис для отправки сообщений
        $service = new BotService();
        // Отправляем ответ пользователю
        $service->sendMessage($chat_id, 'Сервер работает!');
    }
} 