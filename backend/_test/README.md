# 🧪 Стандарт тестовых страниц CabrioRide

## 🎯 Общий принцип
Каждая тестовая страница должна быть **интерактивной** и позволять **конструировать запросы** прямо в браузере, отправлять их на endpoint и видеть результат.

## 📋 Структура тестовой страницы

### 1. **Конструктор запроса**
- Поля для ввода данных (user_id, reg_number, model и т.д.)
- Автоматическое формирование JSON запроса
- Предварительный просмотр запроса

### 2. **Отправка запроса**
- Кнопка "Отправить"
- Отображение реального запроса (как отправляется)
- Отображение реального ответа (как получается)

### 3. **Анализ результата**
- Разбор ответа на ключевые моменты
- Подсветка важных полей
- Отображение статуса операции

## 🎨 Дизайн тестовой страницы

```html
<!DOCTYPE html>
<html>
<head>
    <title>Тест: [Название endpoint]</title>
    <style>
        /* Современный дизайн */
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
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
        <h1>🧪 Тест: [Название endpoint]</h1>
        
        <!-- Конструктор запроса -->
        <div class="section">
            <h2>📝 Конструктор запроса</h2>
            <form id="requestForm">
                <!-- Поля для ввода -->
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
                <!-- Специфичные поля endpoint -->
                <div class="form-group">
                    <label>Reg Number:</label>
                    <input type="text" name="reg_number" value="А123БВ77">
                </div>
                
                <button type="submit">🚀 Отправить запрос</button>
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
        // Логика формирования и отправки запроса
    </script>
</body>
</html>
```

## 🔧 JavaScript логика

### 1. **Формирование запроса**
```javascript
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
        year: formData.get('year')
    };
    
    return data;
}
```

### 2. **Отправка запроса**
```javascript
async function sendRequest() {
    const requestData = buildRequest();
    
    // Показываем запрос
    document.getElementById('requestPreview').textContent = 
        JSON.stringify(requestData, null, 2);
    
    try {
        const response = await fetch('', {
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
```

### 3. **Отображение результата**
```javascript
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
```

## 📝 Примеры тестовых страниц

### 1. **Тест добавления авто** (`cars/add_test.php`)
- Поля: user_id, role, reg_number, model, year, color
- Endpoint: `/backend/_test/cars/add.php`

### 2. **Тест профиля пользователя** (`users/profile_test.php`)
- Поля: user_id, role, target_user_id
- Endpoint: `/backend/_test/users/profile.php`

### 3. **Тест регистрации** (`auth/register_test.php`)
- Поля: telegram_id, username, first_name, last_name
- Endpoint: `/backend/_test/auth/register.php`

## 🎯 Ключевые требования

### ✅ **Обязательные элементы:**
1. **Конструктор запроса** - поля для ввода данных
2. **Предварительный просмотр** - JSON запроса
3. **Отправка** - кнопка и логика отправки
4. **Отображение результата** - разбор ответа
5. **Современный дизайн** - удобный интерфейс

### ✅ **Функциональность:**
1. **Автоматическое формирование** JSON запроса
2. **Валидация полей** - проверка обязательных
3. **Обработка ошибок** - показ ошибок сервера
4. **Подсветка результата** - успех/ошибка
5. **Копирование данных** - для отладки

### ❌ **НЕ делаем:**
1. Сложные формы с множеством полей
2. Избыточную валидацию на клиенте
3. Сложную логику обработки
4. Лишние анимации и эффекты

## 🚀 Быстрый старт

1. **Создаём файл** `backend/_test/[section]/[endpoint]_test.php`
2. **Копируем шаблон** из README
3. **Настраиваем поля** под конкретный endpoint
4. **Указываем endpoint** в fetch запросе
5. **Тестируем** функциональность

## 📋 Чек-лист для каждого теста

- [ ] Конструктор запроса работает
- [ ] JSON формируется правильно
- [ ] Запрос отправляется на endpoint
- [ ] Ответ отображается корректно
- [ ] Ошибки обрабатываются
- [ ] Дизайн современный и удобный
- [ ] Код читаемый и понятный 