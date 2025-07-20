<?php
/**
 * Cars API endpoint
 * Проверка авто по номеру
 * 
 * POST /api/cars/check
 * Body: { "plate_number": "A123BC" }
 */

// Проверяем метод
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['plate_number'])) {
    Response::error('Plate number is required', 400);
}

// ВРЕМЕННО: Для номера 0070MX7 возвращаем найденное авто
if (strtoupper($data['plate_number']) === '0070MX7') {
    Response::success([
        'found' => true,
        'status' => 'active'
    ]);
} else {
    Response::success([
        'found' => false
    ]);
} 