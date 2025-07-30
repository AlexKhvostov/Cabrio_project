<!DOCTYPE html>
<html>
<head>
    <title>Тест: Добавление фото /api/photos/add</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .json-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
        .response { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Тест: Добавление фото <span style="font-size:0.7em; color:#888">/api/photos/add</span></h1>
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
                <label>entity_type:</label>
                <select name="entity_type">
                    <option value="car">car (авто)</option>
                    <option value="user">user (пользователь)</option>
                    <option value="event">event (событие)</option>
                    <option value="review">review (отзыв)</option>
                    <option value="business_card">business_card (визитка)</option>
                </select>
            </div>
            <div class="form-group">
                <label>entity_id:</label>
                <input type="number" name="entity_id" placeholder="ID сущности">
            </div>
            <div class="form-group">
                <label>Описание (description):</label>
                <input type="text" name="description" placeholder="Описание фото (опционально)">
            </div>
            <div class="form-group">
                <label>Фото (base64 или url):</label>
                <textarea name="photo" rows="4" placeholder="data:image/jpeg;base64,... или https://..."></textarea>
            </div>
            <div class="form-group">
                <label>Или выберите файл (будет сконвертирован в base64):</label>
                <input type="file" id="fileInput">
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
        entity_type: formData.get('entity_type'),
        entity_id: parseInt(formData.get('entity_id')),
        description: formData.get('description'),
        photo: formData.get('photo')
    };
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

document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        document.querySelector('textarea[name="photo"]').value = evt.target.result;
    };
    reader.readAsDataURL(file);
});

document.getElementById('requestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const requestData = buildRequest();
    document.getElementById('requestPreview').textContent = JSON.stringify(requestData, null, 2);
    try {
        const response = await fetch('/app/backend/api/photos/add.php', {
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