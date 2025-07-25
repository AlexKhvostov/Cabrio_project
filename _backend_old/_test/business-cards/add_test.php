<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тест: Создание визитки</title>
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
        input, select, textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
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
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffeaa7;
            color: #856404;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: Создание визитки</h1>
        
        <div class="warning">
            <h3>⚠️ Важно!</h3>
            <ul>
                <li>Номер должен содержать минимум 6 символов</li>
                <li>Фото обязательно</li>
                <li>Только для пользователей с ролью member+</li>
                <li>Если авто нет в базе - создастся новый</li>
            </ul>
        </div>
        
        <div class="test-cases">
            <h3>📋 Тестовые сценарии:</h3>
            <ul>
                <li><strong>Новый авто:</strong> A123BC - создастся новый автомобиль</li>
                <li><strong>Существующий авто:</strong> 0070MX7 - использует существующий</li>
                <li><strong>Короткий номер:</strong> ABC - ошибка валидации</li>
            </ul>
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
                        <h3>🚗 Данные визитки</h3>
                        <div class="form-group">
                            <label>Номер <span class="required">*</span></label>
                            <input type="text" name="reg_number" value="A123BC" required>
                            <div class="optional">Формат: A123BC (минимум 6 символов)</div>
                        </div>
                        <div class="form-group">
                            <label>Фото <span class="required">*</span></label>
                            <input type="file" name="photo_file" accept="image/*" required>
                            <div class="optional">JPG или PNG, максимум 5MB</div>
                        </div>
                        <div class="form-group">
                            <label>Местоположение</label>
                            <input type="text" name="location" value="ул. Ленина, 1">
                        </div>
                        <div class="form-group">
                            <label>Заметки</label>
                            <textarea name="notes" placeholder="Красивый кабриолет, встретил на парковке...">Красивый кабриолет</textarea>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="sendRequest()">🚀 Создать визитку</button>
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
        // Конвертация файла в base64
        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            });
        }
        
        // Формирование запроса
        function buildRequest() {
            const formData = new FormData(document.getElementById('requestForm'));
            const data = {};
            
            // Собираем auth данные
            data.auth = {
                user_id: parseInt(formData.get('user_id')),
                role: formData.get('role')
            };
            
            // Собираем специфичные данные
            data.data = {
                reg_number: formData.get('reg_number'),
                location: formData.get('location'),
                notes: formData.get('notes')
            };
            
            // Убираем пустые поля
            Object.keys(data.data).forEach(key => {
                if (data.data[key] === '' || data.data[key] === null) {
                    delete data.data[key];
                }
            });
            
            return data;
        }
        
        // Обновление предварительного просмотра
        async function updatePreview() {
            const requestData = buildRequest();
            
            // Добавляем фото если выбрано
            const photoFile = document.querySelector('input[name="photo_file"]').files[0];
            if (photoFile) {
                try {
                    const base64Photo = await fileToBase64(photoFile);
                    requestData.data.photo = base64Photo;
                } catch (error) {
                    console.error('Ошибка конвертации фото:', error);
                }
            }
            
            document.getElementById('requestPreview').textContent = 
                JSON.stringify(requestData, null, 2);
        }
        
        // Отправка запроса
        async function sendRequest() {
            const requestData = buildRequest();
            
            // Добавляем фото если выбрано
            const photoFile = document.querySelector('input[name="photo_file"]').files[0];
            if (photoFile) {
                try {
                    const base64Photo = await fileToBase64(photoFile);
                    requestData.data.photo = base64Photo;
                } catch (error) {
                    displayError({message: 'Ошибка конвертации фото: ' + error.message});
                    return;
                }
            } else {
                displayError({message: 'Фото обязательно'});
                return;
            }
            
            // Показываем запрос
            document.getElementById('requestPreview').textContent = 
                JSON.stringify(requestData, null, 2);
            
            // Показываем загрузку
            document.getElementById('response').innerHTML = 
                '<div class="info">⏳ Создание визитки...</div>';
            
            try {
                const response = await fetch('../../api/business-cards/add.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(requestData)
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
                
                responseDiv.innerHTML = `
                    <div class="success">
                        <h3>✅ Визитка создана!</h3>
                        <p><strong>Сообщение:</strong> ${data.result.message}</p>
                        <p><strong>ID визитки:</strong> ${result.business_card_id}</p>
                        <p><strong>ID автомобиля:</strong> ${result.car_id}</p>
                        <p><strong>Номер:</strong> ${result.reg_number}</p>
                        <p><strong>Авто создано:</strong> ${result.car_created ? 'Да' : 'Нет'}</p>
                        <p><strong>Местоположение:</strong> ${result.location || 'Не указано'}</p>
                        <p><strong>Заметки:</strong> ${result.notes || 'Не указано'}</p>
                        <p><strong>Создатель:</strong> ${result.inviter_user_id}</p>
                        <hr>
                        <h4>📋 Полный ответ сервера:</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
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