<?php

define('DEBUG_AUTH', true);

require_once __DIR__ . '/../utils/AuthHelper.php';

echo "🧪 Тест извлечения Telegram данных\n\n";

// Симулируем данные
$_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '123456789';
$_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Тест';
$_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'Пользователь';
$_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'test_user';

echo "📤 Установленные данные:\n";
echo "   - HTTP_X_TELEGRAM_USER_ID: " . ($_SERVER['HTTP_X_TELEGRAM_USER_ID'] ?? 'null') . "\n";
echo "   - HTTP_X_TELEGRAM_FIRST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] ?? 'null') . "\n";
echo "   - HTTP_X_TELEGRAM_LAST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] ?? 'null') . "\n";
echo "   - HTTP_X_TELEGRAM_USERNAME: " . ($_SERVER['HTTP_X_TELEGRAM_USERNAME'] ?? 'null') . "\n\n";

$result = AuthHelper::extractTelegramData();

echo "📥 Результат:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"; 