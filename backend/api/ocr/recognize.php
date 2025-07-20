<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Проверяем, нет ли вывода до заголовков
ob_start();

// Устанавливаем заголовки
header('Content-Type: application/json');

// Отладка входящих данных
error_log("=== Начало запроса ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("CONTENT_TYPE: " . $_SERVER['CONTENT_TYPE']);
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
    $envFile = __DIR__ . '/../../../.env';
    error_log("Путь к .env: " . $envFile);
    error_log("Файл существует: " . (file_exists($envFile) ? 'да' : 'нет'));
    
    if (!file_exists($envFile)) {
        throw new Exception('Файл .env не найден: ' . $envFile);
    }
    
    // Читаем .env файл построчно, игнорируя комментарии и пустые строки
    $env = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Пропускаем комментарии и пустые строки
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Ищем знак равенства
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $env[$key] = $value;
        }
    }
    
    if (!isset($env['OCR_TOKEN'])) {
        throw new Exception('OCR_TOKEN не найден в .env');
    }

    $token = $env['OCR_TOKEN'];
    
    // Подготавливаем файл для отправки
    $image = curl_file_create(
        $_FILES['image']['tmp_name'],
        $_FILES['image']['type'],
        $_FILES['image']['name']
    );

    error_log("Подготовлен файл для отправки: " . print_r($image, true));

    // Отправляем запрос к API
    $ch = curl_init('https://api.platerecognizer.com/v1/plate-reader/');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'upload' => $image,
            'regions' => 'ru'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $token
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    error_log("CURL response code: " . $httpCode);
    error_log("CURL error: " . $curlError);
    error_log("API response: " . $response);

    if ($response === false) {
        throw new Exception("Ошибка CURL: " . $curlError);
    }

    if ($httpCode !== 200 && $httpCode !== 201) {
        throw new Exception("API вернул код: " . $httpCode . ", ответ: " . $response);
    }

    curl_close($ch);
    
    // Проверяем, что ответ - валидный JSON
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg() . '. Ответ: ' . $response);
    }

    if (!is_array($data)) {
        throw new Exception('Ответ API не является массивом. Получено: ' . gettype($data));
    }

    // Формируем успешный ответ
    $jsonResponse = json_encode([
        'success' => true,
        'results' => $data['results'] ?? []
    ]);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ошибка кодирования ответа в JSON: ' . json_last_error_msg());
    }

    // Проверяем, не было ли случайного вывода
    $output = ob_get_clean();
    if (!empty($output)) {
        error_log("Обнаружен непредвиденный вывод: " . $output);
    }

    echo $jsonResponse;

} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    
    $errorResponse = json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Если даже JSON с ошибкой не удалось закодировать, отправляем простой JSON
        echo json_encode([
            'success' => false,
            'error' => 'Критическая ошибка: не удалось закодировать сообщение об ошибке'
        ]);
    } else {
        echo $errorResponse;
    }
} 