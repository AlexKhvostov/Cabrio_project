<?php
/**
 * BaseController — базовый класс для всех контроллеров CabrioRide.
 * Здесь можно реализовать общие методы: формирование ответа, авторизация, валидация и т.д.
 */
class BaseController
{
    /**
     * Быстро вернуть JSON-ответ с нужным статусом.
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Здесь можно добавить общие методы: авторизация, валидация, логирование и т.д.
} 