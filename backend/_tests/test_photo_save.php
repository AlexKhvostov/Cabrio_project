<?php
/**
 * Тест сохранения фото
 */

require_once __DIR__ . '/../actions/level1/_SavePhotoAction.php';
require_once __DIR__ . '/../utils/Logger.php';

// Тестовые данные
$testData = [
    'entity_type' => 'car',
    'entity_id' => 999,
    'uploaded_by' => 643, // Используем существующего пользователя
    'photo' => '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A',
    'photo_type' => 'cover',
    'description' => 'Тестовое фото'
];

echo "Тестируем сохранение фото...\n";

try {
    $result = _SavePhotoAction::handle($testData);
    
    if ($result['success']) {
        echo "✅ Фото успешно сохранено!\n";
        echo "ID фото: " . $result['data']['id'] . "\n";
        echo "Путь: " . $result['data']['url'] . "\n";
    } else {
        echo "❌ Ошибка сохранения фото:\n";
        echo "Код: " . $result['error']['code'] . "\n";
        echo "Сообщение: " . $result['error']['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
} 