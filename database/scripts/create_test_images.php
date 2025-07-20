<?php
/**
 * Скрипт для создания тестовых изображений локально
 * Не требует подключения к интернету
 */

// Конфигурация
$baseDir = __DIR__ . '/../../uploads';

// Массив изображений для создания
$images = [
    // Аватары пользователей (200x200)
    'avatars/lex_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => '4A90E2', 'text' => 'Lex'],
    'avatars/ivan_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => '7ED321', 'text' => 'Ivan'],
    'avatars/maria_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => 'F5A623', 'text' => 'Maria'],
    'avatars/dmitry_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => 'BD10E0', 'text' => 'Dmitry'],
    'avatars/anna_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => '50E3C2', 'text' => 'Anna'],
    'avatars/sergey_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => 'D0021B', 'text' => 'Sergey'],
    'avatars/elena_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => '9013FE', 'text' => 'Elena'],
    'avatars/pavel_avatar.jpg' => ['width' => 200, 'height' => 200, 'bg' => '417505', 'text' => 'Pavel'],
    
    // Фото автомобилей (400x300)
    'cars/bmw_3series_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => '000000', 'text' => 'BMW 3 Series Front'],
    'cars/bmw_3series_side.jpg' => ['width' => 400, 'height' => 300, 'bg' => '000000', 'text' => 'BMW 3 Series Side'],
    'cars/bmw_3series_interior.jpg' => ['width' => 400, 'height' => 300, 'bg' => '000000', 'text' => 'BMW 3 Series Interior'],
    'cars/mercedes_slk_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'CCCCCC', 'text' => 'Mercedes SLK Front'],
    'cars/mercedes_slk_top.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'CCCCCC', 'text' => 'Mercedes SLK Top'],
    'cars/audi_a3_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FFFFFF', 'text' => 'Audi A3 Front'],
    'cars/audi_a3_side.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FFFFFF', 'text' => 'Audi A3 Side'],
    'cars/porsche_boxster_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FF0000', 'text' => 'Porsche Boxster Front'],
    'cars/porsche_boxster_side.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FF0000', 'text' => 'Porsche Boxster Side'],
    'cars/mazda_mx5_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => '0000FF', 'text' => 'Mazda MX-5 Front'],
    'cars/mazda_mx5_top.jpg' => ['width' => 400, 'height' => 300, 'bg' => '0000FF', 'text' => 'Mazda MX-5 Top'],
    'cars/vw_beetle_front.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FFFF00', 'text' => 'VW Beetle Front'],
    'cars/vw_beetle_side.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FFFF00', 'text' => 'VW Beetle Side'],
    
    // Фото событий (600x400)
    'events/spring_meet_2024.jpg' => ['width' => 600, 'height' => 400, 'bg' => '4CAF50', 'text' => 'Spring Meet 2024'],
    'events/spring_meet_cars.jpg' => ['width' => 600, 'height' => 400, 'bg' => '4CAF50', 'text' => 'Spring Meet Cars'],
    'events/summer_picnic_2024.jpg' => ['width' => 600, 'height' => 400, 'bg' => 'FF9800', 'text' => 'Summer Picnic 2024'],
    'events/summer_picnic_bbq.jpg' => ['width' => 600, 'height' => 400, 'bg' => 'FF9800', 'text' => 'Summer Picnic BBQ'],
    'events/autumn_tour_start.jpg' => ['width' => 600, 'height' => 400, 'bg' => '795548', 'text' => 'Autumn Tour Start'],
    'events/winter_meet_2024.jpg' => ['width' => 600, 'height' => 400, 'bg' => '607D8B', 'text' => 'Winter Meet 2024'],
    'events/photosession_2024.jpg' => ['width' => 600, 'height' => 400, 'bg' => '9C27B0', 'text' => 'Photosession 2024'],
    
    // Фото гид-объектов (500x300)
    'guide_objects/cabrio_service_exterior.jpg' => ['width' => 500, 'height' => 300, 'bg' => '3F51B5', 'text' => 'Cabrio Service Exterior'],
    'guide_objects/cabrio_service_interior.jpg' => ['width' => 500, 'height' => 300, 'bg' => '3F51B5', 'text' => 'Cabrio Service Interior'],
    'guide_objects/wind_cafe_exterior.jpg' => ['width' => 500, 'height' => 300, 'bg' => 'FF5722', 'text' => 'Wind Cafe Exterior'],
    'guide_objects/wind_cafe_interior.jpg' => ['width' => 500, 'height' => 300, 'bg' => 'FF5722', 'text' => 'Wind Cafe Interior'],
    'guide_objects/blisk_wash_exterior.jpg' => ['width' => 500, 'height' => 300, 'bg' => '00BCD4', 'text' => 'Blisk Wash Exterior'],
    'guide_objects/cabrio_hotel_exterior.jpg' => ['width' => 500, 'height' => 300, 'bg' => '8BC34A', 'text' => 'Cabrio Hotel Exterior'],
    'guide_objects/cabrio_hotel_room.jpg' => ['width' => 500, 'height' => 300, 'bg' => '8BC34A', 'text' => 'Cabrio Hotel Room'],
    'guide_objects/express_fuel_station.jpg' => ['width' => 500, 'height' => 300, 'bg' => 'FFC107', 'text' => 'Express Fuel Station'],
    
    // Фото к отзывам (400x300)
    'reviews/review_cabrio_service_work.jpg' => ['width' => 400, 'height' => 300, 'bg' => '3F51B5', 'text' => 'Service Work'],
    'reviews/review_cabrio_service_result.jpg' => ['width' => 400, 'height' => 300, 'bg' => '3F51B5', 'text' => 'Service Result'],
    'reviews/review_wind_cafe_food.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FF5722', 'text' => 'Cafe Food'],
    'reviews/review_wind_cafe_parking.jpg' => ['width' => 400, 'height' => 300, 'bg' => 'FF5722', 'text' => 'Cafe Parking'],
    'reviews/review_blisk_wash_process.jpg' => ['width' => 400, 'height' => 300, 'bg' => '00BCD4', 'text' => 'Wash Process'],
    'reviews/review_blisk_wash_result.jpg' => ['width' => 400, 'height' => 300, 'bg' => '00BCD4', 'text' => 'Wash Result'],
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
 * Создает простое изображение с текстом
 */
function createImage($filePath, $width, $height, $bgColor, $text) {
    // Проверяем наличие GD extension
    if (!extension_loaded('gd')) {
        echo "❌ GD extension не установлен. Установите php-gd\n";
        return false;
    }
    
    // Создаем изображение
    $image = imagecreate($width, $height);
    
    // Парсим цвет фона
    $bg = sscanf($bgColor, "%02x%02x%02x");
    $bgColor = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
    
    // Определяем цвет текста (белый для темных фонов, черный для светлых)
    $textColor = imagecolorallocate($image, 255, 255, 255); // Белый по умолчанию
    
    // Для светлых фонов используем черный текст
    if ($bg[0] > 200 && $bg[1] > 200 && $bg[2] > 200) {
        $textColor = imagecolorallocate($image, 0, 0, 0);
    }
    
    // Заполняем фон
    imagefill($image, 0, 0, $bgColor);
    
    // Добавляем текст
    $fontSize = min($width, $height) / 15; // Уменьшаем размер шрифта
    
    // Пытаемся найти системный шрифт
    $fontPaths = [
        'C:/Windows/Fonts/arial.ttf',
        'C:/Windows/Fonts/calibri.ttf',
        'C:/Windows/Fonts/tahoma.ttf',
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
echo "🚀 Создаем тестовые изображения локально...\n\n";

// Создаем базовую директорию
if (!createDirectory($baseDir)) {
    exit(1);
}

$successCount = 0;
$totalCount = count($images);

foreach ($images as $relativePath => $config) {
    $fullPath = $baseDir . '/' . $relativePath;
    $dirPath = dirname($fullPath);
    
    echo "📸 Создаем: $relativePath\n";
    
    // Создаем директорию для файла
    if (!createDirectory($dirPath)) {
        continue;
    }
    
    // Создаем изображение
    if (createImage($fullPath, $config['width'], $config['height'], $config['bg'], $config['text'])) {
        echo "  ✅ Успешно создано\n";
        $successCount++;
    } else {
        echo "  ❌ Ошибка создания\n";
    }
    
    echo "\n";
}

echo "📊 Результат создания:\n";
echo "✅ Успешно создано: $successCount из $totalCount\n";
echo "❌ Ошибок: " . ($totalCount - $successCount) . "\n";

if ($successCount === $totalCount) {
    echo "\n🎉 Все изображения успешно созданы!\n";
    echo "📁 Файлы сохранены в: $baseDir\n";
} else {
    echo "\n⚠️ Некоторые изображения не были созданы.\n";
    echo "Проверьте наличие GD extension в PHP.\n";
}

echo "\n💡 Для установки GD extension в XAMPP:\n";
echo "1. Откройте php.ini в папке XAMPP\n";
echo "2. Найдите строку ';extension=gd'\n";
echo "3. Уберите точку с запятой: 'extension=gd'\n";
echo "4. Перезапустите Apache\n";
?> 