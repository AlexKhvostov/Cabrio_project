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

// Конфигурация теста - ИЗМЕНИТЕ ЭТИ ПАРАМЕТРЫ ДЛЯ НОВОГО ТЕСТА
$test_config = [
    'id' => 'test_name',                    // ID теста (без _test.php)
    'name' => 'Название теста',             // Отображаемое название
    'description' => 'Описание теста',      // Описание что тестирует
    'endpoint' => '/api/endpoint',          // API endpoint
    'method' => 'GET',                      // HTTP метод
    'icon' => '🔧',                         // Иконка для отображения
    'data_name' => 'записей'               // Название данных (записей, пользователей, автомобилей и т.д.)
];

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: <?php echo $test_config['name']; ?> (<?php echo $test_config['method']; ?> <?php echo $test_config['endpoint']; ?>)</title>
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
        .config-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="back-link">
        <a href="index.php">← Назад к списку тестов</a>
    </div>
    
    <h2><?php echo $test_config['icon']; ?> Тест: <?php echo $test_config['name']; ?></h2>
    
    <!-- Информация о конфигурации теста -->
    <div class="config-info">
        <strong>Конфигурация теста:</strong><br>
        Endpoint: <?php echo $test_config['method']; ?> <?php echo $test_config['endpoint']; ?><br>
        Описание: <?php echo $test_config['description']; ?>
    </div>
    
    <button onclick="runTest()">▶️ Запустить тест</button>
    <div id="result"></div>
    
    <script>
        const BACKEND_API_URL = <?php echo json_encode($BACKEND_API_URL); ?>;
        const url = BACKEND_API_URL + '/routes/api.php?route=<?php echo $test_config['endpoint']; ?>';
        const testConfig = <?php echo json_encode($test_config); ?>;
        
        async function runTest() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="info">🔄 Выполняется тест...</div>';
            
            try {
                const response = await fetch(url, { method: '<?php echo $test_config['method']; ?>' });
                const text = await response.text();
                let json;
                try { 
                    json = JSON.parse(text); 
                } catch (e) { 
                    json = null; 
                }
                
                let html = '<div class="test-result ' + (json && json.success ? 'success' : 'error') + '">';
                html += '<h3>' + (json && json.success ? '✅ Тест пройден!' : '❌ Тест не пройден!') + '</h3>';
                html += '<div><strong>Запрос:</strong> <?php echo $test_config['method']; ?> ' + url + '</div>';
                html += '<div><strong>Статус:</strong> ' + response.status + '</div>';
                
                if (json && json.success && json.data) {
                    html += '<div><strong>Найдено <?php echo $test_config['data_name']; ?>:</strong> ' + json.data.length + '</div>';
                    
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