<?php
/**
 * test_config_fix.php
 * 
 * Тест исправления конфигурации CLUB_CHAT_ID
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест исправления конфигурации CLUB_CHAT_ID\n\n";

// Подключаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

// Получаем значение из конфигурации
$clubChatId = getConfig('club_chat_id');
$chatIdFromTelegram = -1002873258290;

echo "📋 Значения для сравнения:\n";
echo "CLUB_CHAT_ID из .env: '$clubChatId' (тип: " . gettype($clubChatId) . ")\n";
echo "Chat ID из Telegram: $chatIdFromTelegram (тип: " . gettype($chatIdFromTelegram) . ")\n\n";

// Проверяем типы
echo "🔍 Проверка типов:\n";
echo "CLUB_CHAT_ID пустой: " . (empty($clubChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "CLUB_CHAT_ID null: " . (is_null($clubChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "CLUB_CHAT_ID строка: " . (is_string($clubChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "CLUB_CHAT_ID число: " . (is_numeric($clubChatId) ? 'ДА' : 'НЕТ') . "\n\n";

// Сравниваем значения
echo "🔍 Сравнение значений:\n";
echo "Строгое сравнение (===): " . ($clubChatId === $chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Обычное сравнение (==): " . ($clubChatId == $chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Сравнение как строки: " . ((string)$clubChatId === (string)$chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Сравнение как числа: " . ((int)$clubChatId === (int)$chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n\n";

// Тестируем логику из MessageHandler
echo "🎯 Тест логики MessageHandler:\n";
if ($chatIdFromTelegram != $clubChatId) {
    echo "❌ 'Not main chat, ignoring new members' - бот НЕ будет работать\n";
} else {
    echo "✅ Chat ID совпадает - бот БУДЕТ работать\n";
}

echo "\n💡 Рекомендации:\n";
if (is_string($clubChatId)) {
    echo "1. CLUB_CHAT_ID читается как строка\n";
    echo "2. Нужно привести к числу: (int)\$clubChatId\n";
} else {
    echo "1. CLUB_CHAT_ID читается как число\n";
    echo "2. Сравнение должно работать корректно\n";
}

echo "\n🔧 Исправление в коде:\n";
echo "Заменить: if (\$chat_id != \$clubChatId)\n";
echo "На: if ((int)\$chat_id != (int)\$clubChatId)\n";
?> 