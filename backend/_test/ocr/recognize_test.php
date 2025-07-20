<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тест: OCR Recognize</title>
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
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        .file-input {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
        }
        .file-input:hover {
            border-color: #007cba;
        }
        .file-input.dragover {
            border-color: #007cba;
            background: #f0f8ff;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: OCR Recognize</h1>
        
        <div class="test-cases">
            <h3>📋 Требования к изображениям:</h3>
            <ul>
                <li><strong>Размер файла:</strong> максимум 3MB</li>
                <li><strong>Рекомендуемое разрешение:</strong> 1024×768</li>
                <li><strong>Рекомендуемая ориентация:</strong> портретная</li>
                <li><strong>Автомобиль:</strong> минимум 15% площади изображения</li>
                <li><strong>Номер:</strong> должен быть читаемым человеком</li>
            </ul>
            <h3>📋 Тестовые изображения:</h3>
            <ul>
                <li><strong>test_http.jpg</strong> - тестовое изображение с номером</li>
                <li><strong>test_quality.jpg</strong> - тестовое изображение для проверки качества</li>
                <li>Любое изображение с номером автомобиля</li>
            </ul>
            <p><strong>⚠️ Важно:</strong> Изображение должно быть в формате JPEG или PNG, размером до 3MB</p>
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
                        <h3>📷 Изображение</h3>
                        <div class="form-group">
                            <label>Выберите изображение <span class="required">*</span></label>
                            <div class="file-input" onclick="document.getElementById('imageFile').click()">
                                <input type="file" id="imageFile" accept="image/*" style="display: none;" onchange="handleImageSelect(event)">
                                <div id="fileInputText">📁 Нажмите для выбора файла или перетащите сюда</div>
                            </div>
                            <div class="optional">Поддерживаемые форматы: JPEG, PNG. Максимум 3MB</div>
                        </div>
                        <div id="imagePreview"></div>
                    </div>
                </div>
                
                <button type="button" onclick="sendRequest()">🚀 Распознать номер</button>
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
        let selectedImage = null;
        
        // Обработка выбора файла
        function handleImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            console.log('=== ЗАГРУЗКА ФАЙЛА ===');
            console.log('Файл:', file.name);
            console.log('Тип:', file.type);
            console.log('Размер:', file.size, 'байт');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedImage = e.target.result;
                
                console.log('=== BASE64 ДАННЫЕ ===');
                console.log('Длина:', selectedImage.length);
                console.log('Формат:', selectedImage.match(/^data:([^;]+);base64,/)?.[1]);
                console.log('Начало:', selectedImage.substring(0, 50));
                console.log('Конец:', selectedImage.substring(selectedImage.length - 20));
                
                updateImagePreview(file);
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
        
        // Обновление предварительного просмотра изображения
        function updateImagePreview(file) {
            const preview = document.getElementById('imagePreview');
            const inputText = document.getElementById('fileInputText');
            
            inputText.textContent = `📷 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            
            preview.innerHTML = `
                <img src="${selectedImage}" class="image-preview" alt="Preview">
                <p><strong>Файл:</strong> ${file.name}</p>
                <p><strong>Размер:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                <p><strong>Тип:</strong> ${file.type}</p>
                <div class="debug-info">
                    <strong>Отладка:</strong><br>
                    Base64 длина: ${selectedImage.length}<br>
                    Формат: ${selectedImage.match(/^data:([^;]+);base64,/)?.[1] || 'неизвестный'}
                </div>
            `;
        }
        
        // Drag and drop
        const fileInput = document.querySelector('.file-input');
        
        fileInput.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileInput.classList.add('dragover');
        });
        
        fileInput.addEventListener('dragleave', () => {
            fileInput.classList.remove('dragover');
        });
        
        fileInput.addEventListener('drop', (e) => {
            e.preventDefault();
            fileInput.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                document.getElementById('imageFile').files = e.dataTransfer.files;
                handleImageSelect({ target: { files: [file] } });
            }
        });
        
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
            
            // Добавляем изображение если выбрано
            if (selectedImage) {
                request.data.image = selectedImage;
                console.log('=== ЗАПРОС ===');
                console.log('Изображение добавлено в запрос');
                console.log('Длина изображения в запросе:', selectedImage.length);
            } else {
                console.log('=== ОШИБКА ===');
                console.log('selectedImage не выбран');
            }
            
            return request;
        }
        
        // Обновление предварительного просмотра
        function updatePreview() {
            const request = buildRequest();
            
            // Скрываем изображение в предварительном просмотре
            const preview = { ...request };
            if (preview.data.image) {
                preview.data.image = '[BASE64_IMAGE_DATA]';
            }
            
            document.getElementById('requestPreview').textContent = 
                JSON.stringify(preview, null, 2);
        }
        
        // Отправка запроса
        async function sendRequest() {
            if (!selectedImage) {
                alert('Пожалуйста, выберите изображение');
                return;
            }
            
            const request = buildRequest();
            
            // Показываем загрузку
            document.getElementById('response').innerHTML = 
                '<div class="info">⏳ Распознавание номера...</div>';
            
            console.log('=== ОТПРАВКА ===');
            console.log('Размер запроса:', JSON.stringify(request).length, 'символов');
            console.log('Изображение в запросе:', !!request.data.image);
            
            try {
                const response = await fetch('../../api/ocr/recognize.php', {
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
                
                if (result.plate) {
                    responseDiv.innerHTML = `
                        <div class="success">
                            <h3>✅ Номер распознан!</h3>
                            <p><strong>Сообщение:</strong> ${data.result.message}</p>
                            <p><strong>Номер:</strong> ${result.plate}</p>
                            <p><strong>Уверенность:</strong> ${(result.confidence * 100).toFixed(1)}%</p>
                            <p><strong>Регион:</strong> ${result.region}</p>
                            <hr>
                            <h4>📋 Полный ответ сервера:</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    responseDiv.innerHTML = `
                        <div class="info">
                            <h3>🔍 Номер не распознан</h3>
                            <p><strong>Сообщение:</strong> ${data.result.message}</p>
                            <p><strong>Уверенность:</strong> ${(result.confidence * 100).toFixed(1)}%</p>
                            <p><strong>Регион:</strong> ${result.region}</p>
                            <hr>
                            <h4>📋 Полный ответ сервера:</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
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
                        <li>Настроен ли OCR_TOKEN в .env</li>
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