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

// Загружаем конфигурацию тестов
$config_path = __DIR__ . '/tests_config.json';
$tests_config = [];
if (file_exists($config_path)) {
    $tests_config = json_decode(file_get_contents($config_path), true);
} else {
    // Если файл не найден, создаем базовую конфигурацию
    $tests_config = [
        'tests' => [
            [
                'id' => 'users_list',
                'name' => 'Список пользователей',
                'description' => 'Получение списка пользователей с ролями и фото',
                'endpoint' => '/api/users',
                'method' => 'GET',
                'expected' => [
                    'success' => true,
                    'has_data' => true,
                    'data_type' => 'array'
                ],
                'icon' => '👤',
                'category' => 'users'
            ],
            [
                'id' => 'cars_list',
                'name' => 'Список автомобилей',
                'description' => 'Получение списка автомобилей с марками, владельцами и фото',
                'endpoint' => '/api/cars',
                'method' => 'GET',
                'expected' => [
                    'success' => true,
                    'has_data' => true,
                    'data_type' => 'array'
                ],
                'icon' => '🚗',
                'category' => 'cars'
            ]
        ],
        'config' => [
            'base_url' => 'http://localhost:8000',
            'timeout' => 5000,
            'auto_run' => true
        ]
    ];
}

$TESTS_PATH = $BACKEND_API_URL . '/_tests';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>API Tests</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8f9fa; 
            padding: 15px;
            font-size: 14px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 1.5em;
            font-weight: 300;
            margin-bottom: 5px;
        }
        .header .subtitle {
            opacity: 0.9;
            font-size: 0.9em;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .stat {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 1.4em;
            font-weight: bold;
            color: #495057;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.8em;
            margin-top: 3px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .content {
            padding: 15px;
        }
        .controls {
            text-align: center;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.9em;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .loading {
            text-align: center;
            padding: 15px;
            color: #6c757d;
            font-size: 0.9em;
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .tests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .tests-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 0.85em;
            border-bottom: 2px solid #dee2e6;
        }
        .tests-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 0.85em;
            vertical-align: middle;
        }
        .tests-table tr:hover {
            background: #f8f9fa;
        }
        .test-icon {
            font-size: 1.2em;
            text-align: center;
            width: 30px;
        }
        .test-name {
            font-weight: 600;
            color: #495057;
            min-width: 200px;
        }
        .test-endpoint {
            font-family: monospace;
            color: #6c757d;
            font-size: 0.8em;
            min-width: 150px;
        }
        .test-status {
            text-align: center;
            font-weight: 600;
            min-width: 60px;
        }
        .test-status.success {
            color: #28a745;
        }
        .test-status.error {
            color: #dc3545;
        }
        .test-status.pending {
            color: #6c757d;
        }
        .test-status.planned {
            color: #ffc107;
        }
        .test-result {
            font-size: 0.8em;
            color: #6c757d;
            min-width: 120px;
        }
        .test-result.success {
            color: #28a745;
        }
        .test-result.error {
            color: #dc3545;
        }
        .test-time {
            text-align: center;
            font-family: monospace;
            font-size: 0.8em;
            color: #6c757d;
            min-width: 80px;
        }
        .test-details {
            text-align: center;
            min-width: 80px;
        }
        .test-details a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }
        .test-details a:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .debug {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 API Tests</h1>
            <div class="subtitle">CabrioRide — автоматическое тестирование</div>
        </div>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number" id="total-tests">0</div>
                <div class="stat-label">Всего</div>
            </div>
            <div class="stat">
                <div class="stat-number success" id="passed-tests">0</div>
                <div class="stat-label">Пройдено</div>
            </div>
            <div class="stat">
                <div class="stat-number error" id="failed-tests">0</div>
                <div class="stat-label">Ошибок</div>
            </div>
            <div class="stat">
                <div class="stat-number warning" id="success-rate">0%</div>
                <div class="stat-label">Успешность</div>
            </div>
        </div>
        
        <div class="content">
            <div class="controls">
                <button class="btn" onclick="runAllTests()">▶️ Запустить все</button>
                <button class="btn btn-secondary" onclick="clearResults()">�� Очистить</button>
                <a href="create_test.php" class="btn btn-secondary" style="text-decoration: none;">🔧 Создать тест</a>
            </div>
            
            <div class="loading" id="loading" style="display: none;">
                <div class="spinner"></div>
                Выполняются тесты...
            </div>
            
            <!-- Отладочная информация -->
            <div class="debug" id="debug-info">
                Загрузка конфигурации...
            </div>
            
            <!-- Таблица тестов -->
            <table class="tests-table" id="tests-table">
                <thead>
                    <tr>
                        <th>Тест</th>
                        <th>Название</th>
                        <th>Endpoint</th>
                        <th>Статус</th>
                        <th>Результат</th>
                        <th>Время</th>
                        <th>Детали</th>
                    </tr>
                </thead>
                <tbody id="tests-tbody">
                    <!-- Тесты будут добавлены через JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        const testsConfig = <?php echo json_encode($tests_config); ?>;
        const baseUrl = '<?php echo $BACKEND_API_URL; ?>';
        let testResults = {};
        
        // Отладочная информация
        document.getElementById('debug-info').innerHTML = `
            <strong>Отладка:</strong><br>
            Конфигурация загружена: ${testsConfig ? 'Да' : 'Нет'}<br>
            Количество тестов: ${testsConfig.tests ? testsConfig.tests.length : 0}<br>
            Base URL: ${baseUrl}<br>
            Авто-запуск: ${testsConfig.config ? testsConfig.config.auto_run : 'Нет'}
        `;
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM загружен');
            console.log('Конфигурация тестов:', testsConfig);
            renderTests();
            if (testsConfig.config && testsConfig.config.auto_run) {
                setTimeout(runAllTests, 1000);
            }
        });
        
        // Рендеринг тестов
        function renderTests() {
            console.log('Рендеринг тестов...');
            const tbody = document.getElementById('tests-tbody');
            tbody.innerHTML = '';
            
            if (!testsConfig.tests || testsConfig.tests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #6c757d;">Нет тестов для отображения</td></tr>';
                return;
            }
            
            testsConfig.tests.forEach(test => {
                const row = createTestRow(test);
                tbody.appendChild(row);
            });
            
            updateStats();
        }
        
        // Создание строки теста
        function createTestRow(test) {
            const row = document.createElement('tr');
            row.id = `test-row-${test.id}`;
            
            const status = testResults[test.id] || 'pending';
            const result = testResults[test.id];
            
            row.innerHTML = `
                <td class="test-icon">${test.icon}</td>
                <td class="test-name">${test.name}</td>
                <td class="test-endpoint">${test.method} ${test.endpoint}</td>
                <td class="test-status ${status}" id="status-${test.id}">${getStatusText(status)}</td>
                <td class="test-result ${status}" id="result-${test.id}">${getResultText(test.id, status)}</td>
                <td class="test-time" id="time-${test.id}">${getTimeText(test.id, status)}</td>
                <td class="test-details">
                    <a href="${test.id}_test.php" target="_blank">🔍 Детали</a>
                </td>
            `;
            
            return row;
        }
        
        // Получение текста статуса
        function getStatusText(status) {
            switch(status) {
                case 'success': return '✅';
                case 'error': return '❌';
                case 'pending': return '⏳';
                case 'planned': return '📋';
                default: return '⏳';
            }
        }
        
        // Получение текста результата
        function getResultText(testId, status) {
            const result = testResults[testId];
            if (status === 'success' && result) {
                return `${result.count} записей`;
            } else if (status === 'error' && result) {
                return result.error.substring(0, 30) + (result.error.length > 30 ? '...' : '');
            } else if (status === 'planned') {
                return 'В разработке';
            } else {
                return 'Ожидает';
            }
        }
        
        // Получение текста времени
        function getTimeText(testId, status) {
            const result = testResults[testId];
            if (status === 'success' && result) {
                return `${result.time}ms`;
            } else if (status === 'error' && result) {
                return `${result.time}ms`;
            } else {
                return '-';
            }
        }
        
        // Запуск всех тестов
        async function runAllTests() {
            console.log('Запуск всех тестов...');
            const loading = document.getElementById('loading');
            loading.style.display = 'block';
            
            testResults = {};
            renderTests();
            
            if (!testsConfig.tests || testsConfig.tests.length === 0) {
                loading.style.display = 'none';
                return;
            }
            
            for (const test of testsConfig.tests) {
                if (test.status === 'planned') continue;
                
                try {
                    const result = await runTest(test);
                    testResults[test.id] = result;
                    updateTestRow(test.id, result);
                } catch (error) {
                    testResults[test.id] = { success: false, error: error.message, time: 0 };
                    updateTestRow(test.id, { success: false, error: error.message, time: 0 });
                }
            }
            
            loading.style.display = 'none';
            updateStats();
        }
        
        // Запуск одного теста
        async function runTest(test) {
            const startTime = Date.now();
            const url = baseUrl + '/routes/api.php?route=' + test.endpoint;
            
            const response = await fetch(url, { 
                method: test.method,
                timeout: testsConfig.config.timeout 
            });
            
            const text = await response.text();
            const data = JSON.parse(text);
            const time = Date.now() - startTime;
            
            // Проверка ожидаемого результата
            const expected = test.expected;
            let success = true;
            let error = '';
            
            if (!data.success && expected.success) {
                success = false;
                error = 'API вернул success: false';
            } else if (data.success && expected.has_data && (!data.data || !Array.isArray(data.data))) {
                success = false;
                error = 'Ожидался массив данных';
            }
            
            return {
                success,
                count: data.data ? data.data.length : 0,
                time,
                error: success ? '' : error
            };
        }
        
        // Обновление строки теста
        function updateTestRow(testId, result) {
            const row = document.getElementById(`test-row-${testId}`);
            if (!row) return;
            
            const statusEl = document.getElementById(`status-${testId}`);
            const resultEl = document.getElementById(`result-${testId}`);
            const timeEl = document.getElementById(`time-${testId}`);
            
            const status = result.success ? 'success' : 'error';
            
            statusEl.className = 'test-status ' + status;
            statusEl.textContent = getStatusText(status);
            
            if (resultEl) {
                resultEl.className = 'test-result ' + status;
                resultEl.textContent = getResultText(testId, status);
            }
            
            if (timeEl) {
                timeEl.textContent = getTimeText(testId, status);
            }
        }
        
        // Обновление статистики
        function updateStats() {
            const tests = testsConfig.tests ? testsConfig.tests.filter(t => t.status !== 'planned') : [];
            const total = tests.length;
            const passed = Object.values(testResults).filter(r => r && r.success).length;
            const failed = total - passed;
            const rate = total > 0 ? Math.round((passed / total) * 100) : 0;
            
            document.getElementById('total-tests').textContent = total;
            document.getElementById('passed-tests').textContent = passed;
            document.getElementById('failed-tests').textContent = failed;
            document.getElementById('success-rate').textContent = rate + '%';
        }
        
        // Очистка результатов
        function clearResults() {
            testResults = {};
            renderTests();
        }
    </script>
</body>
</html> 