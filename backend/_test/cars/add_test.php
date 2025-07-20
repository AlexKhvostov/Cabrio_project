<!DOCTYPE html>
<html>
<head>
    <title>Тест: Добавление автомобиля</title>
    <meta charset="utf-8">
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
            background: #005a8b; 
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
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .required {
            color: #dc3545;
        }
        .optional {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: Добавление автомобиля</h1>
        
        <!-- Конструктор запроса -->
        <div class="section">
            <h2>📝 Конструктор запроса</h2>
            <form id="requestForm">
                <div class="grid">
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
                                <option value="registered">registered</option>
                                <option value="member" selected>member</option>
                                <option value="admin">admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <h3>🚗 Данные автомобиля</h3>
                        <div class="form-group">
                            <label>Номер <span class="required">*</span></label>
                            <input type="text" name="reg_number" value="A123BC" required>
                            <div class="optional">Формат: A123BC (минимум 5 символов)</div>
                        </div>
                        <div class="form-group">
                            <label>Фото <span class="required">*</span></label>
                            <input type="file" name="photo_file" accept="image/*" required>
                            <div class="optional">JPG или PNG, максимум 5MB</div>
                        </div>
                        <div class="form-group">
                            <label>Модель</label>
                            <input type="text" name="model" value="MX-5">
                        </div>
                        <div class="form-group">
                            <label>Год</label>
                            <input type="number" name="year" value="2020" min="1900" max="2024">
                        </div>
                        <div class="form-group">
                            <label>Цвет</label>
                            <input type="text" name="color" value="Красный">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="show_reg_number" value="1" checked>
                                Показывать номер пользователям
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit">🚀 Отправить запрос</button>
            </form>
        </div>
        
        <!-- Предварительный просмотр -->
        <div class="section">
            <h2>👀 Предварительный просмотр запроса</h2>
            <div id="requestPreview" class="json-preview">Заполните форму выше...</div>
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
            const data = {};
            
            // Собираем auth данные
            data.auth = {
                user_id: parseInt(formData.get('user_id')),
                role: formData.get('role')
            };
            
            // Собираем специфичные данные
            data.data = {
                reg_number: formData.get('reg_number'),
                model: formData.get('model'),
                year: formData.get('year') ? parseInt(formData.get('year')) : null,
                color: formData.get('color'),
                show_reg_number: formData.get('show_reg_number') === '1'
            };
            
            // Убираем пустые поля
            Object.keys(data.data).forEach(key => {
                if (data.data[key] === '' || data.data[key] === null) {
                    delete data.data[key];
                }
            });
            
            return data;
        }
        
        // Конвертация файла в base64
        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            });
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
                '<div>⏳ Отправка запроса...</div>';
            
            try {
                const response = await fetch('../../api/cars/add.php', {
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
        
        // Отображение успешного ответа
        function displayResponse(data) {
            const responseDiv = document.getElementById('response');
            
            if (data.success) {
                responseDiv.innerHTML = `
                    <div class="success">
                        <h3>✅ Успешно!</h3>
                        <p><strong>Сообщение:</strong> ${data.result.message}</p>
                        <p><strong>Request ID:</strong> ${data.request_id}</p>
                        <p><strong>Время:</strong> ${data.timestamp}</p>
                        <p><strong>Данные автомобиля:</strong></p>
                        <pre>${JSON.stringify(data.result.data, null, 2)}</pre>
                    </div>
                `;
            } else {
                responseDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка!</h3>
                        <p><strong>Код:</strong> ${data.error.code}</p>
                        <p><strong>Тип:</strong> ${data.error.type}</p>
                        <p><strong>Сообщение:</strong> ${data.error.message}</p>
                        ${data.error.details ? `<p><strong>Детали:</strong></p><pre>${JSON.stringify(data.error.details, null, 2)}</pre>` : ''}
                    </div>
                `;
            }
        }
        
        // Отображение ошибки
        function displayError(error) {
            document.getElementById('response').innerHTML = `
                <div class="error">
                    <h3>❌ Ошибка сети!</h3>
                    <p><strong>Сообщение:</strong> ${error.message}</p>
                </div>
            `;
        }
        
        // Обработчики событий
        document.getElementById('requestForm').addEventListener('input', updatePreview);
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            sendRequest();
        });
        
        // Инициализация
        updatePreview();
    </script>
</body>
</html> 