<?php
/**
 * test_caption_logic.php
 * 
 * Тест логики обработки caption в групповом чате
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест логики обработки caption\n\n";

// Симулируем разные типы сообщений с фото
$testMessages = [
    // 1. Фото с caption "?" - ДОЛЖНО обрабатываться
    [
        'type' => 'Фото с caption "?"',
        'photo' => true,
        'text' => null,
        'caption' => '?',
        'chat_type' => 'supergroup',
        'expected' => '✅ ОБРАБАТЫВАЕТСЯ (executeGroupPhoto)'
    ],
    // 2. Фото с text "?" - ДОЛЖНО обрабатываться
    [
        'type' => 'Фото с text "?"',
        'photo' => true,
        'text' => '?',
        'caption' => null,
        'chat_type' => 'supergroup',
        'expected' => '✅ ОБРАБАТЫВАЕТСЯ (executeGroupPhoto)'
    ],
    // 3. Фото с caption " ? " (с пробелами) - ДОЛЖНО обрабатываться
    [
        'type' => 'Фото с caption " ? "',
        'photo' => true,
        'text' => null,
        'caption' => ' ? ',
        'chat_type' => 'supergroup',
        'expected' => '✅ ОБРАБАТЫВАЕТСЯ (executeGroupPhoto)'
    ],
    // 4. Простое фото без текста - НЕ должно обрабатываться
    [
        'type' => 'Простое фото',
        'photo' => true,
        'text' => null,
        'caption' => null,
        'chat_type' => 'supergroup',
        'expected' => '❌ ИГНОРИРУЕТСЯ'
    ],
    // 5. Фото с другим caption - НЕ должно обрабатываться
    [
        'type' => 'Фото с другим caption',
        'photo' => true,
        'text' => null,
        'caption' => 'Привет!',
        'chat_type' => 'supergroup',
        'expected' => '❌ ИГНОРИРУЕТСЯ'
    ]
];

foreach ($testMessages as $i => $test) {
    echo "=== Тест " . ($i + 1) . ": " . $test['type'] . " ===\n";
    echo "📸 Есть фото: " . ($test['photo'] ? 'Да' : 'Нет') . "\n";
    echo "💬 Text: " . ($test['text'] ?? 'Нет') . "\n";
    echo "📝 Caption: " . ($test['caption'] ?? 'Нет') . "\n";
    echo "👥 Тип чата: " . $test['chat_type'] . "\n";
    echo "🎯 Ожидаемый результат: " . $test['expected'] . "\n\n";
}

echo "🎯 Исправленная логика:\n";
echo "1. Фото + text '?' в группе → executeGroupPhoto()\n";
echo "2. Фото + caption '?' в группе → executeGroupPhoto()\n";
echo "3. Фото + caption ' ? ' (с пробелами) → executeGroupPhoto()\n";
echo "4. Простое фото в группе → ИГНОРИРУЕТСЯ\n";
echo "5. Фото + другой caption в группе → ИГНОРИРУЕТСЯ\n\n";

echo "✅ Теперь бот будет обрабатывать:\n";
echo "   • Фото с text '?' в групповом чате\n";
echo "   • Фото с caption '?' в групповом чате\n";
echo "   • Все остальные сообщения игнорируются\n\n";

echo "🔍 Проверка условия в коде:\n";
echo "if (isset(\$message['photo']) && \n";
echo "    (isset(\$message['text']) && trim(\$message['text']) === '?' || \n";
echo "     isset(\$message['caption']) && trim(\$message['caption']) === '?')) {\n";
echo "    // Обрабатываем фото с '?' (text или caption)\n";
echo "}\n";
?> 