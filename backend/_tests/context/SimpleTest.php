<?php

require_once __DIR__ . '/../../utils/AppContext.php';

echo "🧪 Простой тест AppContext...\n";

// Очищаем контекст
AppContext::clear();

// Проверяем начальное состояние
echo "Начальное состояние:\n";
echo "- Пользователь: " . (AppContext::getCurrentUser() === null ? 'null' : 'не null') . "\n";
echo "- Сессия: " . (AppContext::getSessionId() === null ? 'null' : 'не null') . "\n";

// Устанавливаем пользователя
$testUser = ['id' => 123, 'name' => 'Иван'];
AppContext::setCurrentUser($testUser);

echo "После установки пользователя:\n";
echo "- Пользователь: " . (AppContext::getCurrentUser() ? 'установлен' : 'не установлен') . "\n";
echo "- ID пользователя: " . (AppContext::getCurrentUser()['id'] ?? 'нет') . "\n";

// Очищаем контекст
AppContext::clear();

echo "После очистки:\n";
echo "- Пользователь: " . (AppContext::getCurrentUser() === null ? 'null' : 'не null') . "\n";

echo "✅ Тест завершен!\n"; 