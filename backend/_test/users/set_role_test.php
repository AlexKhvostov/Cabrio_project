<!DOCTYPE html>
<html>
<head>
    <title>Тест: Смена роли пользователя</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005a8b; }
        .json-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; border: 1px solid #e9ecef; }
        .response { margin-top: 20px; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 4px; border: 1px solid #c3e6cb; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 4px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест: Смена роли пользователя</h1>
        <div class="section">
            <h2>📝 Конструктор запроса</h2>
            <form id="requestForm">
                <div class="form-group">
                    <label>Ваш user_id (инициатор):</label>
                    <input type="number" name="initiator_id" value="1" required>
                </div>
                <div class="form-group">
                    <label>Ваша роль (инициатор):</label>
                    <select name="initiator_role" required>
                        <option value="moderator">moderator</option>
                        <option value="admin">admin</option>
                        <option value="system">system</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>user_id пользователя для смены роли:</label>
                    <input type="number" name="user_id" value="2" required>
                </div>
                <div class="form-group">
                    <label>Новая роль:</label>
                    <select name="new_role_code" required>
                        <option value="external">external</option>
                        <option value="guest">guest</option>
                        <option value="new">new</option>
                        <option value="registered">registered</option>
                        <option value="member" selected>member</option>
                        <option value="moderator">moderator</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Причина смены роли:</label>
                    <input type="text" name="reason" value="Тестовая смена роли" required>
                </div>
                <button type="submit">🚀 Отправить запрос</button>
            </form>
        </div>
        <div class="section">
            <h2>👀 Предварительный просмотр запроса</h2>
            <div id="requestPreview" class="json-preview">Заполните форму выше...</div>
        </div>
        <div class="section">
            <h2>📊 Результат</h2>
            <div id="response"></div>
        </div>
    </div>
    <script>
        function buildRequest() {
            const formData = new FormData(document.getElementById('requestForm'));
            return {
                auth: {
                    user_id: parseInt(formData.get('initiator_id')),
                    role: formData.get('initiator_role')
                },
                data: {
                    user_id: parseInt(formData.get('user_id')),
                    new_role_code: formData.get('new_role_code'),
                    reason: formData.get('reason')
                }
            };
        }
        async function updatePreview() {
            const requestData = buildRequest();
            document.getElementById('requestPreview').textContent = JSON.stringify(requestData, null, 2);
        }
        async function sendRequest() {
            const requestData = buildRequest();
            document.getElementById('requestPreview').textContent = JSON.stringify(requestData, null, 2);
            document.getElementById('response').innerHTML = '<div>⏳ Отправка запроса...</div>';
            try {
                const response = await fetch('/app/backend/api/users/set_role.php', {
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
        function displayResponse(data) {
            const responseDiv = document.getElementById('response');
            if (data.success) {
                responseDiv.innerHTML = `
                    <div class="success">
                        <h3>✅ Успешно!</h3>
                        <p><strong>Сообщение:</strong> ${data.message || (data.result && data.result.message) || ''}</p>
                        <p><strong>Данные:</strong></p>
                        <pre>${JSON.stringify(data.result ? data.result.data : data, null, 2)}</pre>
                    </div>
                `;
            } else {
                responseDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка!</h3>
                        <p><strong>Код:</strong> ${data.error && data.error.code ? data.error.code : ''}</p>
                        <p><strong>Тип:</strong> ${data.error && data.error.type ? data.error.type : ''}</p>
                        <p><strong>Сообщение:</strong> ${data.error && data.error.message ? data.error.message : data.error}</p>
                    </div>
                `;
            }
        }
        function displayError(error) {
            document.getElementById('response').innerHTML = `
                <div class="error">
                    <h3>❌ Ошибка сети!</h3>
                    <p><strong>Сообщение:</strong> ${error.message}</p>
                </div>
            `;
        }
        document.getElementById('requestForm').addEventListener('input', updatePreview);
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            sendRequest();
        });
        updatePreview();
    </script>
</body>
</html> 