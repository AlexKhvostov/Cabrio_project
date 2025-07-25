<?php
/**
 * Простой тест endpoint
 */

// Тестируем endpoint добавления авто
$testData = [
    'auth' => [
        'user_id' => 1,
        'role' => 'member'
    ],
    'data' => [
        'reg_number' => 'TEST123',
        'model' => 'Test Car',
        'year' => 2024,
        'color' => 'Red'
    ]
];

// Отправляем запрос
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/app/backend/api/cars/add.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";
?> 