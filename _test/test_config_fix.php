<?php
/**
 * test_config_fix.php
 * 
 * Тест исправления конфигурации MAIN_CHAT_ID
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест исправления конфигурации MAIN_CHAT_ID\n\n";

// Подключаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

// Получаем значение из конфигурации
$mainChatId = getConfig('main_chat_id');
$chatIdFromTelegram = -1002873258290;

echo "📋 Значения для сравнения:\n";
echo "MAIN_CHAT_ID из .env: '$mainChatId' (тип: " . gettype($mainChatId) . ")\n";
echo "Chat ID из Telegram: $chatIdFromTelegram (тип: " . gettype($chatIdFromTelegram) . ")\n\n";

// Проверяем типы
echo "🔍 Проверка типов:\n";
echo "MAIN_CHAT_ID пустой: " . (empty($mainChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "MAIN_CHAT_ID null: " . (is_null($mainChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "MAIN_CHAT_ID строка: " . (is_string($mainChatId) ? 'ДА' : 'НЕТ') . "\n";
echo "MAIN_CHAT_ID число: " . (is_numeric($mainChatId) ? 'ДА' : 'НЕТ') . "\n\n";

// Сравниваем значения
echo "🔍 Сравнение значений:\n";
echo "Строгое сравнение (===): " . ($mainChatId === $chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Обычное сравнение (==): " . ($mainChatId == $chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Сравнение как строки: " . ((string)$mainChatId === (string)$chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n";
echo "Сравнение как числа: " . ((int)$mainChatId === (int)$chatIdFromTelegram ? 'РАВНЫ' : 'НЕ РАВНЫ') . "\n\n";

// Тестируем логику из MessageHandler
echo "🎯 Тест логики MessageHandler:\n";
if ($chatIdFromTelegram != $mainChatId) {
    echo "❌ 'Not main chat, ignoring new members' - бот НЕ будет работать\n";
} else {
    echo "✅ Chat ID совпадает - бот БУДЕТ работать\n";
}

echo "\n💡 Рекомендации:\n";
if (is_string($mainChatId)) {
    echo "1. MAIN_CHAT_ID читается как строка\n";
    echo "2. Нужно привести к числу: (int)\$mainChatId\n";
} else {
    echo "1. MAIN_CHAT_ID читается как число\n";
    echo "2. Сравнение должно работать корректно\n";
}

echo "\n🔧 Исправление в коде:\n";
echo "Заменить: if (\$chat_id != \$mainChatId)\n";
echo "На: if ((int)\$chat_id != (int)\$mainChatId)\n";
?> 