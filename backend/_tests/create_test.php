<?php
/**
 * Скрипт для создания нового теста из шаблона
 * 
 * Использование:
 * 1. Откройте в браузере: http://localhost:8000/_tests/create_test.php
 * 2. Заполните форму
 * 3. Нажмите "Создать тест"
 * 4. Скопируйте сгенерированный код в новый файл
 */

$test_config = [
    'id' => $_POST['id'] ?? '',
    'name' => $_POST['name'] ?? '',
    'description' => $_POST['description'] ?? '',
    'endpoint' => $_POST['endpoint'] ?? '',
    'method' => $_POST['method'] ?? 'GET',
    'icon' => $_POST['icon'] ?? '🔧',
    'data_name' => $_POST['data_name'] ?? 'записей'
];

$generated_code = '';

if ($_POST && !empty($_POST['id'])) {
    // Генерируем код теста
    $generated_code = generateTestCode($test_config);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание нового теста</title>
    <style>
        body { font-family: sans-serif; margin: 2em; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2em; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1em; }
        label { display: block; margin-bottom: 0.5em; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 0.5em; border: 1px solid #ddd; border-radius: 4px; font-size: 1em; }
        textarea { height: 100px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button:hover { background: #0056b3; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .generated-code { background: #f8f9fa; padding: 1em; border-radius: 4px; margin-top: 1em; font-family: monospace; white-space: pre-wrap; }
        .instructions { background: #e9ecef; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="index.php">← Назад к списку тестов</a>
        </div>
        
        <h1>🔧 Создание нового теста</h1>
        
        <div class="instructions">
            <strong>Инструкция:</strong><br>
            1. Заполните форму ниже<br>
            2. Нажмите "Создать тест"<br>
            3. Скопируйте сгенерированный код<br>
            4. Создайте новый файл с именем <code>{id}_test.php</code><br>
            5. Вставьте код в файл
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="id">ID теста (без _test.php):</label>
                <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($test_config['id']); ?>" placeholder="users_list" required>
                <small>Будет создан файл: {id}_test.php</small>
            </div>
            
            <div class="form-group">
                <label for="name">Название теста:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($test_config['name']); ?>" placeholder="Список пользователей" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" placeholder="Получение списка пользователей с ролями и фото"><?php echo htmlspecialchars($test_config['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="endpoint">API Endpoint:</label>
                <input type="text" id="endpoint" name="endpoint" value="<?php echo htmlspecialchars($test_config['endpoint']); ?>" placeholder="/api/users" required>
            </div>
            
            <div class="form-group">
                <label for="method">HTTP Метод:</label>
                <select id="method" name="method">
                    <option value="GET" <?php echo $test_config['method'] === 'GET' ? 'selected' : ''; ?>>GET</option>
                    <option value="POST" <?php echo $test_config['method'] === 'POST' ? 'selected' : ''; ?>>POST</option>
                    <option value="PUT" <?php echo $test_config['method'] === 'PUT' ? 'selected' : ''; ?>>PUT</option>
                    <option value="DELETE" <?php echo $test_config['method'] === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="icon">Иконка:</label>
                <input type="text" id="icon" name="icon" value="<?php echo htmlspecialchars($test_config['icon']); ?>" placeholder="👤">
            </div>
            
            <div class="form-group">
                <label for="data_name">Название данных:</label>
                <input type="text" id="data_name" name="data_name" value="<?php echo htmlspecialchars($test_config['data_name']); ?>" placeholder="пользователей">
                <small>Например: пользователей, автомобилей, событий</small>
            </div>
            
            <button type="submit">🔧 Создать тест</button>
        </form>
        
        <?php if ($generated_code): ?>
        <h3>📋 Сгенерированный код:</h3>
        <div class="generated-code"><?php echo htmlspecialchars($generated_code); ?></div>
        
        <h3>📝 Следующие шаги:</h3>
        <ol>
            <li>Создайте файл: <code><?php echo htmlspecialchars($test_config['id']); ?>_test.php</code></li>
            <li>Скопируйте код выше в файл</li>
            <li>Сохраните файл</li>
            <li>Добавьте тест в <code>tests_config.json</code></li>
            <li>Проверьте работу теста</li>
        </ol>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
function generateTestCode($config) {
    return '<?php
// Получаем BACKEND_API_URL из .env
$env_path = __DIR__ . \'/../../.env\';
$BACKEND_API_URL = \'\';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, \'BACKEND_API_URL=\') === 0) {
            $BACKEND_API_URL = trim(substr($line, strlen(\'BACKEND_API_URL=\')));
            break;
        }
    }
}

// Конфигурация теста
$test_config = [
    \'id\' => \'' . $config['id'] . '\',
    \'name\' => \'' . $config['name'] . '\',
    \'description\' => \'' . $config['description'] . '\',
    \'endpoint\' => \'' . $config['endpoint'] . '\',
    \'method\' => \'' . $config['method'] . '\',
    \'icon\' => \'' . $config['icon'] . '\',
    \'data_name\' => \'' . $config['data_name'] . '\'
];

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: ' . $config['name'] . ' (' . $config['method'] . ' ' . $config['endpoint'] . ')</title>
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
    
    <h2>' . $config['icon'] . ' Тест: ' . $config['name'] . '</h2>
    
    <!-- Информация о конфигурации теста -->
    <div class="config-info">
        <strong>Конфигурация теста:</strong><br>
        Endpoint: ' . $config['method'] . ' ' . $config['endpoint'] . '<br>
        Описание: ' . $config['description'] . '
    </div>
    
    <button onclick="runTest()">▶️ Запустить тест</button>
    <div id="result"></div>
    
    <script>
        const BACKEND_API_URL = <?php echo json_encode($BACKEND_API_URL); ?>;
        const url = BACKEND_API_URL + \'/routes/api.php?route=' . $config['endpoint'] . '\';
        const testConfig = <?php echo json_encode($test_config); ?>;
        
        async function runTest() {
            const resultDiv = document.getElementById(\'result\');
            resultDiv.innerHTML = \'<div class="info">🔄 Выполняется тест...</div>\';
            
            try {
                const response = await fetch(url, { method: \'' . $config['method'] . '\' });
                const text = await response.text();
                let json;
                try { 
                    json = JSON.parse(text); 
                } catch (e) { 
                    json = null; 
                }
                
                let html = \'<div class="test-result \' + (json && json.success ? \'success\' : \'error\') + \'">\';
                html += \'<h3>\' + (json && json.success ? \'✅ Тест пройден!\' : \'❌ Тест не пройден!\') + \'</h3>\';
                html += \'<div><strong>Запрос:</strong> ' . $config['method'] . ' \' + url + \'</div>\';
                html += \'<div><strong>Статус:</strong> \' + response.status + \'</div>\';
                
                if (json && json.success && json.data) {
                    html += \'<div><strong>Найдено ' . $config['data_name'] . ':</strong> \' + json.data.length + \'</div>\';
                    
                    if (json.data.length > 0) {
                        html += \'<h4>📋 Пример данных:</h4>\';
                        html += \'<pre>\' + JSON.stringify(json.data[0], null, 2) + \'</pre>\';
                    }
                } else {
                    html += \'<div class="fail">❌ Ошибка: \' + text + \'</div>\';
                }
                
                html += \'</div>\';
                resultDiv.innerHTML = html;
                
            } catch (e) {
                resultDiv.innerHTML = \'<div class="test-result error">❌ Ошибка выполнения: \' + e.message + \'</div>\';
            }
        }
    </script>
</body>
</html>';
}
?> 