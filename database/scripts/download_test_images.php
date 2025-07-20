<?php
/**
 * Скрипт для загрузки тестовых изображений
 * Использует placeholder сервисы для создания изображений
 */

// Конфигурация
$baseDir = __DIR__ . '/../../uploads';
$imageUrls = [
    // Аватары пользователей
    'avatars/lex_avatar.jpg' => 'https://via.placeholder.com/200x200/4A90E2/FFFFFF?text=Lex',
    'avatars/ivan_avatar.jpg' => 'https://via.placeholder.com/200x200/7ED321/FFFFFF?text=Ivan',
    'avatars/maria_avatar.jpg' => 'https://via.placeholder.com/200x200/F5A623/FFFFFF?text=Maria',
    'avatars/dmitry_avatar.jpg' => 'https://via.placeholder.com/200x200/BD10E0/FFFFFF?text=Dmitry',
    'avatars/anna_avatar.jpg' => 'https://via.placeholder.com/200x200/50E3C2/FFFFFF?text=Anna',
    'avatars/sergey_avatar.jpg' => 'https://via.placeholder.com/200x200/D0021B/FFFFFF?text=Sergey',
    'avatars/elena_avatar.jpg' => 'https://via.placeholder.com/200x200/9013FE/FFFFFF?text=Elena',
    'avatars/pavel_avatar.jpg' => 'https://via.placeholder.com/200x200/417505/FFFFFF?text=Pavel',
    
    // Фото автомобилей
    'cars/bmw_3series_front.jpg' => 'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Front',
    'cars/bmw_3series_side.jpg' => 'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Side',
    'cars/bmw_3series_interior.jpg' => 'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Interior',
    'cars/mercedes_slk_front.jpg' => 'https://via.placeholder.com/400x300/CCCCCC/000000?text=Mercedes+SLK+Front',
    'cars/mercedes_slk_top.jpg' => 'https://via.placeholder.com/400x300/CCCCCC/000000?text=Mercedes+SLK+Top',
    'cars/audi_a3_front.jpg' => 'https://via.placeholder.com/400x300/FFFFFF/000000?text=Audi+A3+Front',
    'cars/audi_a3_side.jpg' => 'https://via.placeholder.com/400x300/FFFFFF/000000?text=Audi+A3+Side',
    'cars/porsche_boxster_front.jpg' => 'https://via.placeholder.com/400x300/FF0000/FFFFFF?text=Porsche+Boxster+Front',
    'cars/porsche_boxster_side.jpg' => 'https://via.placeholder.com/400x300/FF0000/FFFFFF?text=Porsche+Boxster+Side',
    'cars/mazda_mx5_front.jpg' => 'https://via.placeholder.com/400x300/0000FF/FFFFFF?text=Mazda+MX-5+Front',
    'cars/mazda_mx5_top.jpg' => 'https://via.placeholder.com/400x300/0000FF/FFFFFF?text=Mazda+MX-5+Top',
    'cars/vw_beetle_front.jpg' => 'https://via.placeholder.com/400x300/FFFF00/000000?text=VW+Beetle+Front',
    'cars/vw_beetle_side.jpg' => 'https://via.placeholder.com/400x300/FFFF00/000000?text=VW+Beetle+Side',
    
    // Фото событий
    'events/spring_meet_2024.jpg' => 'https://via.placeholder.com/600x400/4CAF50/FFFFFF?text=Spring+Meet+2024',
    'events/spring_meet_cars.jpg' => 'https://via.placeholder.com/600x400/4CAF50/FFFFFF?text=Spring+Meet+Cars',
    'events/summer_picnic_2024.jpg' => 'https://via.placeholder.com/600x400/FF9800/FFFFFF?text=Summer+Picnic+2024',
    'events/summer_picnic_bbq.jpg' => 'https://via.placeholder.com/600x400/FF9800/FFFFFF?text=Summer+Picnic+BBQ',
    'events/autumn_tour_start.jpg' => 'https://via.placeholder.com/600x400/795548/FFFFFF?text=Autumn+Tour+Start',
    'events/winter_meet_2024.jpg' => 'https://via.placeholder.com/600x400/607D8B/FFFFFF?text=Winter+Meet+2024',
    'events/photosession_2024.jpg' => 'https://via.placeholder.com/600x400/9C27B0/FFFFFF?text=Photosession+2024',
    
    // Фото гид-объектов
    'guide_objects/cabrio_service_exterior.jpg' => 'https://via.placeholder.com/500x300/3F51B5/FFFFFF?text=Cabrio+Service+Exterior',
    'guide_objects/cabrio_service_interior.jpg' => 'https://via.placeholder.com/500x300/3F51B5/FFFFFF?text=Cabrio+Service+Interior',
    'guide_objects/wind_cafe_exterior.jpg' => 'https://via.placeholder.com/500x300/FF5722/FFFFFF?text=Wind+Cafe+Exterior',
    'guide_objects/wind_cafe_interior.jpg' => 'https://via.placeholder.com/500x300/FF5722/FFFFFF?text=Wind+Cafe+Interior',
    'guide_objects/blisk_wash_exterior.jpg' => 'https://via.placeholder.com/500x300/00BCD4/FFFFFF?text=Blisk+Wash+Exterior',
    'guide_objects/cabrio_hotel_exterior.jpg' => 'https://via.placeholder.com/500x300/8BC34A/FFFFFF?text=Cabrio+Hotel+Exterior',
    'guide_objects/cabrio_hotel_room.jpg' => 'https://via.placeholder.com/500x300/8BC34A/FFFFFF?text=Cabrio+Hotel+Room',
    'guide_objects/express_fuel_station.jpg' => 'https://via.placeholder.com/500x300/FFC107/000000?text=Express+Fuel+Station',
    
    // Фото к отзывам
    'reviews/review_cabrio_service_work.jpg' => 'https://via.placeholder.com/400x300/3F51B5/FFFFFF?text=Service+Work',
    'reviews/review_cabrio_service_result.jpg' => 'https://via.placeholder.com/400x300/3F51B5/FFFFFF?text=Service+Result',
    'reviews/review_wind_cafe_food.jpg' => 'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Cafe+Food',
    'reviews/review_wind_cafe_parking.jpg' => 'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Cafe+Parking',
    'reviews/review_blisk_wash_process.jpg' => 'https://via.placeholder.com/400x300/00BCD4/FFFFFF?text=Wash+Process',
    'reviews/review_blisk_wash_result.jpg' => 'https://via.placeholder.com/400x300/00BCD4/FFFFFF?text=Wash+Result',
];

/**
 * Создает директорию если она не существует
 */
function createDirectory($path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✅ Создана директория: $path\n";
        } else {
            echo "❌ Ошибка создания директории: $path\n";
            return false;
        }
    }
    return true;
}

/**
 * Скачивает изображение по URL
 */
function downloadImage($url, $filePath) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'CabrioRide Test Image Downloader'
        ]
    ]);
    
    $imageData = file_get_contents($url, false, $context);
    
    if ($imageData === false) {
        echo "❌ Ошибка загрузки: $url\n";
        return false;
    }
    
    if (file_put_contents($filePath, $imageData) === false) {
        echo "❌ Ошибка сохранения: $filePath\n";
        return false;
    }
    
    return true;
}

// Основная логика
echo "🚀 Начинаем загрузку тестовых изображений...\n\n";

// Создаем базовую директорию
if (!createDirectory($baseDir)) {
    exit(1);
}

$successCount = 0;
$totalCount = count($imageUrls);

foreach ($imageUrls as $relativePath => $url) {
    $fullPath = $baseDir . '/' . $relativePath;
    $dirPath = dirname($fullPath);
    
    // Создаем директорию для файла
    if (!createDirectory($dirPath)) {
        continue;
    }
    
    // Скачиваем изображение
    if (downloadImage($url, $fullPath)) {
        echo "✅ Загружено: $relativePath\n";
        $successCount++;
    } else {
        echo "❌ Пропущено: $relativePath\n";
    }
}

echo "\n📊 Результат загрузки:\n";
echo "✅ Успешно загружено: $successCount из $totalCount\n";
echo "❌ Ошибок: " . ($totalCount - $successCount) . "\n";

if ($successCount === $totalCount) {
    echo "\n🎉 Все изображения успешно загружены!\n";
    echo "📁 Файлы сохранены в: $baseDir\n";
} else {
    echo "\n⚠️ Некоторые изображения не были загружены.\n";
    echo "Проверьте подключение к интернету и повторите попытку.\n";
}
?> 