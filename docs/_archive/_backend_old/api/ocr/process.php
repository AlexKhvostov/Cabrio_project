<?php
/**
 * API Endpoint: Оркестрация OCR — распознавание номера и проверка в базе
 * POST /api/ocr/process.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class OcrProcessEndpoint extends ApiHandler {
    protected function process() {
        // Проверка прав (например, доступно всем)
        $this->checkAccess('guest');

        // Получаем изображение из data
        $image = $this->requireField('image', 'Изображение обязательно');

        // 1. Отправляем изображение на recognize.php (через внутренний HTTP-запрос)
        $recognizeResult = $this->callInternalEndpoint('/backend/api/ocr/recognize.php', [
            'auth' => $this->auth,
            'data' => ['image' => $image]
        ]);
        if (!$recognizeResult || !$recognizeResult['success'] || empty($recognizeResult['result']['data']['plate'])) {
            return $this->error('Ошибка распознавания: ' . ($recognizeResult['error']['message'] ?? 'Не удалось распознать номер'), 400, 'OCR_ERROR');
        }
        $plate = $recognizeResult['result']['data']['plate'];

        // 2. Проверяем номер через check.php
        $checkResult = $this->callInternalEndpoint('/backend/api/ocr/check.php', [
            'auth' => $this->auth,
            'data' => ['plate' => $plate]
        ]);

        // 3. Возвращаем объединённый результат
        return $this->success([
            'ocr' => $recognizeResult,
            'check' => $checkResult
        ]);
    }

    private function callInternalEndpoint($url, $payload) {
        // Формируем полный HTTP-адрес
        $endpointUrl = 'http://localhost/app' . $url;
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}

$endpoint = new OcrProcessEndpoint();
$endpoint->handle(); 