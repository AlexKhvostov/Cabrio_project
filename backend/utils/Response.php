<?php
/**
 * Класс для форматирования ответов API
 */

class Response {
    public static function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public static function success($data = null, $message = 'OK') {
        self::json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }
    
    public static function error($message, $code = 400) {
        self::json([
            'success' => false,
            'error' => $message
        ], $code);
    }
} 