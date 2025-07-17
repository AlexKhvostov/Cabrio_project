<?php
// --- Обработчик входящих сообщений Telegram ---
require_once __DIR__ . '/../commands/TestCommand.php';

class MessageHandler {
    // Главный метод для обработки сообщения
    public function handle($message) {
        // Получаем текст сообщения (если есть)
        $text = trim($message['text'] ?? '');
        // Если это команда /test — вызываем соответствующую команду
        if ($text === '/test') {
            $cmd = new TestCommand();
            $cmd->execute($message);
        }
        // Здесь можно добавить обработку других команд/типов сообщений
    }
}
