<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тест: Чтение профиля пользователя</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .section { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        button { 
            background: #007cba; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
        }
        button:hover { 
            background: #005a87; 
        }
        .json-preview { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 4px; 
            font-family: monospace; 
            white-space: pre-wrap;
            border: 1px solid #e9ecef;
            max-height: 300px;
            overflow-y: auto;
        }
        .response { 
            margin-top: 20px; 
        }
        .success { 
            color: #28a745; 
            background: #d4edda;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        .error { 
            color: #dc3545; 
            background: #f8d7da;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .required { 
            color: #dc3545; 
        }
        .optional { 
            color: #6c757d; 
            font-size: 12px; 
            margin-top: 5px; 
        }
        h1, h2 { 
            color: #333; 
        }
        .test-cases {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffeaa7;
            margin-bottom: 20px;
        }
        .user-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin: 10px 0;
        }
        .car-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            margin: 10px 0;
        }
        .car-item {
            background: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: Чтение профиля пользователя</h1>
        
        <div class="test-cases">
            <h3>📋 Тестовые данные:</h3>
            <ul>
                <li><strong>User ID:</strong> 1 (тестовый пользователь)</li>
                <li><strong>User ID:</strong> 2 (другой пользователь)</li>
                <li><strong>Пустой User ID:</strong> покажет свой профиль</li>
            </ul>
            <p><strong>⚠️ Важно:</strong> Endpoint возвращает полную информацию о пользователе и список его машин</p>
        </div>
        
        <!-- Конструктор запроса -->
        <div class="section">
            <h2>📝 Конструктор запроса</h2>
            <form id="requestForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h3>🔐 Авторизация</h3>
                        <div class="form-group">
                            <label>User ID <span class="required">*</span></label>
                            <input type="number" name="user_id" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Role <span class="required">*</span></label>
                            <select name="role" required>
                                <option value="guest">guest</option>
                                <option value="new">new</option>
                                <option value="registered">registered</option>
                                <option value="member" selected>member</option>
                                <option value="moderator">moderator</option>
                                <option value="admin">admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <h3>👤 Данные запроса</h3>
                        <div class="form-group">
                            <label>Target User ID</label>
                            <input type="number" name="target_user_id" value="1" placeholder="Оставьте пустым для своего профиля">
                            <div class="optional">ID пользователя для просмотра профиля (пусто = свой профиль)</div>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="sendRequest()">🚀 Получить профиль</button>
            </form>
        </div>
        
        <!-- Предварительный просмотр -->
        <div class="section">
            <h2>👀 Предварительный просмотр запроса</h2>
            <div id="requestPreview" class="json-preview"></div>
        </div>
        
        <!-- Результат -->
        <div class="section">
            <h2>📊 Результат</h2>
            <div id="response"></div>
        </div>
    </div>
    
    <script>
        // Формирование запроса
        function buildRequest() {
            const formData = new FormData(document.getElementById('requestForm'));
            
            const request = {
                auth: {
                    user_id: parseInt(formData.get('user_id')),
                    role: formData.get('role')
                },
                data: {}
            };
            
            // Добавляем target_user_id если указан
            const targetUserId = formData.get('target_user_id');
            if (targetUserId && targetUserId.trim() !== '') {
                request.data.user_id = parseInt(targetUserId);
            }
            
            return request;
        }
        
        // Обновление предварительного просмотра
        function updatePreview() {
            const request = buildRequest();
            document.getElementById('requestPreview').textContent = 
                JSON.stringify(request, null, 2);
        }
        
        // Отправка запроса
        async function sendRequest() {
            const request = buildRequest();
            
            // Показываем загрузку
            document.getElementById('response').innerHTML = 
                '<div class="info">⏳ Получение профиля...</div>';
            
            try {
                const response = await fetch('../../api/users/profile.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(request)
                });
                
                const result = await response.json();
                displayResponse(result);
                
            } catch (error) {
                displayError(error);
            }
        }
        
        // Отображение результата
        function displayResponse(data) {
            const responseDiv = document.getElementById('response');
            
            if (data.success) {
                const result = data.result.data;
                const user = result.user;
                const cars = result.cars;
                
                let userHtml = `
                    <div class="success">
                        <h3>✅ ${data.result.message}</h3>
                        <div class="user-info">
                            <h4>👤 Информация о пользователе:</h4>
                            <p><strong>ID:</strong> ${user.id}</p>
                            <p><strong>Telegram ID:</strong> ${user.telegram_id}</p>
                            <p><strong>Username:</strong> ${user.username || 'Не указан'}</p>
                            <p><strong>Имя:</strong> ${user.first_name || 'Не указано'}</p>
                            <p><strong>Фамилия:</strong> ${user.last_name || 'Не указана'}</p>
                            <p><strong>Роль:</strong> ${user.role}</p>
                            <p><strong>Дата регистрации:</strong> ${user.join_date}</p>
                            ${user.photo ? `<p><strong>Фото:</strong> ${user.photo}</p>` : ''}
                        </div>
                `;
                
                if (cars && cars.length > 0) {
                    userHtml += `
                        <div class="car-list">
                            <h4>🚗 Автомобили пользователя (${cars.length}):</h4>
                    `;
                    
                    cars.forEach(car => {
                        userHtml += `
                            <div class="car-item">
                                <p><strong>ID:</strong> ${car.id}</p>
                                <p><strong>Номер:</strong> ${car.reg_number}</p>
                                <p><strong>Марка:</strong> ${car.brand || 'Не указана'}</p>
                                <p><strong>Модель:</strong> ${car.model || 'Не указана'}</p>
                                <p><strong>Год:</strong> ${car.year || 'Не указан'}</p>
                                <p><strong>Цвет:</strong> ${car.color || 'Не указан'}</p>
                                <p><strong>Статус:</strong> ${car.status || 'Не указан'}</p>
                            </div>
                        `;
                    });
                    
                    userHtml += '</div>';
                } else {
                    userHtml += `
                        <div class="car-list">
                            <h4>🚗 Автомобили пользователя:</h4>
                            <p>У пользователя нет автомобилей</p>
                        </div>
                    `;
                }
                
                userHtml += `
                        <hr>
                        <h4>📋 Полный ответ сервера:</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
                
                responseDiv.innerHTML = userHtml;
            } else {
                responseDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка!</h3>
                        <p><strong>Код:</strong> ${data.error.code}</p>
                        <p><strong>Тип:</strong> ${data.error.type}</p>
                        <p><strong>Сообщение:</strong> ${data.error.message}</p>
                        ${data.error.details ? `<p><strong>Детали:</strong> ${JSON.stringify(data.error.details, null, 2)}</p>` : ''}
                        <hr>
                        <h4>📋 Полный ответ сервера:</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            }
        }
        
        // Отображение ошибки сети
        function displayError(error) {
            document.getElementById('response').innerHTML = `
                <div class="error">
                    <h3>❌ Ошибка сети!</h3>
                    <p><strong>Сообщение:</strong> ${error.message}</p>
                    <p>Проверьте:</p>
                    <ul>
                        <li>Запущен ли сервер</li>
                        <li>Правильный ли путь к endpoint</li>
                        <li>Нет ли ошибок в консоли браузера</li>
                    </ul>
                </div>
            `;
        }
        
        // Обновляем предварительный просмотр при изменении формы
        document.getElementById('requestForm').addEventListener('input', updatePreview);
        
        // Инициализация
        updatePreview();
    </script>
</body>
</html> 