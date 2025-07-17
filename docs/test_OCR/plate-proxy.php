<?php
// plate-proxy.php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['upload'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

// Попытка взять токен из .env (если настроено), иначе используем тестовый
$token = getenv('OCR_TOKEN') ?: 'adc5c5a5ea976e1b3bcb27d20f220f6c3c4506dc';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.platerecognizer.com/v1/plate-reader/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Token ' . $token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'upload' => new CURLFile($_FILES['upload']['tmp_name'], $_FILES['upload']['type'], $_FILES['upload']['name'])
]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpcode);
echo $response; 