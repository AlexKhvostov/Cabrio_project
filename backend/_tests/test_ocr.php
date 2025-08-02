<?php
/**
 * 🧪 Тест OCR распознавания номеров
 * 
 * Назначение: Проверка работы _RecognizePlateNumberAction
 * Запуск: php backend/_tests/test_ocr.php
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение OCR утилиты
require_once __DIR__ . '/../actions/helpers/RecognizeCarNumberFromPhotoAction.php';

echo "🧪 Тест OCR распознавания номеров\n";
echo "================================\n\n";

// Проверка наличия токена
if (empty($_ENV['OCR_TOKEN'])) {
    echo "❌ Ошибка: OCR_TOKEN не найден в .env файле\n";
    exit(1);
}

echo "✅ OCR_TOKEN найден в .env\n\n";

// Тестовые данные с реальным изображением
$testPhotoFile = [
    'name' => '_test_photo_(9588MI1).jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/_test_photo_(9588MI1).jpg', // Путь к тестовому файлу
    'size' => filesize(__DIR__ . '/_test_photo_(9588MI1).jpg'), // Реальный размер файла
    'error' => 0
];

echo "📸 Тестовый файл: {$testPhotoFile['name']}\n";
echo "📏 Размер: " . round($testPhotoFile['size'] / 1024 / 1024, 2) . "MB\n";
echo "🔤 Тип: {$testPhotoFile['type']}\n\n";

// Проверка существования тестового файла
if (!file_exists($testPhotoFile['tmp_name'])) {
    echo "⚠️  Тестовый файл не найден: {$testPhotoFile['tmp_name']}\n";
    echo "📝 Создайте тестовое изображение автомобиля для проверки OCR\n\n";
    
    echo "📋 Инструкции для тестирования:\n";
    echo "1. Создайте файл test_car.jpg в папке backend/_tests/\n";
    echo "2. Фото должно содержать четкий номер автомобиля\n";
    echo "3. Размер файла не более 3MB\n";
    echo "4. Формат: JPEG или PNG\n";
    echo "5. Рекомендуемое разрешение: 1024×768\n";
    echo "6. Автомобиль должен занимать минимум 15% изображения\n\n";
    
    exit(0);
}

echo "✅ Тестовый файл найден\n\n";

// Вызов экшена
echo "🔍 Выполняем распознавание номера...\n";

try {
    $plateNumber = RecognizeCarNumberFromPhotoAction::handle($testPhotoFile);
    
    echo "\n📊 Результат:\n";
    echo "=============\n";
    echo "✅ Успешно!\n";
    echo "🚗 Номер: $plateNumber\n";
    echo "💬 Сообщение: Номер автомобиля успешно распознан\n";
    
} catch (Exception $e) {
    echo "💥 Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "\n🏁 Тест завершен\n"; 