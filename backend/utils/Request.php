<?php
/**
 * Базовый класс для работы с HTTP запросами
 * Предоставляет методы для получения данных запроса и контекста
 */

class Request {
    private $user = null;
    private $session = null;
    private $function = null;
    
    /**
     * Получает заголовок запроса
     */
    public function getHeader($name) {
        $headerName = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$headerName] ?? null;
    }
    
    /**
     * Получает данные из POST запроса
     */
    public function getData() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    /**
     * Получает параметры из URL
     */
    public function getParams() {
        return $_GET;
    }
    
    /**
     * Получает параметр из URL
     */
    public function getParam($name, $default = null) {
        return $_GET[$name] ?? $default;
    }
    
    /**
     * Получает метод запроса
     */
    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Получает URL запроса
     */
    public function getUrl() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Устанавливает функцию для проверки прав
     */
    public function setFunction($function) {
        $this->function = $function;
    }
    
    /**
     * Получает функцию для проверки прав
     */
    public function getFunction() {
        return $this->function;
    }
    
    /**
     * Устанавливает пользователя в контекст
     */
    public function setUser($user) {
        $this->user = $user;
    }
    
    /**
     * Получает пользователя из контекста
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Устанавливает сессию в контекст
     */
    public function setSession($session) {
        $this->session = $session;
    }
    
    /**
     * Получает сессию из контекста
     */
    public function getSession() {
        return $this->session;
    }
    
    /**
     * Проверяет, является ли запрос AJAX
     */
    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Получает IP адрес клиента
     */
    public function getClientIp() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
               $_SERVER['HTTP_X_REAL_IP'] ?? 
               $_SERVER['REMOTE_ADDR'] ?? 
               'unknown';
    }
    
    /**
     * Получает User Agent
     */
    public function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
} 