<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тест: Добавление пользователя</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: Добавление пользователя</h1>
        
        <div class="test-cases">
            <h3>📋 Тестовые данные:</h3>
            <ul>
                <li><strong>Telegram ID:</strong> 287536885 (тестовый пользователь)</li>
                <li><strong>Username:</strong> lex (тестовый username)</li>
                <li><strong>Имя:</strong> Lex (тестовое имя)</li>
                <li><strong>Фамилия:</strong> Smith (тестовая фамилия)</li>
            </ul>
            <p><strong>⚠️ Важно:</strong> При первом вызове создаётся пользователь с ролью guest, при повторном - обновляются данные</p>
        </div>
        
        <!-- Конструктор запроса -->
        <div class="section">
            <h2>📝 Конструктор запроса</h2>
            <form id="requestForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h3>🔐 Авторизация</h3>
                        <div class="form-group">
                            <label>User ID</label>
                            <input type="number" name="user_id" value="" placeholder="Оставьте пустым для создания нового пользователя">
                            <div class="optional">Оставьте пустым для создания нового пользователя</div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role">
                                <option value="">Оставьте пустым для создания нового пользователя</option>
                                <option value="guest">guest</option>
                                <option value="new">new</option>
                                <option value="registered">registered</option>
                                <option value="member">member</option>
                                <option value="moderator">moderator</option>
                                <option value="admin">admin</option>
                            </select>
                            <div class="optional">Оставьте пустым для создания нового пользователя</div>
                        </div>
                    </div>
                    
                    <div>
                        <h3>👤 Данные пользователя</h3>
                        <div class="form-group">
                            <label>Telegram ID <span class="required">*</span></label>
                            <input type="number" name="telegram_id" value="287536885" required>
                            <div class="optional">Уникальный ID пользователя в Telegram</div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" value="lex">
                            <div class="optional">Username в Telegram (без @)</div>
                        </div>
                        <div class="form-group">
                            <label>Имя</label>
                            <input type="text" name="first_name" value="Lex">
                            <div class="optional">Имя из Telegram</div>
                        </div>
                        <div class="form-group">
                            <label>Фамилия</label>
                            <input type="text" name="last_name" value="Smith">
                            <div class="optional">Фамилия из Telegram</div>
                        </div>
                        <div class="form-group">
                            <label>Фото профиля</label>
                            <div class="file-input" onclick="document.getElementById('photoFile').click()">
                                <input type="file" id="photoFile" accept="image/*" style="display: none;" onchange="handlePhotoSelect(event)">
                                <div id="photoInputText">📁 Нажмите для выбора фото или перетащите сюда</div>
                            </div>
                            <div class="optional">Фото профиля (опционально)</div>
                        </div>
                        <div id="photoPreview"></div>
                        <div class="form-group">
                            <label><input type="checkbox" id="is_member" checked> Пользователь состоит в клубном чате (is_member)</label>
                            <div class="optional">Снимите галочку, чтобы протестировать сценарий выхода из чата</div>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="sendRequest()">🚀 Добавить пользователя</button>
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
        let selectedPhoto = null;
        
        // Обработка выбора фото
        function handlePhotoSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedPhoto = e.target.result;
                updatePhotoPreview(file);
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
        
        // Обновление предварительного просмотра фото
        function updatePhotoPreview(file) {
            const preview = document.getElementById('photoPreview');
            const inputText = document.getElementById('photoInputText');
            
            inputText.textContent = `📷 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            
            preview.innerHTML = `
                <img src="${selectedPhoto}" class="image-preview" alt="Preview">
                <p><strong>Файл:</strong> ${file.name}</p>
                <p><strong>Размер:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                <p><strong>Тип:</strong> ${file.type}</p>
            `;
        }
        
        // Drag and drop для фото
        const photoInput = document.querySelector('.file-input');
        
        photoInput.addEventListener('dragover', (e) => {
            e.preventDefault();
            photoInput.classList.add('dragover');
        });
        
        photoInput.addEventListener('dragleave', () => {
            photoInput.classList.remove('dragover');
        });
        
        photoInput.addEventListener('drop', (e) => {
            e.preventDefault();
            photoInput.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                document.getElementById('photoFile').files = e.dataTransfer.files;
                handlePhotoSelect({ target: { files: [file] } });
            }
        });
        
        // Формирование запроса
        function buildRequest() {
            const formData = new FormData(document.getElementById('requestForm'));
            // Формируем auth объект
            const auth = {};
            const user_id = formData.get('user_id');
            const role = formData.get('role');
            if (user_id && user_id.trim() !== '') {
                auth.user_id = parseInt(user_id);
            }
            if (role && role.trim() !== '') {
                auth.role = role;
            }
            // Формируем telegram_requestor_profile
            const tgProfile = {
                telegram_id: parseInt(formData.get('telegram_id')),
                username: formData.get('username') || null,
                first_name: formData.get('first_name') || null,
                last_name: formData.get('last_name') || null
            };
            // Добавляем фото если выбрано
            if (selectedPhoto) {
                tgProfile.telegram_photo_id = '[BASE64_PHOTO_DATA]'; // Для теста, реальный file_id не нужен
            }
            // Убираем пустые поля
            Object.keys(tgProfile).forEach(key => {
                if (tgProfile[key] === '' || tgProfile[key] === null) {
                    delete tgProfile[key];
                }
            });
            // Флаг is_member
            const isMember = document.getElementById('is_member').checked;
            const request = {
                auth: auth,
                data: {
                    telegram_requestor_profile: tgProfile,
                    is_member: isMember
                }
            };
            return request;
        }
        
        // Обновление предварительного просмотра
        function updatePreview() {
            const request = buildRequest();
            
            // Скрываем фото в предварительном просмотре
            const preview = { ...request };
            if (preview.data.telegram_requestor_profile.telegram_photo_id) {
                preview.data.telegram_requestor_profile.telegram_photo_id = '[BASE64_PHOTO_DATA]';
            }
            
            document.getElementById('requestPreview').textContent = 
                JSON.stringify(preview, null, 2);
        }
        
        // Отправка запроса
        async function sendRequest() {
            const request = buildRequest();
            
            // Показываем загрузку
            document.getElementById('response').innerHTML = 
                '<div class="info">⏳ Добавление пользователя...</div>';
            
            try {
                const response = await fetch('../../api/users/add.php', {
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
                
                responseDiv.innerHTML = `
                    <div class="success">
                        <h3>✅ ${data.result.message}</h3>
                        <p><strong>User ID:</strong> ${result.user_id}</p>
                        <p><strong>Telegram ID:</strong> ${result.telegram_id}</p>
                        <p><strong>Роль:</strong> ${result.role}</p>
                        <p><strong>Создан:</strong> ${result.created ? 'Да' : 'Нет (обновлён)'}</p>
                        <p><strong>Дата обновления:</strong> ${result.updated_at || '—'}</p>
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