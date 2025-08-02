<?php
// Конфигурация тестов
$tests_config = [
    'base_url' => 'http://localhost/app/backend',
    'tests' => [
        // L1 Actions
        [
            'id' => 'l1_create_user',
            'name' => 'L1: Создание пользователя',
            'description' => 'Создание нового пользователя с Telegram ID',
            'endpoint' => '/actions/level1/_CreateUserAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true,
                'data_type' => 'object'
            ],
            'icon' => '👤',
            'category' => 'l1_users'
        ],
        [
            'id' => 'l1_check_user',
            'name' => 'L1: Проверка пользователя',
            'description' => 'Проверка существования пользователя по Telegram ID',
            'endpoint' => '/actions/level1/_CheckUserByTelegramIdAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🔍',
            'category' => 'l1_users'
        ],
        [
            'id' => 'l1_update_user',
            'name' => 'L1: Обновление пользователя',
            'description' => 'Обновление данных пользователя',
            'endpoint' => '/actions/level1/_UpdateUserAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '✏️',
            'category' => 'l1_users'
        ],
        [
            'id' => 'l1_update_role',
            'name' => 'L1: Обновление роли',
            'description' => 'Обновление роли пользователя',
            'endpoint' => '/actions/level1/_UpdateRoleUserAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true
            ],
            'icon' => '👑',
            'category' => 'l1_users'
        ],
        [
            'id' => 'l1_create_car',
            'name' => 'L1: Создание автомобиля',
            'description' => 'Создание нового автомобиля',
            'endpoint' => '/actions/level1/_CreateCarAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🚗',
            'category' => 'l1_cars'
        ],
        [
            'id' => 'l1_check_car',
            'name' => 'L1: Проверка автомобиля',
            'description' => 'Проверка существования автомобиля по номеру',
            'endpoint' => '/actions/level1/_CheckCarInDbAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true
            ],
            'icon' => '🔍',
            'category' => 'l1_cars'
        ],
        [
            'id' => 'l1_update_status',
            'name' => 'L1: Обновление статуса',
            'description' => 'Обновление статуса сущности',
            'endpoint' => '/actions/level1/_UpdateStatusAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true
            ],
            'icon' => '📊',
            'category' => 'l1_cars'
        ],
        [
            'id' => 'l1_update_owner',
            'name' => 'L1: Назначение владельца',
            'description' => 'Назначение владельца автомобилю',
            'endpoint' => '/actions/level1/_UpdateOwnerToCarAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true
            ],
            'icon' => '👤',
            'category' => 'l1_cars'
        ],
        [
            'id' => 'l1_create_business_card',
            'name' => 'L1: Создание визитки',
            'description' => 'Создание новой визитки',
            'endpoint' => '/actions/level1/_CreateBusinessCardAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📇',
            'category' => 'l1_business_cards'
        ],
        [
            'id' => 'l1_create_photo',
            'name' => 'L1: Создание фото',
            'description' => 'Создание новой фотографии',
            'endpoint' => '/actions/level1/_CreatePhotoAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📸',
            'category' => 'l1_photos'
        ],
        
        // L2 Actions
        [
            'id' => 'l2_sync_user',
            'name' => 'L2: Синхронизация пользователя',
            'description' => 'Создание/обновление пользователя с Telegram',
            'endpoint' => '/actions/level2/__SyncUserDataAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🔄',
            'category' => 'l2_users'
        ],
        [
            'id' => 'l2_search_car',
            'name' => 'L2: Поиск автомобиля',
            'description' => 'Поиск автомобиля по номеру',
            'endpoint' => '/actions/level2/__SearchCarAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🔍',
            'category' => 'l2_cars'
        ],
        [
            'id' => 'l2_add_car_to_user',
            'name' => 'L2: Добавление в гараж',
            'description' => 'Добавление автомобиля пользователю',
            'endpoint' => '/actions/level2/__AddCarToUserAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🚗',
            'category' => 'l2_cars'
        ],
        [
            'id' => 'l2_drop_business_card',
            'name' => 'L2: Оставление визитки',
            'description' => 'Оставление визитки на автомобиле',
            'endpoint' => '/actions/level2/__DropBusinessCardAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📇',
            'category' => 'l2_business_cards'
        ],
        
        // L3 Actions
        [
            'id' => 'l3_check_car_in_club',
            'name' => 'L3: Проверка в клубе',
            'description' => 'Проверка автомобиля в клубе с OCR',
            'endpoint' => '/actions/level3/___CheckCarInClubAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🏁',
            'category' => 'l3_cars'
        ],
        [
            'id' => 'l3_leave_business_card',
            'name' => 'L3: Оставление визитки',
            'description' => 'Оставление визитки с OCR',
            'endpoint' => '/actions/level3/___LeaveBusinessCardAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📇',
            'category' => 'l3_business_cards'
        ],
        [
            'id' => 'l3_add_car_to_garage',
            'name' => 'L3: Добавление в гараж',
            'description' => 'Добавление в гараж с OCR',
            'endpoint' => '/actions/level3/___AddCarToGarageAction.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🚗',
            'category' => 'l3_cars'
        ],
        
        // Models
        [
            'id' => 'models_user',
            'name' => 'Модель: Пользователь',
            'description' => 'Тестирование модели User',
            'endpoint' => '/_tests/test_models_api.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '👤',
            'category' => 'models'
        ],
        [
            'id' => 'models_car',
            'name' => 'Модель: Автомобиль',
            'description' => 'Тестирование модели Car',
            'endpoint' => '/_tests/test_models_api.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🚗',
            'category' => 'models'
        ],
        [
            'id' => 'models_business_card',
            'name' => 'Модель: Визитка',
            'description' => 'Тестирование модели BusinessCard',
            'endpoint' => '/_tests/test_models_api.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📇',
            'category' => 'models'
        ],
        [
            'id' => 'models_photo',
            'name' => 'Модель: Фото',
            'description' => 'Тестирование модели Photo',
            'endpoint' => '/_tests/test_models_api.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '📸',
            'category' => 'models'
        ],
        
        // OCR
        [
            'id' => 'ocr_test',
            'name' => 'OCR: Распознавание номера',
            'description' => 'Тестирование OCR распознавания',
            'endpoint' => '/_tests/test_ocr_api.php',
            'method' => 'POST',
            'expected' => [
                'success' => true,
                'has_data' => true
            ],
            'icon' => '🔍',
            'category' => 'ocr'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 CabrioRide Tests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .results {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .results-header {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .results-content {
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
        }
        
        .test-result {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .test-result.success {
            background: #d5f4e6;
            border-left-color: #27ae60;
        }
        
        .test-result.error {
            background: #fadbd8;
            border-left-color: #e74c3c;
        }
        
        .test-result.pending {
            background: #fef9e7;
            border-left-color: #f39c12;
        }
        
        .test-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .test-status {
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .test-status.success {
            color: #27ae60;
        }
        
        .test-status.error {
            color: #e74c3c;
        }
        
        .test-status.pending {
            color: #f39c12;
        }
        
        .test-details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .stats {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
        
        .tests-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tests-header {
            background: #34495e;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .test-icon {
            font-size: 1.2em;
            margin-right: 8px;
        }
        
        .test-description {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .run-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .run-btn:hover {
            background: #229954;
        }
        
        .run-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        
        .category-header {
            background: #e9ecef;
            padding: 8px 12px;
            font-weight: 600;
            color: #495057;
            font-size: 0.9em;
            border-left: 4px solid #007bff;
        }
        
        .category-l1 { border-left-color: #28a745; }
        .category-l2 { border-left-color: #ffc107; }
        .category-l3 { border-left-color: #dc3545; }
        .category-models { border-left-color: #6f42c1; }
        .category-ocr { border-left-color: #fd7e14; }
        
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.8em;
            color: #6c757d;
            margin-top: 10px;
        }
        
        .manual-tests {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .manual-tests h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        .manual-tests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        
        .manual-test-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .manual-test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .manual-test-btn .test-icon {
            font-size: 1.5em;
            margin-bottom: 6px;
        }
        
        .manual-test-btn .test-name {
            font-weight: 600;
            font-size: 0.9em;
            margin-bottom: 3px;
        }
        
        .manual-test-btn .test-desc {
            font-size: 0.75em;
            opacity: 0.9;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 CabrioRide Tests</h1>
        <div class="subtitle">Тестирование L1, L2, L3 Actions, Моделей и OCR</div>
        
        <div class="controls">
            <button class="btn" onclick="runAllTests()">▶️ Запустить все</button>
            <button class="btn btn-secondary" onclick="runCategoryTests('l1')">L1 Actions</button>
            <button class="btn btn-secondary" onclick="runCategoryTests('l2')">L2 Actions</button>
            <button class="btn btn-secondary" onclick="runCategoryTests('l3')">L3 Actions</button>
            <button class="btn btn-secondary" onclick="runCategoryTests('models')">Модели</button>
            <button class="btn btn-secondary" onclick="runCategoryTests('ocr')">OCR</button>
            <button class="btn btn-secondary" onclick="clearResults()">🔄 Очистить</button>
            <a href="create_test.php" class="btn btn-secondary" style="text-decoration: none;">🔧 Создать тест</a>
        </div>
        
        <div class="manual-tests">
            <h3>🖱️ Ручные тесты</h3>
            <div class="manual-tests-grid">
                <a href="test_l1_actions_web.html" class="manual-test-btn" target="_blank">
                    <span class="test-icon">👤</span>
                    <span class="test-name">L1 Actions</span>
                    <span class="test-desc">Ручное тестирование L1 Actions</span>
                </a>
                <a href="test_l2_actions_web.html" class="manual-test-btn" target="_blank">
                    <span class="test-icon">🔄</span>
                    <span class="test-name">L2 Actions</span>
                    <span class="test-desc">Ручное тестирование L2 Actions</span>
                </a>
                <a href="test_l3_actions_web.html" class="manual-test-btn" target="_blank">
                    <span class="test-icon">🏁</span>
                    <span class="test-name">L3 Actions</span>
                    <span class="test-desc">Ручное тестирование L3 Actions с OCR</span>
                </a>
                <a href="test_models_web.html" class="manual-test-btn" target="_blank">
                    <span class="test-icon">🗄️</span>
                    <span class="test-name">Модели</span>
                    <span class="test-desc">Ручное тестирование моделей</span>
                </a>
                <a href="test_ocr_web.html" class="manual-test-btn" target="_blank">
                    <span class="test-icon">🔍</span>
                    <span class="test-name">OCR</span>
                    <span class="test-desc">Ручное тестирование OCR</span>
                </a>
            </div>
        </div>
        
        <div class="stats">
            <div id="stats-content">
                Загружается статистика...
            </div>
        </div>
        
        <div class="results">
            <div class="results-header">
                📊 Результаты тестов
            </div>
            <div class="results-content" id="results">
                <div style="text-align: center; color: #7f8c8d; padding: 40px;">
                    Нажмите "Запустить все" для начала тестирования
                </div>
            </div>
        </div>
        
        <div class="tests-table">
            <div class="tests-header">
                📋 Список тестов
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Тест</th>
                        <th>Описание</th>
                        <th>Метод</th>
                        <th>Эндпоинт</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody id="tests-table-body">
                    <!-- Тесты будут добавлены через JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const baseUrl = '<?php echo $tests_config['base_url']; ?>';
        const testsConfig = <?php echo json_encode($tests_config); ?>;
        
        let testResults = {};
        let isRunning = false;
        
        // Категории тестов
        const categories = {
            'l1_users': { name: 'L1: Пользователи', icon: '👤' },
            'l1_cars': { name: 'L1: Автомобили', icon: '🚗' },
            'l1_business_cards': { name: 'L1: Визитки', icon: '📇' },
            'l1_photos': { name: 'L1: Фотографии', icon: '📸' },
            'l2_users': { name: 'L2: Пользователи', icon: '🔄' },
            'l2_cars': { name: 'L2: Автомобили', icon: '🔍' },
            'l2_business_cards': { name: 'L2: Визитки', icon: '📇' },
            'l3_cars': { name: 'L3: Автомобили', icon: '🏁' },
            'l3_business_cards': { name: 'L3: Визитки', icon: '📇' },
            'models': { name: 'Модели', icon: '🗄️' },
            'ocr': { name: 'OCR', icon: '🔍' }
        };
        
        function renderTests() {
            const tbody = document.getElementById('tests-table-body');
            tbody.innerHTML = '';
            
            // Группируем тесты по категориям
            Object.keys(categories).forEach(category => {
                const categoryTests = testsConfig.tests.filter(test => test.category === category);
                if (categoryTests.length > 0) {
                    // Добавляем заголовок категории
                    const headerRow = document.createElement('tr');
                    headerRow.innerHTML = `
                        <td colspan="6" class="category-header category-${category.replace('_', '')}">
                            ${categories[category].icon} ${categories[category].name} (${categoryTests.length})
                        </td>
                    `;
                    tbody.appendChild(headerRow);
                    
                    // Добавляем тесты категории
                    categoryTests.forEach(test => {
                        const row = createTestRow(test);
                        tbody.appendChild(row);
                    });
                }
            });
        }
        
        function createTestRow(test) {
            const row = document.createElement('tr');
            const status = testResults[test.id]?.status || 'pending';
            const statusClass = status === 'success' ? 'success' : status === 'error' ? 'error' : 'pending';
            
            row.innerHTML = `
                <td>
                    <span class="test-icon">${test.icon}</span>
                    ${test.name}
                </td>
                <td>
                    <div class="test-description">${test.description}</div>
                </td>
                <td>${test.method}</td>
                <td style="font-family: monospace; font-size: 0.9em;">${test.endpoint}</td>
                <td>
                    <span class="test-status ${statusClass}">
                        ${status === 'success' ? '✅ Успех' : 
                          status === 'error' ? '❌ Ошибка' : 
                          status === 'running' ? '🔄 Выполняется' : '⏳ Ожидает'}
                    </span>
                </td>
                <td>
                    <button class="run-btn" onclick="runTest('${test.id}')" 
                            ${isRunning ? 'disabled' : ''}>
                        ▶️ Запустить
                    </button>
                </td>
            `;
            
            return row;
        }
        
        async function runTest(testId) {
            if (isRunning) return;
            
            const test = testsConfig.tests.find(t => t.id === testId);
            if (!test) return;
            
            isRunning = true;
            updateTestStatus(testId, 'running');
            updateResults();
            
            try {
                // Определяем URL в зависимости от типа эндпоинта
                let url;
                if (test.endpoint.startsWith('/actions/')) {
                    url = baseUrl + test.endpoint;
                } else if (test.endpoint.startsWith('/_tests/')) {
                    url = baseUrl + test.endpoint;
                } else {
                    url = baseUrl + '/routes/api.php?route=' + test.endpoint;
                }
                
                // Подготавливаем данные для теста
                const testData = getTestData(test);
                
                const response = await fetch(url, {
                    method: test.method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });
                
                const result = await response.text();
                let jsonResult;
                
                try {
                    jsonResult = JSON.parse(result);
                } catch (e) {
                    jsonResult = { error: 'Неверный JSON ответ', raw: result };
                }
                
                const success = jsonResult.success === true;
                updateTestStatus(testId, success ? 'success' : 'error');
                updateTestResult(testId, {
                    status: success ? 'success' : 'error',
                    data: jsonResult,
                    raw: result
                });
                
            } catch (error) {
                updateTestStatus(testId, 'error');
                updateTestResult(testId, {
                    status: 'error',
                    error: error.message,
                    data: null
                });
            } finally {
                isRunning = false;
                updateResults();
            }
        }
        
        function getTestData(test) {
            const timestamp = Date.now();
            const testTelegramId = Math.floor(Math.random() * 900000) + 100000;
            
            switch (test.id) {
                case 'l1_create_user':
                    return {
                        telegram_id: testTelegramId,
                        username: `test_user_${timestamp}`,
                        first_name: 'Тест',
                        last_name: 'Пользователь'
                    };
                    
                case 'l1_check_user':
                    return {
                        telegram_id: 563 // Используем существующий ID
                    };
                    
                case 'l1_update_user':
                    return {
                        id: 563,
                        first_name: 'Обновленный',
                        last_name: 'Пользователь',
                        email: `test_${timestamp}@example.com`
                    };
                    
                case 'l1_update_role':
                    return {
                        user_id: 563,
                        role_id: 4 // member
                    };
                    
                case 'l1_create_car':
                    return {
                        reg_number: `TEST${timestamp}`,
                        create_user_id: 563,
                        owner_user_id: null
                    };
                    
                case 'l1_check_car':
                    return {
                        plate_number: 'TEST123'
                    };
                    
                case 'l1_update_status':
                    return {
                        entity_type: 'car',
                        entity_id: 1,
                        status_id: 2
                    };
                    
                case 'l1_update_owner':
                    return {
                        car_id: 1,
                        user_id: 563
                    };
                    
                case 'l1_create_business_card':
                    return {
                        car_id: 1,
                        user_id: 563,
                        message: 'Тестовая визитка'
                    };
                    
                case 'l1_create_photo':
                    return {
                        entity_type: 'car',
                        entity_id: 1,
                        description: 'Тестовое фото'
                    };
                    
                case 'l2_sync_user':
                    return {
                        telegram_id: testTelegramId,
                        username: `sync_user_${timestamp}`,
                        first_name: 'Синхронизированный',
                        last_name: 'Пользователь'
                    };
                    
                case 'l2_search_car':
                    return {
                        plate_number: `SEARCH${timestamp}`,
                        create_user_id: 563
                    };
                    
                case 'l2_add_car_to_user':
                    return {
                        plate_number: `GARAGE${timestamp}`,
                        user_id: 563
                    };
                    
                case 'l2_drop_business_card':
                    return {
                        plate_number: `CARD${timestamp}`,
                        user_id: 563,
                        message: 'Тестовая визитка'
                    };
                    
                case 'l3_check_car_in_club':
                case 'l3_leave_business_card':
                case 'l3_add_car_to_garage':
                    return {
                        user_id: 563
                    };
                    
                case 'models_user':
                    return {
                        action: 'create_user',
                        data: {
                            telegram_id: testTelegramId,
                            username: `model_user_${timestamp}`
                        }
                    };
                    
                case 'models_car':
                    return {
                        action: 'create_car',
                        data: {
                            reg_number: `MODEL${timestamp}`,
                            create_user_id: 563
                        }
                    };
                    
                case 'models_business_card':
                    return {
                        action: 'create_business_card',
                        data: {
                            car_id: 1,
                            user_id: 563
                        }
                    };
                    
                case 'models_photo':
                    return {
                        action: 'create_photo',
                        data: {
                            entity_type: 'car',
                            entity_id: 1
                        }
                    };
                    
                case 'ocr_test':
                    return {
                        action: 'recognize_plate'
                    };
                    
                default:
                    return {};
            }
        }
        
        function updateTestStatus(testId, status) {
            testResults[testId] = { ...testResults[testId], status };
            renderTests();
        }
        
        function updateTestResult(testId, result) {
            testResults[testId] = { ...testResults[testId], ...result };
        }
        
        function updateResults() {
            const resultsDiv = document.getElementById('results');
            const results = Object.keys(testResults).filter(id => testResults[id].status !== 'pending');
            
            if (results.length === 0) {
                resultsDiv.innerHTML = `
                    <div style="text-align: center; color: #7f8c8d; padding: 40px;">
                        Нажмите "Запустить все" для начала тестирования
                    </div>
                `;
                return;
            }
            
            resultsDiv.innerHTML = results.map(id => {
                const result = testResults[id];
                const test = testsConfig.tests.find(t => t.id === id);
                const statusClass = result.status === 'success' ? 'success' : 'error';
                
                return `
                    <div class="test-result ${statusClass}">
                        <div class="test-name">${test.icon} ${test.name}</div>
                        <div class="test-status ${statusClass}">
                            ${result.status === 'success' ? '✅ УСПЕХ' : '❌ ОШИБКА'}
                        </div>
                        <div class="test-details">
                            ${result.error ? `Ошибка: ${result.error}` : 
                              result.data ? JSON.stringify(result.data, null, 2) : 
                              result.raw || 'Нет данных'}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        async function runAllTests() {
            if (isRunning) return;
            
            isRunning = true;
            clearResults();
            
            for (const test of testsConfig.tests) {
                await runTest(test.id);
                await new Promise(resolve => setTimeout(resolve, 500)); // Пауза между тестами
            }
            
            isRunning = false;
            updateStats();
        }
        
        async function runCategoryTests(category) {
            if (isRunning) return;
            
            isRunning = true;
            clearResults();
            
            const categoryTests = testsConfig.tests.filter(test => test.category.startsWith(category));
            
            for (const test of categoryTests) {
                await runTest(test.id);
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            isRunning = false;
            updateStats();
        }
        
        function clearResults() {
            testResults = {};
            updateResults();
            updateStats();
        }
        
        function updateStats() {
            const total = testsConfig.tests.length;
            const completed = Object.keys(testResults).length;
            const successful = Object.values(testResults).filter(r => r.status === 'success').length;
            const failed = Object.values(testResults).filter(r => r.status === 'error').length;
            const pending = total - completed;
            
            // Подсчитываем тесты по категориям
            const testCounts = {};
            testsConfig.tests.forEach(test => {
                const category = test.category.split('_')[0];
                testCounts[category] = (testCounts[category] || 0) + 1;
            });
            
            document.getElementById('stats-content').innerHTML = `
                📊 Статистика: ${completed}/${total} выполнено | ✅ ${successful} успешно | ❌ ${failed} ошибок | ⏳ ${pending} ожидает<br>
                L1 Actions: ${testCounts.l1 || 0} | L2 Actions: ${testCounts.l2 || 0} | L3 Actions: ${testCounts.l3 || 0}<br>
                Модели: ${testCounts.models || 0} | OCR: ${testCounts.ocr || 0}<br>
                <div class="debug-info">
                    Base URL: ${baseUrl} | Тестов: ${total} | Категорий: ${Object.keys(categories).length}
                </div>
            `;
        }
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            renderTests();
            updateStats();
        });
    </script>
</body>
</html> 