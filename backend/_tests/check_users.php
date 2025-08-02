<?php
/**
 * Проверка существующих пользователей
 */
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../models/User.php';

echo "👥 Проверка существующих пользователей\n";

$users = User::getAll();
echo "Найдено пользователей: " . count($users) . "\n";

foreach ($users as $user) {
    echo "ID: {$user['id']}, Telegram ID: {$user['telegram_id']}, Имя: {$user['first_name_app']}\n";
} 