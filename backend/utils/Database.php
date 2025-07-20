<?php
/**
 * Класс для работы с базой данных
 * Реализует паттерн Singleton для единого подключения
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        try {
            $host = getConfig('DB_HOST');
            $port = getConfig('DB_PORT', '3306');
            $dbname = getConfig('DB_NAME');
            $user = getConfig('DB_USER');
            $password = getConfig('DB_PASSWORD', ''); // Используем DB_PASSWORD вместо DB_PASS
            
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