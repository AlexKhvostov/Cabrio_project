<?php
/**
 * test_logging.php
 * 
 * Тест логирования групповых сообщений
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест логирования групповых сообщений\n\n";

// Симулируем разные типы сообщений
$testMessages = [
    // Групповое сообщение с фото и "?"
    [
        'type' => 'Групповое фото с "?"',
        'chat' => ['type' => 'group', 'id' => -1002873258290],
        'photo' => true,
        'text' => '?',
        'should_log' => true
    ],
    // Групповое сообщение с простым фото
    [
        'type' => 'Групповое простое фото',
        'chat' => ['type' => 'group', 'id' => -1002873258290],
        'photo' => true,
        'text' => null,
        'should_log' => false
    ],
    // Личное сообщение с фото
    [
        'type' => 'Личное фото',
        'chat' => ['type' => 'private', 'id' => 123456789],
        'photo' => true,
        'text' => null,
        'should_log' => true
    ]
];

foreach ($testMessages as $i => $test) {
    echo "=== Тест " . ($i + 1) . ": " . $test['type'] . " ===\n";
    echo "📱 Тип чата: " . $test['chat']['type'] . "\n";
    echo "📸 Есть фото: " . ($test['photo'] ? 'Да' : 'Нет') . "\n";
    echo "💬 Текст: " . ($test['text'] ?? 'Нет') . "\n";
    echo "📝 Должно логироваться: " . ($test['should_log'] ? 'Да' : 'Нет') . "\n\n";
}

echo "🎯 Проверка логирования:\n";
echo "1. Групповые фото с '?' → ДОЛЖНЫ логироваться\n";
echo "2. Групповые простые фото → НЕ логируются (игнорируются)\n";
echo "3. Личные фото → ДОЛЖНЫ логироваться\n\n";

echo "🔍 Проверьте логи в bot/webhook.log\n";
echo "📝 Ищите записи с 'Processing message' и 'chat.type': 'group'\n\n";

echo "💡 Возможные причины отсутствия логов:\n";
echo "   • Сообщения не доходят до webhook\n";
echo "   • Бот не добавлен в группу\n";
echo "   • Неправильный webhook URL\n";
echo "   • Ошибки в обработке сообщений\n";
?> 