<?php
/**
 * Базовый класс для обработки API запросов и ответов
 * Реализует стандарт API CabrioRide
 */

class ApiHandler {
    protected $request;
    protected $response;
    protected $auth;
    protected $data;
    protected $requestId;
    protected $timestamp;
    
    public function __construct() {
        $this->requestId = $this->generateRequestId();
        $this->timestamp = date('c'); // ISO 8601
        $this->parseRequest();
    }
    
    /**
     * Парсит входящий запрос
     */
    protected function parseRequest() {
        // Получаем JSON данные
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        // Извлекаем auth и data
        $this->auth = $input['auth'] ?? [];
        $this->data = $input['data'] ?? [];
        
        // Устанавливаем заголовки
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
    
    /**
     * Валидирует auth данные
     */
    protected function validateAuth() {
        if (empty($this->auth['user_id'])) {
            return $this->error('user_id обязателен в auth', 400, 'AUTH_ERROR');
        }
        
        if (empty($this->auth['role'])) {
            return $this->error('role обязателен в auth', 400, 'AUTH_ERROR');
        }
        
        return true;
    }
    
    /**
     * Проверяет права доступа
     */
    protected function checkAccess($requiredRole = 'guest') {
        $userRole = $this->auth['role'] ?? 'guest';
        
        $roles = [
            'external' => 0,
            'guest' => 1,
            'new' => 2,
            'registered' => 3,
            'member' => 4,
            'moderator' => 5,
            'admin' => 6
        ];
        
        $userLevel = $roles[$userRole] ?? -1;
        $requiredLevel = $roles[$requiredRole] ?? 999;
        
        if ($userLevel < $requiredLevel) {
            return $this->error("Недостаточно прав. Требуется роль: {$requiredRole}", 403, 'ACCESS_DENIED');
        }
        
        return true;
    }
    
    /**
     * Формирует успешный ответ
     */
    protected function success($data, $message = 'OK') {
        $response = [
            'success' => true,
            'timestamp' => $this->timestamp,
            'request_id' => $this->requestId,
            'auth' => [
                'user_id' => $this->auth['user_id'] ?? null,
                'role' => $this->auth['role'] ?? null
            ],
            'result' => [
                'message' => $message,
                'data' => $data
            ],
            'error' => null
        ];
        
        http_response_code(200);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Формирует ответ с ошибкой
     */
    protected function error($message, $code = 400, $type = 'VALIDATION_ERROR', $details = null) {
        $response = [
            'success' => false,
            'timestamp' => $this->timestamp,
            'request_id' => $this->requestId,
            'auth' => [
                'user_id' => $this->auth['user_id'] ?? null,
                'role' => $this->auth['role'] ?? null
            ],
            'result' => null,
            'error' => [
                'code' => $code,
                'type' => $type,
                'message' => $message,
                'details' => $details
            ]
        ];
        
        http_response_code($code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Генерирует уникальный ID запроса
     */
    protected function generateRequestId() {
        return 'req_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
    }
    
    /**
     * Получает значение из data
     */
    protected function getData($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Получает значение из auth
     */
    protected function getAuth($key, $default = null) {
        return $this->auth[$key] ?? $default;
    }
    
    /**
     * Проверяет обязательное поле
     */
    protected function requireField($field, $message = null) {
        $value = $this->getData($field);
        if (empty($value)) {
            $errorMessage = $message ?: "Поле {$field} обязательно";
            $this->error($errorMessage, 400, 'VALIDATION_ERROR', [
                'field' => $field,
                'rule' => 'required'
            ]);
        }
        return $value;
    }
    
    /**
     * Валидирует email
     */
    protected function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Неверный формат email', 400, 'VALIDATION_ERROR', [
                'field' => 'email',
                'rule' => 'email'
            ]);
        }
        return $email;
    }
    
    /**
     * Валидирует номер телефона
     */
    protected function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (strlen($phone) < 10) {
            $this->error('Неверный формат телефона', 400, 'VALIDATION_ERROR', [
                'field' => 'phone',
                'rule' => 'phone'
            ]);
        }
        return $phone;
    }
    
    /**
     * Валидирует регистрационный номер
     */
    protected function validateRegNumber($regNumber) {
        $regNumber = strtoupper(trim($regNumber));
        
        // Проверяем только цифры и английские буквы, минимум 5 символов
        if (!preg_match('/^[A-Z0-9]{5,}$/', $regNumber)) {
            $this->error('Неверный формат номера. Только цифры и английские буквы, минимум 5 символов', 400, 'VALIDATION_ERROR', [
                'field' => 'reg_number',
                'rule' => 'reg_number_format'
            ]);
        }
        
        return $regNumber;
    }
    
    /**
     * Логирует запрос
     */
    protected function logRequest($action, $details = []) {
        $logData = [
            'timestamp' => $this->timestamp,
            'request_id' => $this->requestId,
            'user_id' => $this->getAuth('user_id'),
            'action' => $action,
            'details' => $details
        ];
        
        $logEntry = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $logFile = __DIR__ . '/../logs/api.log';
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Получает подключение к БД
     */
    protected function getDb() {
        return Database::getInstance()->getConnection();
    }
    
    /**
     * Основной метод обработки запроса
     * Должен быть переопределён в наследниках
     */
    public function handle() {
        // Валидируем auth
        $authResult = $this->validateAuth();
        if ($authResult !== true) {
            return $authResult;
        }
        
        // Проверяем права доступа (по умолчанию guest)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Логируем запрос
        $this->logRequest('request_started');
        
        // Выполняем основную логику
        try {
            $result = $this->process();
            $this->logRequest('request_completed', ['success' => true]);
            return $result;
        } catch (Exception $e) {
            $this->logRequest('request_error', ['error' => $e->getMessage()]);
            return $this->error('Внутренняя ошибка сервера', 500, 'SERVER_ERROR');
        }
    }
    
    /**
     * Основная логика обработки
     * Должен быть переопределён в наследниках
     */
    protected function process() {
        $this->error('Метод process() должен быть переопределён', 500, 'NOT_IMPLEMENTED');
    }
}
?> 