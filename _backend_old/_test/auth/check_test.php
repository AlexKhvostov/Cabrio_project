<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: Проверка авторизации (auth/check.php)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); }
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
    <h1>🧪 Тест: Проверка авторизации (auth/check.php)</h1>
    <div class="section">
        <h2>📝 Конструктор запроса</h2>
        <form id="requestForm">
            <div class="form-group">
                <label>User ID:</label>
                <input type="number" name="user_id" value="1" required>
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
                <label>Token:</label>
                <input type="text" name="token" value="123456789asd" required>
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
    const form = document.getElementById('requestForm');
    return {
        auth: {
            user_id: parseInt(form.user_id.value),
            role: form.role.value,
            token: form.token.value
        },
        data: {}
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
        const res = await fetch('/app/backend/api/auth/check.php', {
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