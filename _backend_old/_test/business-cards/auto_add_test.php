<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тест: Автоматическое добавление визитки (auto_add)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .json-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
        .response { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .img-preview { max-width: 200px; margin-top: 8px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Тест: Автоматическое добавление визитки (auto_add)</h1>
    <div class="section">
        <h2>📝 Конструктор запроса</h2>
        <form id="requestForm">
            <div class="form-group">
                <label>Telegram ID:</label>
                <input type="number" name="telegram_id" value="123456789" required>
            </div>
            <div class="form-group">
                <label>Фото (base64):</label>
                <input type="file" id="photoInput" accept="image/*" required>
                <input type="hidden" name="photo" id="photoBase64">
                <img id="imgPreview" class="img-preview" style="display:none;"/>
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
    const data = {};
    data.auth = {
        user_id: 1,
        role: 'admin',
        telegram_id: parseInt(formData.get('telegram_id'))
    };
    data.data = {
        photo: formData.get('photo')
    };
    return data;
}
function updatePreview() {
    const req = buildRequest();
    document.getElementById('requestPreview').textContent = JSON.stringify(req, null, 2);
}
document.getElementById('requestForm').addEventListener('input', updatePreview);
document.getElementById('requestForm').addEventListener('change', updatePreview);
// Обработка фото
const photoInput = document.getElementById('photoInput');
const photoBase64 = document.getElementById('photoBase64');
const imgPreview = document.getElementById('imgPreview');
photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        photoBase64.value = evt.target.result;
        imgPreview.src = evt.target.result;
        imgPreview.style.display = 'block';
        updatePreview();
    };
    reader.readAsDataURL(file);
});
// Отправка запроса
async function sendRequest(e) {
    e.preventDefault();
    const requestData = buildRequest();
    document.getElementById('requestPreview').textContent = JSON.stringify(requestData, null, 2);
    try {
        const response = await fetch('/app/backend/api/business-cards/auto_add.php', {
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
document.getElementById('requestForm').addEventListener('submit', sendRequest);
function displayResponse(data) {
    const responseDiv = document.getElementById('response');
    if (data.success) {
        responseDiv.innerHTML = `
            <div class="success">
                <h3>✅ Успешно!</h3>
                <p><strong>Сообщение:</strong> ${data.message || ''}</p>
                <p><strong>Данные:</strong></p>
                <pre>${JSON.stringify(data.result, null, 2)}</pre>
            </div>
        `;
    } else {
        responseDiv.innerHTML = `
            <div class="error">
                <h3>❌ Ошибка!</h3>
                <p><strong>Код:</strong> ${data.error?.code || ''}</p>
                <p><strong>Тип:</strong> ${data.error?.type || ''}</p>
                <p><strong>Сообщение:</strong> ${data.error?.message || ''}</p>
            </div>
        `;
    }
}
function displayError(error) {
    document.getElementById('response').innerHTML = `<div class="error"><h3>❌ Ошибка!</h3><pre>${error}</pre></div>`;
}
// Первичная инициализация превью
updatePreview();
</script>
</body>
</html> 