<?php
/**
 * Тест сохранения фото
 * Проверяет исправленную логику сохранения фото в __AddCarToUserAction
 */

require_once __DIR__ . '/../actions/helpers/FileHelper.php';
require_once __DIR__ . '/../utils/Logger.php';

// Создаем тестовое base64 изображение (1x1 пиксель) - только base64 часть
$testBase64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A';

echo "🧪 Тестирование сохранения фото...\n";

try {
    echo "1. Проверка base64 данных...\n";
    $imageBinary = base64_decode($testBase64, true);
    if ($imageBinary === false) {
        throw new Exception('Неверный формат base64 данных');
    }
    echo "✅ Base64 декодирован успешно, размер: " . strlen($imageBinary) . " байт\n";
    
    // Тестируем создание временного файла из base64
    echo "\n2. Создание временного файла из base64...\n";
    $tempFileData = FileHelper::createTempFileFromBase64($testBase64, 'test_photo.jpg');
    
    echo "✅ Временный файл создан: " . $tempFileData['tmp_name'] . "\n";
    echo "   Размер: " . $tempFileData['size'] . " байт\n";
    echo "   Тип: " . $tempFileData['type'] . "\n";
    
    // Проверяем, что файл существует
    if (file_exists($tempFileData['tmp_name'])) {
        echo "✅ Временный файл существует\n";
    } else {
        echo "❌ Временный файл не существует\n";
    }
    
    // Тестируем сохранение фото
    echo "\n3. Тестирование savePhotoFromBase64...\n";
    $savedPath = FileHelper::savePhotoFromBase64($testBase64, 'car', 999, 999, 'test_photo.jpg');
    
    echo "✅ Фото сохранено: $savedPath\n";
    
    // Проверяем, что файл существует
    $fullPath = __DIR__ . '/../../' . $savedPath;
    if (file_exists($fullPath)) {
        echo "✅ Сохраненный файл существует: $fullPath\n";
        echo "   Размер: " . filesize($fullPath) . " байт\n";
    } else {
        echo "❌ Сохраненный файл не существует: $fullPath\n";
    }
    
    // Удаляем временный файл
    if (file_exists($tempFileData['tmp_name'])) {
        unlink($tempFileData['tmp_name']);
        echo "✅ Временный файл удален\n";
    }
    
    echo "\n🎉 Тест прошел успешно!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . "\n";
    echo "   Строка: " . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
} 