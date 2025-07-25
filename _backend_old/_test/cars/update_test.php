<!DOCTYPE html>
<html>
<head>
    <title>Тест: Обновление авто /api/cars/update</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .json-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
        .response { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Тест: Обновление авто <span style="font-size:0.7em; color:#888">/api/cars/update</span></h1>
    <div class="section">
        <h2>📝 Конструктор запроса</h2>
        <form id="requestForm">
            <div class="form-group">
                <label>User ID:</label>
                <input type="number" name="user_id" value="1">
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role">
                    <option value="guest">guest</option>
                    <option value="member" selected>member</option>
                    <option value="admin">admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>car_id:</label>
                <input type="number" name="car_id" placeholder="ID авто" required>
            </div>
            <div class="form-group">
                <label>owner_user_id:</label>
                <input type="number" name="owner_user_id" placeholder="ID нового владельца (только для модератора)">
            </div>
            <div class="form-group">
                <label>status:</label>
                <input type="text" name="status" placeholder="Статус (например, active)">
            </div>
            <div class="form-group">
                <label>model:</label>
                <input type="text" name="model" placeholder="Модель">
            </div>
            <div class="form-group">
                <label>color:</label>
                <input type="text" name="color" placeholder="Цвет">
            </div>
            <div class="form-group">
                <label>year:</label>
                <input type="number" name="year" placeholder="Год выпуска">
            </div>
            <button type="submit">🚀 Отправить запрос</button>
        </form>
    </div>
    <div class="section">
        <h2>👀 Предварительный просмотр запроса</h2>
        <div id="requestPreview" class="json-preview"></div>
    </div>
    <div class="section">
        <h2>📊 Результат</h2>
        <div id="response"></div>
    </div>
</div>
<script>
function buildRequest() {
    const formData = new FormData(document.getElementById('requestForm'));
    const auth = {
        user_id: parseInt(formData.get('user_id')),
        role: formData.get('role')
    };
    const data = {
        car_id: parseInt(formData.get('car_id'))
    };
    if (formData.get('owner_user_id')) data.owner_user_id = parseInt(formData.get('owner_user_id'));
    if (formData.get('status')) data.status = formData.get('status');
    if (formData.get('model')) data.model = formData.get('model');
    if (formData.get('color')) data.color = formData.get('color');
    if (formData.get('year')) data.year = parseInt(formData.get('year'));
    return { auth, data };
}

function displayResponse(data) {
    const responseDiv = document.getElementById('response');
    if (data.success) {
        responseDiv.innerHTML = `
            <div class="success">
                <h3>✅ Успешно!</h3>
                <p><strong>Сообщение:</strong> ${data.result.message}</p>
                <p><strong>Данные:</strong></p>
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
            </div>
        `;
    }
}

document.getElementById('requestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const requestData = buildRequest();
    document.getElementById('requestPreview').textContent = JSON.stringify(requestData, null, 2);
    try {
        const response = await fetch('/app/backend/api/cars/update.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(requestData)
        });
        const result = await response.json();
        displayResponse(result);
    } catch (error) {
        document.getElementById('response').innerHTML = `<div class="error">Ошибка: ${error}</div>`;
    }
});
</script>
</body>
</html> 