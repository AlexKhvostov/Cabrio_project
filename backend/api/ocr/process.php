<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Проверяем, нет ли вывода до заголовков
ob_start();

// Устанавливаем заголовки
header('Content-Type: application/json');

// Отладка входящих данных
error_log("=== OCR Process API ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("FILES: " . print_r($_FILES, true));
error_log("POST: " . print_r($_POST, true));

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Метод не поддерживается. Используйте POST.'
    ]);
    exit;
}

try {
    // Проверяем наличие файла
    if (empty($_FILES)) {
        throw new Exception('Файлы не были переданы');
    }

    if (!isset($_FILES['image'])) {
        throw new Exception('Файл не передан. Параметр должен называться "image". Получены параметры: ' . implode(', ', array_keys($_FILES)));
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Ошибка загрузки файла: ' . $_FILES['image']['error']);
    }

    // Проверяем файл
    if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
        throw new Exception('Файл не был загружен через POST');
    }

    // Загружаем конфигурацию
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../utils/Database.php';

    // Получаем токен OCR
    $ocrToken = getConfig('OCR_TOKEN');
    if (!$ocrToken) {
        throw new Exception('OCR_TOKEN не найден в .env');
    }

    error_log("Начинаем распознавание номера...");

    // Шаг 1: Распознавание номера через OCR
    $image = curl_file_create(
        $_FILES['image']['tmp_name'],
        $_FILES['image']['type'],
        $_FILES['image']['name']
    );

    $ch = curl_init('https://api.platerecognizer.com/v1/plate-reader/');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'upload' => $image,
            'regions' => 'ru'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $ocrToken
        ]
    ]);

    $ocrResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    error_log("OCR response code: " . $httpCode);
    error_log("OCR response: " . $ocrResponse);

    if ($ocrResponse === false) {
        throw new Exception("Ошибка CURL: " . $curlError);
    }

    if ($httpCode !== 200 && $httpCode !== 201) {
        throw new Exception("OCR API вернул код: " . $httpCode . ", ответ: " . $ocrResponse);
    }

    curl_close($ch);
    
    // Проверяем, что ответ - валидный JSON
    $ocrData = json_decode($ocrResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ошибка декодирования JSON от OCR: ' . json_last_error_msg());
    }

    // Проверяем, есть ли результаты распознавания
    if (!isset($ocrData['results']) || empty($ocrData['results'])) {
        $response = [
            'success' => true,
            'ocr_success' => false,
            'message' => 'Не удалось распознать номер на изображении',
            'can_leave_card' => false
        ];
        
        echo json_encode($response);
        exit;
    }

    // Берем первый распознанный номер
    $recognizedPlate = $ocrData['results'][0]['plate'];
    $confidence = $ocrData['results'][0]['score'];
    
    error_log("Распознан номер: " . $recognizedPlate . " (уверенность: " . $confidence . ")");

    // Шаг 2: Проверка номера в базе данных
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.reg_number,
            c.model,
            c.year,
            c.status_id,
            s.name as status_name,
            cb.brand,
            u.first_name_app,
            u.last_name_app,
            r.code as owner_role
        FROM cars c
        LEFT JOIN ref_car_brands cb ON c.car_brand_id = cb.id
        LEFT JOIN users u ON c.owner_user_id = u.id
        LEFT JOIN ref_roles r ON u.role_id = r.id
        LEFT JOIN ref_statuses s ON c.status_id = s.id
        WHERE c.reg_number = ?
    ");
    
    $stmt->execute([$recognizedPlate]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    // Формируем итоговый ответ
    if ($car) {
        // Автомобиль найден
        $response = [
            'success' => true,
            'ocr_success' => true,
            'found' => true,
            'plate' => $recognizedPlate,
            'confidence' => $confidence,
            'status' => $car['status_name'],
            'message' => 'Автомобиль найден в базе клуба',
            'can_leave_card' => true
        ];
        
        error_log("Автомобиль найден в БД: " . json_encode($response));
        
    } else {
        // Автомобиль не найден
        $response = [
            'success' => true,
            'ocr_success' => true,
            'found' => false,
            'plate' => $recognizedPlate,
            'confidence' => $confidence,
            'message' => 'Автомобиль с таким номером не найден в базе клуба',
            'can_leave_card' => false
        ];
        
        error_log("Автомобиль не найден в БД: " . $recognizedPlate);
    }

    // Проверяем, не было ли случайного вывода
    $output = ob_get_clean();
    if (!empty($output)) {
        error_log("Обнаружен непредвиденный вывод: " . $output);
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    
    $errorResponse = json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'error' => 'Критическая ошибка: не удалось закодировать сообщение об ошибке'
        ]);
    } else {
        echo $errorResponse;
    }
} 