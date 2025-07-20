<?php
/**
 * Альтернативный скрипт для загрузки тестовых изображений
 * Использует несколько сервисов и fallback опции
 */

// Конфигурация
$baseDir = __DIR__ . '/../../uploads';

// Массив URL с несколькими вариантами для каждого изображения
$imageUrls = [
    // Аватары пользователей
    'avatars/lex_avatar.jpg' => [
        'https://picsum.photos/200/200?random=1',
        'https://loremflickr.com/200/200/person?random=1',
        'https://via.placeholder.com/200x200/4A90E2/FFFFFF?text=Lex'
    ],
    'avatars/ivan_avatar.jpg' => [
        'https://picsum.photos/200/200?random=2',
        'https://loremflickr.com/200/200/person?random=2',
        'https://via.placeholder.com/200x200/7ED321/FFFFFF?text=Ivan'
    ],
    'avatars/maria_avatar.jpg' => [
        'https://picsum.photos/200/200?random=3',
        'https://loremflickr.com/200/200/person?random=3',
        'https://via.placeholder.com/200x200/F5A623/FFFFFF?text=Maria'
    ],
    'avatars/dmitry_avatar.jpg' => [
        'https://picsum.photos/200/200?random=4',
        'https://loremflickr.com/200/200/person?random=4',
        'https://via.placeholder.com/200x200/BD10E0/FFFFFF?text=Dmitry'
    ],
    'avatars/anna_avatar.jpg' => [
        'https://picsum.photos/200/200?random=5',
        'https://loremflickr.com/200/200/person?random=5',
        'https://via.placeholder.com/200x200/50E3C2/FFFFFF?text=Anna'
    ],
    'avatars/sergey_avatar.jpg' => [
        'https://picsum.photos/200/200?random=6',
        'https://loremflickr.com/200/200/person?random=6',
        'https://via.placeholder.com/200x200/D0021B/FFFFFF?text=Sergey'
    ],
    'avatars/elena_avatar.jpg' => [
        'https://picsum.photos/200/200?random=7',
        'https://loremflickr.com/200/200/person?random=7',
        'https://via.placeholder.com/200x200/9013FE/FFFFFF?text=Elena'
    ],
    'avatars/pavel_avatar.jpg' => [
        'https://picsum.photos/200/200?random=8',
        'https://loremflickr.com/200/200/person?random=8',
        'https://via.placeholder.com/200x200/417505/FFFFFF?text=Pavel'
    ],
    
    // Фото автомобилей
    'cars/bmw_3series_front.jpg' => [
        'https://picsum.photos/400/300?random=10',
        'https://loremflickr.com/400/300/car?random=10',
        'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Front'
    ],
    'cars/bmw_3series_side.jpg' => [
        'https://picsum.photos/400/300?random=11',
        'https://loremflickr.com/400/300/car?random=11',
        'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Side'
    ],
    'cars/bmw_3series_interior.jpg' => [
        'https://picsum.photos/400/300?random=12',
        'https://loremflickr.com/400/300/car?random=12',
        'https://via.placeholder.com/400x300/000000/FFFFFF?text=BMW+3+Series+Interior'
    ],
    'cars/mercedes_slk_front.jpg' => [
        'https://picsum.photos/400/300?random=13',
        'https://loremflickr.com/400/300/car?random=13',
        'https://via.placeholder.com/400x300/CCCCCC/000000?text=Mercedes+SLK+Front'
    ],
    'cars/mercedes_slk_top.jpg' => [
        'https://picsum.photos/400/300?random=14',
        'https://loremflickr.com/400/300/car?random=14',
        'https://via.placeholder.com/400x300/CCCCCC/000000?text=Mercedes+SLK+Top'
    ],
    'cars/audi_a3_front.jpg' => [
        'https://picsum.photos/400/300?random=15',
        'https://loremflickr.com/400/300/car?random=15',
        'https://via.placeholder.com/400x300/FFFFFF/000000?text=Audi+A3+Front'
    ],
    'cars/audi_a3_side.jpg' => [
        'https://picsum.photos/400/300?random=16',
        'https://loremflickr.com/400/300/car?random=16',
        'https://via.placeholder.com/400x300/FFFFFF/000000?text=Audi+A3+Side'
    ],
    'cars/porsche_boxster_front.jpg' => [
        'https://picsum.photos/400/300?random=17',
        'https://loremflickr.com/400/300/car?random=17',
        'https://via.placeholder.com/400x300/FF0000/FFFFFF?text=Porsche+Boxster+Front'
    ],
    'cars/porsche_boxster_side.jpg' => [
        'https://picsum.photos/400/300?random=18',
        'https://loremflickr.com/400/300/car?random=18',
        'https://via.placeholder.com/400x300/FF0000/FFFFFF?text=Porsche+Boxster+Side'
    ],
    'cars/mazda_mx5_front.jpg' => [
        'https://picsum.photos/400/300?random=19',
        'https://loremflickr.com/400/300/car?random=19',
        'https://via.placeholder.com/400x300/0000FF/FFFFFF?text=Mazda+MX-5+Front'
    ],
    'cars/mazda_mx5_top.jpg' => [
        'https://picsum.photos/400/300?random=20',
        'https://loremflickr.com/400/300/car?random=20',
        'https://via.placeholder.com/400x300/0000FF/FFFFFF?text=Mazda+MX-5+Top'
    ],
    'cars/vw_beetle_front.jpg' => [
        'https://picsum.photos/400/300?random=21',
        'https://loremflickr.com/400/300/car?random=21',
        'https://via.placeholder.com/400x300/FFFF00/000000?text=VW+Beetle+Front'
    ],
    'cars/vw_beetle_side.jpg' => [
        'https://picsum.photos/400/300?random=22',
        'https://loremflickr.com/400/300/car?random=22',
        'https://via.placeholder.com/400x300/FFFF00/000000?text=VW+Beetle+Side'
    ],
    
    // Фото событий
    'events/spring_meet_2024.jpg' => [
        'https://picsum.photos/600/400?random=30',
        'https://loremflickr.com/600/400/meeting?random=30',
        'https://via.placeholder.com/600x400/4CAF50/FFFFFF?text=Spring+Meet+2024'
    ],
    'events/spring_meet_cars.jpg' => [
        'https://picsum.photos/600/400?random=31',
        'https://loremflickr.com/600/400/cars?random=31',
        'https://via.placeholder.com/600x400/4CAF50/FFFFFF?text=Spring+Meet+Cars'
    ],
    'events/summer_picnic_2024.jpg' => [
        'https://picsum.photos/600/400?random=32',
        'https://loremflickr.com/600/400/picnic?random=32',
        'https://via.placeholder.com/600x400/FF9800/FFFFFF?text=Summer+Picnic+2024'
    ],
    'events/summer_picnic_bbq.jpg' => [
        'https://picsum.photos/600/400?random=33',
        'https://loremflickr.com/600/400/bbq?random=33',
        'https://via.placeholder.com/600x400/FF9800/FFFFFF?text=Summer+Picnic+BBQ'
    ],
    'events/autumn_tour_start.jpg' => [
        'https://picsum.photos/600/400?random=34',
        'https://loremflickr.com/600/400/tour?random=34',
        'https://via.placeholder.com/600x400/795548/FFFFFF?text=Autumn+Tour+Start'
    ],
    'events/winter_meet_2024.jpg' => [
        'https://picsum.photos/600/400?random=35',
        'https://loremflickr.com/600/400/meeting?random=35',
        'https://via.placeholder.com/600x400/607D8B/FFFFFF?text=Winter+Meet+2024'
    ],
    'events/photosession_2024.jpg' => [
        'https://picsum.photos/600/400?random=36',
        'https://loremflickr.com/600/400/photography?random=36',
        'https://via.placeholder.com/600x400/9C27B0/FFFFFF?text=Photosession+2024'
    ],
    
    // Фото гид-объектов
    'guide_objects/cabrio_service_exterior.jpg' => [
        'https://picsum.photos/500/300?random=40',
        'https://loremflickr.com/500/300/service?random=40',
        'https://via.placeholder.com/500x300/3F51B5/FFFFFF?text=Cabrio+Service+Exterior'
    ],
    'guide_objects/cabrio_service_interior.jpg' => [
        'https://picsum.photos/500/300?random=41',
        'https://loremflickr.com/500/300/workshop?random=41',
        'https://via.placeholder.com/500x300/3F51B5/FFFFFF?text=Cabrio+Service+Interior'
    ],
    'guide_objects/wind_cafe_exterior.jpg' => [
        'https://picsum.photos/500/300?random=42',
        'https://loremflickr.com/500/300/cafe?random=42',
        'https://via.placeholder.com/500x300/FF5722/FFFFFF?text=Wind+Cafe+Exterior'
    ],
    'guide_objects/wind_cafe_interior.jpg' => [
        'https://picsum.photos/500/300?random=43',
        'https://loremflickr.com/500/300/restaurant?random=43',
        'https://via.placeholder.com/500x300/FF5722/FFFFFF?text=Wind+Cafe+Interior'
    ],
    'guide_objects/blisk_wash_exterior.jpg' => [
        'https://picsum.photos/500/300?random=44',
        'https://loremflickr.com/500/300/carwash?random=44',
        'https://via.placeholder.com/500x300/00BCD4/FFFFFF?text=Blisk+Wash+Exterior'
    ],
    'guide_objects/cabrio_hotel_exterior.jpg' => [
        'https://picsum.photos/500/300?random=45',
        'https://loremflickr.com/500/300/hotel?random=45',
        'https://via.placeholder.com/500x300/8BC34A/FFFFFF?text=Cabrio+Hotel+Exterior'
    ],
    'guide_objects/cabrio_hotel_room.jpg' => [
        'https://picsum.photos/500/300?random=46',
        'https://loremflickr.com/500/300/room?random=46',
        'https://via.placeholder.com/500x300/8BC34A/FFFFFF?text=Cabrio+Hotel+Room'
    ],
    'guide_objects/express_fuel_station.jpg' => [
        'https://picsum.photos/500/300?random=47',
        'https://loremflickr.com/500/300/gasstation?random=47',
        'https://via.placeholder.com/500x300/FFC107/000000?text=Express+Fuel+Station'
    ],
    
    // Фото к отзывам
    'reviews/review_cabrio_service_work.jpg' => [
        'https://picsum.photos/400/300?random=50',
        'https://loremflickr.com/400/300/repair?random=50',
        'https://via.placeholder.com/400x300/3F51B5/FFFFFF?text=Service+Work'
    ],
    'reviews/review_cabrio_service_result.jpg' => [
        'https://picsum.photos/400/300?random=51',
        'https://loremflickr.com/400/300/fixed?random=51',
        'https://via.placeholder.com/400x300/3F51B5/FFFFFF?text=Service+Result'
    ],
    'reviews/review_wind_cafe_food.jpg' => [
        'https://picsum.photos/400/300?random=52',
        'https://loremflickr.com/400/300/food?random=52',
        'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Cafe+Food'
    ],
    'reviews/review_wind_cafe_parking.jpg' => [
        'https://picsum.photos/400/300?random=53',
        'https://loremflickr.com/400/300/parking?random=53',
        'https://via.placeholder.com/400x300/FF5722/FFFFFF?text=Cafe+Parking'
    ],
    'reviews/review_blisk_wash_process.jpg' => [
        'https://picsum.photos/400/300?random=54',
        'https://loremflickr.com/400/300/washing?random=54',
        'https://via.placeholder.com/400x300/00BCD4/FFFFFF?text=Wash+Process'
    ],
    'reviews/review_blisk_wash_result.jpg' => [
        'https://picsum.photos/400/300?random=55',
        'https://loremflickr.com/400/300/clean?random=55',
        'https://via.placeholder.com/400x300/00BCD4/FFFFFF?text=Wash+Result'
    ],
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
 * Скачивает изображение по URL с несколькими попытками
 */
function downloadImage($urls, $filePath) {
    foreach ($urls as $index => $url) {
        echo "  Попытка " . ($index + 1) . " из " . count($urls) . ": $url\n";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'CabrioRide Test Image Downloader'
            ]
        ]);
        
        $imageData = @file_get_contents($url, false, $context);
        
        if ($imageData !== false && strlen($imageData) > 1000) { // Проверяем что файл не пустой
            if (file_put_contents($filePath, $imageData) !== false) {
                return true;
            }
        }
        
        echo "    ❌ Не удалось загрузить\n";
    }
    
    return false;
}

/**
 * Создает простое изображение с текстом
 */
function createSimpleImage($filePath, $width, $height, $bgColor, $textColor, $text) {
    // Создаем простое изображение с помощью GD
    if (!extension_loaded('gd')) {
        echo "    ❌ GD extension не установлен\n";
        return false;
    }
    
    $image = imagecreate($width, $height);
    
    // Парсим цвета
    $bg = sscanf($bgColor, "%02x%02x%02x");
    $text = sscanf($textColor, "%02x%02x%02x");
    
    $bgColor = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
    $textColor = imagecolorallocate($image, $text[0], $text[1], $text[2]);
    
    // Заполняем фон
    imagefill($image, 0, 0, $bgColor);
    
    // Добавляем текст
    $fontSize = min($width, $height) / 10;
    $fontFile = 'arial.ttf'; // Используем системный шрифт
    
    // Пытаемся найти шрифт
    $fontPaths = [
        'C:/Windows/Fonts/arial.ttf',
        'C:/Windows/Fonts/calibri.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/System/Library/Fonts/Arial.ttf'
    ];
    
    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }
    
    if ($fontFile) {
        // Используем TrueType шрифт
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        
        $x = ($width - $textWidth) / 2;
        $y = ($height + $textHeight) / 2;
        
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $text);
    } else {
        // Используем встроенный шрифт
        $textWidth = strlen($text) * imagefontwidth(5);
        $textHeight = imagefontheight(5);
        
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        imagestring($image, 5, $x, $y, $text, $textColor);
    }
    
    // Сохраняем изображение
    $result = imagejpeg($image, $filePath, 90);
    imagedestroy($image);
    
    return $result;
}

// Основная логика
echo "🚀 Начинаем загрузку тестовых изображений...\n\n";

// Создаем базовую директорию
if (!createDirectory($baseDir)) {
    exit(1);
}

$successCount = 0;
$totalCount = count($imageUrls);

foreach ($imageUrls as $relativePath => $urls) {
    $fullPath = $baseDir . '/' . $relativePath;
    $dirPath = dirname($fullPath);
    
    echo "📸 Загружаем: $relativePath\n";
    
    // Создаем директорию для файла
    if (!createDirectory($dirPath)) {
        continue;
    }
    
    // Пытаемся скачать изображение
    if (downloadImage($urls, $fullPath)) {
        echo "  ✅ Успешно загружено\n";
        $successCount++;
    } else {
        echo "  ❌ Все попытки неудачны, создаем простое изображение\n";
        
        // Создаем простое изображение как fallback
        $filename = basename($relativePath, '.jpg');
        $width = 400;
        $height = 300;
        
        // Определяем размеры по типу
        if (strpos($relativePath, 'avatars/') === 0) {
            $width = $height = 200;
        } elseif (strpos($relativePath, 'events/') === 0) {
            $width = 600;
            $height = 400;
        } elseif (strpos($relativePath, 'guide_objects/') === 0) {
            $width = 500;
            $height = 300;
        }
        
        if (createSimpleImage($fullPath, $width, $height, '4A90E2', 'FFFFFF', $filename)) {
            echo "  ✅ Создано простое изображение\n";
            $successCount++;
        } else {
            echo "  ❌ Не удалось создать изображение\n";
        }
    }
    
    echo "\n";
}

echo "📊 Результат загрузки:\n";
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