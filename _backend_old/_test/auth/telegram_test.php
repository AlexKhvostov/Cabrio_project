<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: Авторизация через Telegram (auth/telegram.php)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .json-preview { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
        .response { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Тест: Авторизация через Telegram (auth/telegram.php)</h1>
    <div class="section">
        <h2>📝 Конструктор запроса</h2>
        <form id="requestForm">
            <div class="form-group">
                <label>ID пользователя:</label>
                <input type="number" name="id" value="123456789" required>
            </div>
            <div class="form-group">
                <label>First name:</label>
                <input type="text" name="first_name" value="Ivan" required>
            </div>
            <div class="form-group">
                <label>Last name:</label>
                <input type="text" name="last_name" value="Ivanov">
            </div>
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="ivan_test">
            </div>
            <div class="form-group">
                <label>Language code:</label>
                <input type="text" name="language_code" value="ru">
            </div>
            <div class="form-group">
                <label>Auth date (UNIX):</label>
                <input type="number" name="auth_date" value="1710000000" required>
            </div>
            <div class="form-group">
                <label>initData (строка от Telegram WebApp):</label>
                <textarea name="initData" rows="4" required readonly></textarea>
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
function buildInitData(form) {
    const userObj = {
        id: parseInt(form.id.value),
        first_name: form.first_name.value,
        last_name: form.last_name.value,
        username: form.username.value,
        language_code: form.language_code.value
    };
    const userStr = encodeURIComponent(JSON.stringify(userObj));
    const auth_date = form.auth_date.value;
    // Для теста hash — просто плейсхолдер (реальную подпись не вычисляем)
    const hash = 'testhashplaceholder';
    return `user=${userStr}&auth_date=${auth_date}&hash=${hash}`;
}
function buildRequest() {
    const form = document.getElementById('requestForm');
    // Автоматически формируем initData
    form.initData.value = buildInitData(form);
    return {
        auth: {},
        data: {
            initData: form.initData.value.trim()
        }
    };
}
function updatePreview() {
    const req = buildRequest();
    document.getElementById('requestPreview').textContent = JSON.stringify(req, null, 2);
}
document.getElementById('requestForm').addEventListener('input', updatePreview);
document.getElementById('requestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const req = buildRequest();
    updatePreview();
    document.getElementById('response').innerHTML = '<em>Отправка запроса...</em>';
    try {
        const res = await fetch('/app/backend/api/auth/telegram.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(req)
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('response').innerHTML = `<div class="success"><h3>✅ Успешно!</h3><pre>${JSON.stringify(data, null, 2)}</pre></div>`;
        } else {
            document.getElementById('response').innerHTML = `<div class="error"><h3>❌ Ошибка!</h3><pre>${JSON.stringify(data, null, 2)}</pre></div>`;
        }
    } catch (err) {
        document.getElementById('response').innerHTML = `<div class="error"><h3>❌ Ошибка JS!</h3><pre>${err}</pre></div>`;
    }
});
// Первичная инициализация превью
updatePreview();
</script>
</body>
</html> 