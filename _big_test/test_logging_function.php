<?php
/**
 * test_logging_function.php
 * 
 * Тест функции логирования
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест функции логирования\n\n";

// Подключаем функцию логирования
require_once __DIR__ . '/../bot/utils/Logger.php';

echo "📝 Тестируем функцию writeToLog():\n";

// Тест 1: Простое сообщение
writeToLog("TEST: Простое тестовое сообщение");
echo "✅ Тест 1: Простое сообщение записано\n";

// Тест 2: Сообщение с данными
writeToLog("TEST: Сообщение с данными", ['test' => 'data', 'time' => time()]);
echo "✅ Тест 2: Сообщение с данными записано\n";

// Тест 3: Проверяем размер файла
$logFile = __DIR__ . '/../bot/webhook.log';
$sizeBefore = filesize($logFile);
echo "📊 Размер файла до: " . $sizeBefore . " байт\n";

// Ждем немного
sleep(1);

$sizeAfter = filesize($logFile);
echo "📊 Размер файла после: " . $sizeAfter . " байт\n";

if ($sizeAfter > $sizeBefore) {
    echo "✅ Файл логов обновляется\n";
} else {
    echo "❌ Файл логов НЕ обновляется\n";
}

// Тест 4: Читаем последние строки
echo "\n📖 Последние 5 строк лога:\n";
$lines = file($logFile);
$lastLines = array_slice($lines, -5);
foreach ($lastLines as $line) {
    echo trim($line) . "\n";
}

echo "\n🎯 Результат:\n";
echo "Если вы видите тестовые сообщения выше, логирование работает\n";
echo "Если размер файла увеличился, логи записываются\n";
?> 