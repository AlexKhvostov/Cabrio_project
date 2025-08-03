<?php
/**
 * 🛣️ API Router — точка входа для всех API-запросов CabrioRide
 * 
 * Назначение: Маршрутизация запросов с интеграцией AuthMiddleware
 * Использование: Все API запросы проходят через этот файл
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/ResponseHelper.php';
require_once __DIR__ . '/../utils/AuthHelper.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/Logger.php';

try {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? null;
    
    // Логируем для отладки
    Logger::info('API Router: Request received', [
        'uri' => $uri,
        'method' => $method,
        'route' => $route ?? 'null'
    ]);

    // Сначала проверяем, существует ли маршрут
    $routeExists = false;
    
    // Проверяем существующие маршруты
    $existingRoutes = [
        '/api/users' => ['GET', 'POST'],
        '/api/users/profile' => ['GET'],
        '/api/users/check-by-telegram' => ['POST'],
        '/api/users/find-by-telegram' => ['POST'],
        '/api/cars' => ['GET', 'POST'],
        '/api/events' => ['GET', 'POST'],
        '/api/guide-objects' => ['GET', 'POST'],
        '/api/business-cards' => ['GET', 'POST'],
        '/api/photos' => ['GET', 'POST'],
        '/api/reviews' => ['GET', 'POST'],
        '/api/health' => ['GET'],
        '/api/status' => ['GET'],
        '/api/system/user-sync' => ['POST'],
        '/api/system/user-role' => ['POST'],
        '/api/system/entity-status' => ['POST'],
        '/api/actions/check-car-in-club' => ['POST'],
        '/api/actions/leave-business-card' => ['POST'],
        '/api/actions/add-car-to-garage' => ['POST']
    ];
    
    // Проверяем точное совпадение
    if (isset($existingRoutes[$route]) && in_array($method, $existingRoutes[$route])) {
        $routeExists = true;
    }
    
    // Проверяем динамические маршруты (например, /api/cars/{id})
    if (!$routeExists && preg_match('/^\/api\/cars\/\d+$/', $route) && $method === 'GET') {
        $routeExists = true;
    }
    
    // Если маршрут не существует, возвращаем 404
    if (!$routeExists) {
        Logger::warning('API Router: Route not found', [
            'route' => $route,
            'method' => $method
        ]);
        
        http_response_code(404);
        echo ResponseHelper::error('NOT_FOUND', 'Маршрут не найден');
        return;
    }
    
    // Проверяем, требуется ли авторизация для существующего маршрута
    $requiresAuth = AuthMiddleware::requiresAuth($route, $method);
    
    if ($requiresAuth) {
        // Обрабатываем авторизацию для защищенных маршрутов
        $authResult = AuthMiddleware::process();
        
        if (!$authResult['success']) {
            Logger::warning('API Router: Authentication failed', [
                'route' => $route,
                'error' => $authResult['error']['message']
            ]);
            
            http_response_code(401);
            echo ResponseHelper::error('AUTH_ERROR', $authResult['error']['message']);
            exit;
        }
        
        Logger::info('API Router: Authentication successful', [
            'route' => $route,
            'user_id' => $authResult['user_id'] ?? 'unknown'
        ]);
    } else {
        // Обрабатываем публичные маршруты
        $authResult = AuthMiddleware::processPublic();
        
        if (!$authResult['success']) {
            Logger::warning('API Router: Public auth failed', [
                'route' => $route,
                'error' => $authResult['error']['message']
            ]);
            
            http_response_code(500);
            echo ResponseHelper::error('PUBLIC_AUTH_ERROR', $authResult['error']['message']);
            exit;
        }
        
        Logger::info('API Router: Public request processed', [
            'route' => $route
        ]);
    }

    // Маршрутизация запросов
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
    } elseif (preg_match('/^\/api\/cars\/(\d+)$/', $route, $matches) && $method === 'GET') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->getById($matches[1]);
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
    // Маршруты для визиток
    elseif ($route === '/api/business-cards' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/BusinessCardController.php';
        (new BusinessCardController())->getList();
    } elseif ($route === '/api/business-cards' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/BusinessCardController.php';
        (new BusinessCardController())->create();
    }
    // Маршруты для фото
    elseif ($route === '/api/photos' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/PhotoController.php';
        (new PhotoController())->getList();
    } elseif ($route === '/api/photos' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/PhotoController.php';
        (new PhotoController())->upload();
    }
    // Маршруты для отзывов
    elseif ($route === '/api/reviews' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/ReviewController.php';
        (new ReviewController())->getList();
    } elseif ($route === '/api/reviews' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/ReviewController.php';
        (new ReviewController())->create();
    }
    // Публичные маршруты для проверки состояния
    elseif ($route === '/api/health' && $method === 'GET') {
        echo ResponseHelper::success(['status' => 'ok', 'message' => 'API is healthy']);
    } elseif ($route === '/api/status' && $method === 'GET') {
        echo ResponseHelper::success(['status' => 'online', 'version' => '1.0.0']);
    }
    // Маршрут для профиля пользователя
    elseif ($route === '/api/users/profile' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/UserController.php';
        (new UserController())->getProfile();
    }
    // Системные маршруты (требуют SYSTEM_TOKEN)
    elseif ($route === '/api/system/user-sync' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/SystemController.php';
        (new SystemController())->userSync();
    } elseif ($route === '/api/system/user-role' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/SystemController.php';
        (new SystemController())->userRole();
    } elseif ($route === '/api/system/entity-status' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/SystemController.php';
        (new SystemController())->entityStatus();
    }
    // Маршруты для L3 Actions (с OCR)
    elseif ($route === '/api/actions/check-car-in-club' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->checkCarInClub();
    } elseif ($route === '/api/actions/leave-business-card' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->leaveBusinessCard();
    } elseif ($route === '/api/actions/add-car-to-garage' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->addCarToGarage();
    }
    // ...добавляйте остальные маршруты по аналогии
} catch (Throwable $e) {
    Logger::error('API Router: Unexpected error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo ResponseHelper::error('INTERNAL_ERROR', $e->getMessage());
} finally {
    // Очищаем глобальный контекст в конце запроса
    if (class_exists('AppContext')) {
        AppContext::clear();
    }
} 