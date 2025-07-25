<?php
// Прямая загрузка .env только для Database.php
$env_path = __DIR__ . '/../../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

/**
 * Класс для работы с базой данных
 * Реализует паттерн Singleton для единого подключения
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        try {
            $host = $_ENV['DB_HOST'] ?? '';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            // Логируем параметры подключения
         //   error_log('PDO_CONNECT: host=' . $host . ' port=' . $port . ' dbname=' . $dbname . ' user=' . $user . ' password=***');

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                $host,
                $port,
                $dbname
            );
            
            $this->connection = new PDO(
                $dsn,
                $user,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
} 