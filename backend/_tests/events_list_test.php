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
    <title>Тест: Получение списка событий (GET /api/events)</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        pre { background: #f5f5f5; padding: 1em; border-radius: 4px; }
        .ok { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .info { color: blue; }
        .test-result { margin: 1em 0; padding: 1em; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        button { padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="back-link">
        <a href="index.php">← Назад к списку тестов</a>
    </div>
    
    <h2>📅 Тест получения списка событий</h2>
    <button onclick="runTest()">▶️ Запустить тест</button>
    <div id="result"></div>
    
    <script>
        const BACKEND_API_URL = <?php echo json_encode($BACKEND_API_URL); ?>;
        const url = BACKEND_API_URL + '/routes/api.php?route=/api/events';
        
        async function runTest() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="info">🔄 Выполняется тест...</div>';
            
            try {
                const response = await fetch(url, { method: 'GET' });
                const text = await response.text();
                let json;
                try { 
                    json = JSON.parse(text); 
                } catch (e) { 
                    json = null; 
                }
                
                let html = '<div class="test-result ' + (json && json.success ? 'success' : 'error') + '">';
                html += '<h3>' + (json && json.success ? '✅ Тест пройден!' : '❌ Тест не пройден!') + '</h3>';
                html += '<div><strong>Запрос:</strong> GET ' + url + '</div>';
                html += '<div><strong>Статус:</strong> ' + response.status + '</div>';
                
                if (json && json.success && json.data) {
                    html += '<div><strong>Найдено событий:</strong> ' + json.data.length + '</div>';
                    
                    if (json.data.length > 0) {
                        html += '<h4>📋 Пример данных:</h4>';
                        html += '<pre>' + JSON.stringify(json.data[0], null, 2) + '</pre>';
                    }
                } else {
                    html += '<div class="fail">❌ Ошибка: ' + text + '</div>';
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
                
            } catch (e) {
                resultDiv.innerHTML = '<div class="test-result error">❌ Ошибка выполнения: ' + e.message + '</div>';
            }
        }
    </script>
</body>
</html> 