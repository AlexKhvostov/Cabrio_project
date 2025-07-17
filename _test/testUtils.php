<?php
header('Content-Type: application/json; charset=utf-8');

function loadEnv($path) {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        list($k, $v) = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
    return $env;
}

$env = loadEnv(__DIR__ . '/../.env');

// Обработка action=get_token для генерации команды webhook
if (($_GET['action'] ?? '') === 'get_token') {
    $token = $env['BOT_TOKEN'] ?? null;
    if ($token) {
        echo json_encode(['token' => $token]);
    } else {
        echo json_encode(['error' => 'BOT_TOKEN не найден в .env']);
    }
    exit;
}

$host = $env['DB_HOST'] ?? 'localhost';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$db   = $env['DB_NAME'] ?? '';
$port = $env['DB_PORT'] ?? 3308;

$conn = @new mysqli($host, $user, $pass, $db, (int)$port);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Ошибка подключения к БД: ' . $conn->connect_error]);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'tables') {
    $res = $conn->query('SHOW TABLES');
    if (!$res) {
        echo json_encode(['error' => 'Ошибка запроса: ' . $conn->error]);
        exit;
    }
    $tables = [];
    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }
    echo json_encode(['tables' => $tables]);
    exit;
}

if ($action === 'fields' && isset($_GET['table'])) {
    $table = $conn->real_escape_string($_GET['table']);
    $res = $conn->query("SHOW COLUMNS FROM `{$table}`");
    if (!$res) {
        echo json_encode(['error' => 'Ошибка запроса: ' . $conn->error]);
        exit;
    }
    $fields = [];
    while ($row = $res->fetch_assoc()) {
        $fields[] = $row;
    }
    echo json_encode(['fields' => $fields]);
    exit;
}

echo json_encode(['error' => 'Некорректный запрос']); 