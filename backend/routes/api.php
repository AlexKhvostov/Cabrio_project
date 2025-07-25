<?php
// Точка входа для всех API-запросов CabrioRide
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/ResponseHelper.php';
require_once __DIR__ . '/../utils/AuthHelper.php';
// ...подключайте другие утилиты и контроллеры по мере необходимости

try {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? null;
    // Логируем для отладки
    file_put_contents(
        __DIR__ . '/../logs/test_router.log',
        date('c') . " | uri: $uri | method: $method | route: " . ($route ?? '-') . "\n",
        FILE_APPEND
    );

    // Простейший роутер (MVP)
    if ($route === '/api/users' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/UserController.php';
        (new UserController())->getList();
    } elseif ($route === '/api/users' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/UserController.php';
        (new UserController())->create();
    }
    // Маршруты для автомобилей
    elseif ($route === '/api/cars' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->getList();
    } elseif ($route === '/api/cars' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->create();
    }
    // Маршруты для событий
    elseif ($route === '/api/events' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/EventController.php';
        (new EventController())->getList();
    } elseif ($route === '/api/events' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/EventController.php';
        (new EventController())->create();
    }
    // Маршруты для гид-объектов
    elseif ($route === '/api/guide-objects' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/GuideObjectController.php';
        (new GuideObjectController())->getList();
    } elseif ($route === '/api/guide-objects' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/GuideObjectController.php';
        (new GuideObjectController())->create();
    }
    // ...добавляйте остальные маршруты по аналогии

    else {
        http_response_code(404);
        echo ResponseHelper::error('NOT_FOUND', 'Маршрут не найден');
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo ResponseHelper::error('INTERNAL_ERROR', $e->getMessage());
} 