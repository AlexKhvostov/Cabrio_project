<!DOCTYPE html>
<html>
<head>
    <title>🧪 Тестовые страницы CabrioRide</title>
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
        .header { 
            background: #007cba; 
            color: white; 
            padding: 30px; 
            border-radius: 8px; 
            margin-bottom: 30px; 
            text-align: center;
        }
        .section { 
            background: white; 
            padding: 25px; 
            margin: 20px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .section h2 { 
            color: #007cba; 
            border-bottom: 2px solid #007cba; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .test-link { 
            display: block; 
            padding: 15px; 
            margin: 10px 0; 
            background: #f8f9fa; 
            border: 1px solid #e9ecef; 
            border-radius: 6px; 
            text-decoration: none; 
            color: #333; 
            transition: all 0.3s ease;
        }
        .test-link:hover { 
            background: #e3f2fd; 
            border-color: #007cba; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .test-link .title { 
            font-weight: bold; 
            font-size: 16px; 
            color: #007cba; 
        }
        .test-link .description { 
            color: #666; 
            margin-top: 5px; 
            font-size: 14px; 
        }
        .test-link .path { 
            color: #999; 
            font-size: 12px; 
            font-family: monospace; 
            margin-top: 5px; 
        }
        .status { 
            display: inline-block; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 11px; 
            font-weight: bold; 
            margin-left: 10px; 
        }
        .status.active { 
            background: #d4edda; 
            color: #155724; 
        }
        .status.new { 
            background: #fff3cd; 
            color: #856404; 
        }
        .status.updated { 
            background: #cce5ff; 
            color: #004085; 
        }
        .info-box {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #bee5eb;
            margin-bottom: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Тестовые страницы CabrioRide</h1>
            <p>Интерактивные тесты для всех API endpoints и функций</p>
        </div>

        <div class="info-box">
            <h3>📋 Информация</h3>
            <p><strong>Назначение:</strong> Тестирование API endpoints и функций проекта</p>
            <p><strong>Формат:</strong> Интерактивные HTML страницы с JavaScript</p>
            <p><strong>Стандарт API:</strong> Единый формат запросов/ответов с auth и data секциями</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">8</div>
                <div class="stat-label">API Endpoints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">3</div>
                <div class="stat-label">Группы тестов</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2</div>
                <div class="stat-label">Отладочные инструменты</div>
            </div>
        </div>

        <!-- 🚗 Автомобили -->
        <div class="section">
            <h2>🚗 Автомобили</h2>
            <p>Тесты для работы с автомобилями участников клуба</p>
            
            <a href="cars/add_test.php" class="test-link">
                <div class="title">➕ Добавление автомобиля <span class="status active">Активен</span></div>
                <div class="description">Добавление нового автомобиля с фото и номером. Поддержка флага show_reg_number.</div>
                <div class="path">backend/_test/cars/add_test.php</div>
            </a>
            
            <a href="cars/check_test.php" class="test-link">
                <div class="title">🔍 Проверка автомобиля <span class="status active">Активен</span></div>
                <div class="description">Проверка существования автомобиля в базе по номеру. Возвращает статус и возможность оставить визитку.</div>
                <div class="path">backend/_test/cars/check_test.php</div>
            </a>
        </div>

        <!-- 📇 Визитки -->
        <div class="section">
            <h2>📇 Визитки</h2>
            <p>Тесты для создания и управления визитками участников</p>
            
            <a href="business-cards/add_test.php" class="test-link">
                <div class="title">➕ Создание визитки <span class="status active">Активен</span></div>
                <div class="description">Создание визитки с фото и данными. Автоматическое создание автомобиля если не существует.</div>
                <div class="path">backend/_test/business-cards/add_test.php</div>
            </a>
        </div>

        <!-- 🔍 OCR (Распознавание номеров) -->
        <div class="section">
            <h2>🔍 OCR (Распознавание номеров)</h2>
            <p>Тесты для распознавания номеров автомобилей по фотографиям</p>
            
            <a href="ocr/check_test.php" class="test-link">
                <div class="title">🔍 Проверка номера в БД <span class="status active">Активен</span></div>
                <div class="description">Проверка существования номера в базе данных клуба. Возвращает статус автомобиля.</div>
                <div class="path">backend/_test/ocr/check_test.php</div>
            </a>
            
            <a href="ocr/recognize_test.php" class="test-link">
                <div class="title">📷 Распознавание номера <span class="status active">Активен</span></div>
                <div class="description">Распознавание номера по фотографии через OCR API. Поддержка drag & drop.</div>
                <div class="path">backend/_test/ocr/recognize_test.php</div>
            </a>
            
            <a href="ocr/debug_image_format.php" class="test-link">
                <div class="title">🔍 Отладка формата изображения <span class="status new">Новый</span></div>
                <div class="description">Анализ формата изображения и проверка требований для OCR. Отладочная информация.</div>
                <div class="path">backend/_test/ocr/debug_image_format.php</div>
            </a>
        </div>

        <!-- 🛠️ Отладочные инструменты -->
        <div class="section">
            <h2>🛠️ Отладочные инструменты</h2>
            <p>Инструменты для диагностики и отладки</p>
            
            <a href="test_business_cards_table.php" class="test-link">
                <div class="title">📊 Проверка таблицы business_cards <span class="status active">Активен</span></div>
                <div class="description">Проверка структуры и содержимого таблицы business_cards. Диагностика проблем с БД.</div>
                <div class="path">backend/_test/test_business_cards_table.php</div>
            </a>
            
            <a href="test_db.php" class="test-link">
                <div class="title">🔧 Тест подключения к БД <span class="status active">Активен</span></div>
                <div class="description">Проверка подключения к базе данных и основных таблиц. Диагностика проблем с БД.</div>
                <div class="path">backend/_test/test_db.php</div>
            </a>
        </div>

        <!-- 📚 Документация -->
        <div class="section">
            <h2>📚 Документация</h2>
            <p>Документация и справочные материалы</p>
            
            <a href="README.md" class="test-link">
                <div class="title">📖 README тестов <span class="status active">Активен</span></div>
                <div class="description">Подробная документация по тестовым страницам и их использованию.</div>
                <div class="path">backend/_test/README.md</div>
            </a>
        </div>

        <!-- 👥 Пользователи -->
        <div class="section">
            <h2>👥 Пользователи</h2>
            <p>Тесты для управления пользователями и профилями</p>
            
            <a href="users/add_test.php" class="test-link">
                <div class="title">➕ Добавление пользователя <span class="status new">Новый</span></div>
                <div class="description">Добавление нового пользователя с проверкой по Telegram ID. Создание или обновление данных.</div>
                <div class="path">backend/_test/users/add_test.php</div>
            </a>
            
            <a href="users/profile_test.php" class="test-link">
                <div class="title">👤 Чтение профиля <span class="status new">Новый</span></div>
                <div class="description">Получение полной информации о пользователе и списка его автомобилей.</div>
                <div class="path">backend/_test/users/profile_test.php</div>
            </a>
            
            <a href="users/update_test.php" class="test-link">
                <div class="title">✏️ Обновление профиля <span class="status new">Новый</span></div>
                <div class="description">Обновление данных пользователя и загрузка фото профиля.</div>
                <div class="path">backend/_test/users/update_test.php</div>
            </a>
            
            <a href="users/profile_example.php" class="test-link">
                <div class="title">👤 Пример профиля <span class="status active">Активен</span></div>
                <div class="description">Пример отображения профиля пользователя с данными</div>
                <div class="path">backend/_test/users/profile_example.php</div>
            </a>
        </div>

        <!-- 🚧 В разработке -->
        <div class="section">
            <h2>🚧 В разработке</h2>
            <p>Секции, которые будут добавлены в будущем</p>
            
            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; color: #666;">
                <p><strong>🔐 Авторизация:</strong> Тесты для регистрации, входа и управления сессиями</p>
                <p><strong>📅 События:</strong> Тесты для создания и управления событиями клуба</p>
                <p><strong>🗺️ Карта:</strong> Тесты для работы с картой и геолокацией</p>
                <p><strong>📚 Гид:</strong> Тесты для работы с гидом и объектами</p>
                <p><strong>🔔 Уведомления:</strong> Тесты для системы уведомлений</p>
                <p><strong>⚙️ Админ панель:</strong> Тесты для административных функций</p>
            </div>
        </div>

        <div class="section">
            <h2>📋 Статистика</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number">6</div>
                    <div class="stat-label">Активных тестов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">2</div>
                    <div class="stat-label">Отладочных инструмента</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Документация</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 