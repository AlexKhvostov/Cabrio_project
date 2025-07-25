<?php
// Получаем BACKEND_API_URL из .env
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
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: Получение списка пользователей (GET /api/users)</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        pre { background: #f5f5f5; padding: 1em; }
        .ok { color: green; }
        .fail { color: red; }
    </style>
</head>
<body>
    <h2>Тест: Получение списка пользователей (GET /api/users)</h2>
    <button onclick="runTest()">Запустить тест</button>
    <div id="result"></div>
    <script>
        const BACKEND_API_URL = <?php echo json_encode($BACKEND_API_URL); ?>;
        const url = BACKEND_API_URL + '/routes/api.php?route=/api/users';
        async function runTest() {
            document.getElementById('result').innerHTML = '<b>Запрос:</b> <pre>GET ' + url + '</pre>';
            try {
                const response = await fetch(url, { method: 'GET' });
                const text = await response.text();
                let json;
                try { json = JSON.parse(text); } catch { json = null; }
                document.getElementById('result').innerHTML += '<b>Ответ:</b> <pre>' + text + '</pre>';
                if (json && json.success && Array.isArray(json.data)) {
                    document.getElementById('result').innerHTML += '<div class="ok">✅ Тест пройден: получен список пользователей (' + json.data.length + ')</div>';
                } else {
                    document.getElementById('result').innerHTML += '<div class="fail">❌ Тест не пройден: некорректный ответ</div>';
                }
            } catch (e) {
                document.getElementById('result').innerHTML += '<div class="fail">❌ Ошибка запроса: ' + e + '</div>';
            }
        }
    </script>
</body>
</html> 