<!DOCTYPE html>
<html>
<head>
    <title>Тест: Добавление визитки к машине</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; }
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
    <h1>🧪 Тест: Добавление визитки к машине</h1>
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
                <label>Car ID:</label>
                <input type="number" name="car_id" placeholder="ID машины (если известен)">
            </div>
            <div class="form-group">
                <label>Reg Number:</label>
                <input type="text" name="reg_number" placeholder="Рег. номер (если нет car_id)">
            </div>
            <div class="form-group">
                <label>Photo (base64): <span style="color:#888;font-weight:normal">(обязательно, data:image/jpeg;base64,...)</span></label>
                <textarea name="photo" id="photoField" rows="2" placeholder="base64-строка или data:image...\nПример: data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD..."></textarea>
            </div>
            <div class="form-group">
                <label>Загрузить фото-файл:</label>
                <input type="file" id="photoFileInput" accept="image/*">
            </div>
            <div class="form-group" id="photoPreviewGroup" style="display:none;">
                <label>Предпросмотр фото:</label>
                <img id="photoPreview" src="" alt="preview" style="max-width:200px;max-height:200px;border:1px solid #ccc;">
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" placeholder="Место (необязательно)">
            </div>
            <div class="form-group">
                <label>Notes:</label>
                <input type="text" name="notes" placeholder="Заметки (необязательно)">
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
    const data = {};
    if (formData.get('car_id')) data.car_id = parseInt(formData.get('car_id'));
    if (formData.get('reg_number')) data.reg_number = formData.get('reg_number');
    if (formData.get('photo')) data.photo = formData.get('photo');
    if (formData.get('location')) data.location = formData.get('location');
    if (formData.get('notes')) data.notes = formData.get('notes');
    return { auth, data };
}
function updatePreview() {
    document.getElementById('requestPreview').textContent = JSON.stringify(buildRequest(), null, 2);
    // Предпросмотр фото, если base64
    const photoVal = document.getElementById('photoField').value;
    const previewGroup = document.getElementById('photoPreviewGroup');
    const previewImg = document.getElementById('photoPreview');
    if (photoVal && photoVal.startsWith('data:image/')) {
        previewGroup.style.display = '';
        previewImg.src = photoVal;
    } else {
        previewGroup.style.display = 'none';
        previewImg.src = '';
    }
}
document.getElementById('requestForm').addEventListener('input', updatePreview);
document.getElementById('requestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    updatePreview();
    const requestData = buildRequest();
    document.getElementById('response').innerHTML = '<em>Отправка запроса...</em>';
    try {
        const response = await fetch('/app/backend/api/business-cards/add_to_car.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(requestData)
        });
        const data = await response.json();
        displayResponse(data);
    } catch (err) {
        document.getElementById('response').innerHTML = '<div class="error">Ошибка JS: ' + err + '</div>';
    }
});
// --- Новое: загрузка фото-файла и преобразование в base64 ---
document.getElementById('photoFileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        document.getElementById('photoField').value = evt.target.result;
        updatePreview();
    };
    reader.readAsDataURL(file);
});
function displayResponse(data) {
    const responseDiv = document.getElementById('response');
    if (data.success) {
        let photoUrl = '';
        if (data.result.data && data.result.data.photo) {
            photoUrl = `<p><strong>Фото (url):</strong> <a href="${data.result.data.photo}" target="_blank">${data.result.data.photo}</a></p>`;
        }
        responseDiv.innerHTML = `
            <div class="success">
                <h3>✅ Успешно!</h3>
                <p><strong>Сообщение:</strong> ${data.result.message}</p>
                ${photoUrl}
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
updatePreview();
</script>
</body>
</html> 