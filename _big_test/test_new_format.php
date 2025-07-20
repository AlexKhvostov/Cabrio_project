<?php
/**
 * test_new_format.php
 * 
 * Тест нового формата ответа бота
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест нового формата ответа\n\n";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

// Симулируем разные ответы от API
$testResponses = [
    // Автомобиль найден
    [
        'success' => true,
        'ocr_success' => true,
        'found' => true,
        'plate' => '0070MX7',
        'status' => 'Активный'
    ],
    // Автомобиль не найден
    [
        'success' => true,
        'ocr_success' => true,
        'found' => false,
        'plate' => 'ABC123'
    ]
];

foreach ($testResponses as $i => $result) {
    echo "=== Тест " . ($i + 1) . " ===\n";
    
    if (!$result['success']) {
        echo "❌ Не удалось обработать фото\n\n";
        continue;
    }
    
    if (!$result['ocr_success']) {
        echo "❌ Не удалось распознать номер на фото\n\n";
        continue;
    }
    
    // Номер распознан успешно
    $plate = $result['plate'];
    
    // Формируем структурированный ответ
    $text = "Распознан номер\n";
    $text .= "🚗 " . $plate . "\n";
    
    if ($result['found']) {
        // Автомобиль найден в базе
        $status = $result['status'] ?? 'Неизвестно';
        $text .= "✅ " . $status;
    } else {
        // Автомобиль не найден в базе
        $text .= "🚫 Такой машины нет в базе данных";
    }
    
    // Добавляем вопрос о визитке
    $text .= "\n\n💼 Оставляешь визитку?";
    
    echo $text . "\n\n";
    
    // Показываем кнопки
    echo "Кнопки:\n";
    echo "✅ Да (callback_data: leave_card_" . $plate . ")\n";
    echo "❌ Нет (callback_data: cancel_card)\n\n";
}
?> 