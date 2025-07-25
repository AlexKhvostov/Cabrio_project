<?php
$env_path = __DIR__ . '/../../.env';
$BACKEND_API_URL = '';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'BACKEND_API_URL=') === 0) {
            $BACKEND_API_URL = trim(substr($line, strlen('BACKEND_API_URL=')));
            break;
        }
    }
}
$TESTS_PATH = $BACKEND_API_URL . '/_tests';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интеграционные тесты backend CabrioRide</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        h2 { margin-bottom: 0.5em; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 0.7em; }
        a { color: #1976d2; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .desc { color: #555; font-size: 0.95em; }
    </style>
</head>
<body>
    <h2>Список интеграционных тестов backend</h2>
    <ul>
        <li>
            <a href="<?php echo htmlspecialchars($TESTS_PATH); ?>/users_list_test.php" target="_blank">users_list_test.html</a>
            <span class="desc">— Получение списка пользователей (GET /api/users)</span>
        </li>
        <!-- Добавляйте новые тесты ниже по аналогии -->
    </ul>
    <hr>
    <div style="color:#888;font-size:0.9em;">
        Формат: каждый тест — отдельный HTML-файл. Запускать через браузер по адресу <?php echo htmlspecialchars($TESTS_PATH); ?>/index.php
    </div>
</body>
</html> 