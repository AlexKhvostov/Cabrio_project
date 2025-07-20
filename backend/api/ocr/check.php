<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Проверяем, нет ли вывода до заголовков
ob_start();

// Устанавливаем заголовки
header('Content-Type: application/json');

// Отладка входящих данных
error_log("=== OCR Check API ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
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
    // Проверяем наличие номера
    if (!isset($_POST['plate'])) {
        throw new Exception('Номер не передан. Используйте параметр "plate".');
    }

    $plate = trim($_POST['plate']);
    if (empty($plate)) {
        throw new Exception('Номер не может быть пустым.');
    }

    error_log("Проверяем номер: " . $plate);

    // Загружаем конфигурацию и Database класс
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../utils/Database.php';

    // Отладочная информация о параметрах БД
    error_log("DB_HOST: " . (getConfig('DB_HOST') ?? 'не задан'));
    error_log("DB_PORT: " . (getConfig('DB_PORT') ?? 'не задан'));
    error_log("DB_USER: " . (getConfig('DB_USER') ?? 'не задан'));
    error_log("DB_NAME: " . (getConfig('DB_NAME') ?? 'не задан'));
    error_log("DB_PASSWORD: " . (getConfig('DB_PASSWORD') ? 'задан' : 'не задан'));

    // Получаем подключение к БД через Singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Ищем автомобиль в базе
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
    
    $stmt->execute([$plate]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($car) {
        // Автомобиль найден - только номер и статус (без личных данных)
        $response = [
            'success' => true,
            'found' => true,
            'plate' => $car['reg_number'],
            'status' => $car['status_name'],
            'message' => 'Автомобиль найден в базе клуба',
            'can_leave_card' => true // Бот может предложить оставить визитку
        ];
        
        error_log("Автомобиль найден: " . json_encode($response));
        
    } else {
        // Автомобиль не найден - простой ответ
        $response = [
            'success' => true,
            'found' => false,
            'plate' => $plate,
            'message' => 'Автомобиль с таким номером не найден в базе клуба',
            'can_leave_card' => false
        ];
        
        error_log("Автомобиль не найден: " . $plate);
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