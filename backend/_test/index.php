<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тесты CabrioRide</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #007cba; margin-bottom: 8px; font-size: 1.5em; }
        h2 { color: #007cba; margin: 18px 0 8px 0; font-size: 1.1em; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
        ul { padding-left: 18px; margin: 0 0 6px 0; font-size: 14px; }
        li { margin-bottom: 4px; line-height: 1.3; }
        a { color: #007cba; text-decoration: none; font-weight: 500; }
        a:hover { text-decoration: underline; }
        .desc { color: #888; font-size: 12px; margin-left: 4px; }
        .dev { color: #aaa; font-size: 12px; margin-top: 18px; }
        .section { margin-bottom: 0; padding: 0; background: none; box-shadow: none; border-radius: 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Тесты API CabrioRide</h1>
    <div class="grid">
        <div class="section">
            <h2>👥 Пользователи</h2>
            <ul>
                <li><a href="users/add_test.php">Добавление пользователя</a> <span class="desc">Создание/обновление</span></li>
                <li><a href="auth/telegram_test.php">Авторизация через Telegram</a> <span class="desc">Telegram WebApp</span></li>
                <li><a href="users/profile_test.php">Чтение профиля</a> <span class="desc">Инфо и авто</span></li>
                <li><a href="users/update_test.php">Обновление профиля</a> <span class="desc">Данные и фото</span></li>
                <li><a href="users/set_role_test.php">Смена роли пользователя</a> <span class="desc">Модерация/автоматизация</span></li>
                <li><a href="auth/check_test.php">Проверка авторизации</a> <span class="desc">Проверка токена/роли</span></li>
                <li><a href="auth/logout_test.php">Выход (logout)</a> <span class="desc">Выход, деактивация сессии</span></li>
                <li><a href="users/profile_example.php">Пример профиля</a></li>
            </ul>
            <h2>🚗 Автомобили</h2>
            <ul>
                <li><a href="cars/add_test.php">Добавление авто</a> <span class="desc">Регистрация с фото</span></li>
                <li><a href="cars/check_test.php">Проверка авто</a> <span class="desc">Поиск по номеру</span></li>
            </ul>
            <h2>📇 Визитки</h2>
            <ul>
                <li><a href="business-cards/add_test.php">Создание визитки</a> <span class="desc">Визитка + авто</span></li>
                <li><a href="business-cards/auto_add_test.php">Автоматическое добавление визитки</a> <span class="desc">Оркестратор: авто/визитка по номеру</span></li>
            </ul>
        </div>
        <div class="section">
            <h2>🔍 OCR</h2>
            <ul>
                <li><a href="ocr/recognize_test.php">Распознавание номера</a></li>
                <li><a href="ocr/check_test.php">Проверка номера в БД</a></li>
                <li><a href="ocr/debug_image_format.php">Отладка изображения</a></li>
            </ul>
            <h2>🛠️ Отладка</h2>
            <ul>
                <li><a href="test_db.php">Тест БД</a></li>
                <li><a href="test_business_cards_table.php">Таблица визиток</a></li>
                <li><a href="../_upd_token/index.html">Просмотр/обновление Telegram-хеша</a> <span class="desc">Telegram hash</span></li>
            </ul>
            <h2>🤖 Автотесты</h2>
            <ul>
                <li><a href="auto_test/auto_test.php">Автотест всех эндпоинтов</a> <span class="desc">Пакетная проверка API</span></li>
            </ul>
            <h2>📚 Документация</h2>
            <ul>
                <li><a href="README.md">README тестов</a></li>
            </ul>
        </div>
    </div>
    <div class="dev">
        🚧 В разработке: авторизация, события, карта, гид, уведомления, админка
    </div>
</div>
</body>
</html> 