<?php
/**
 * Тестовый файл для проверки таблицы sessions в базе данных
 * ТОЛЬКО ДЛЯ РАЗРАБОТКИ!
 */
require_once __DIR__ . '/utils/load_env.php';
require_once __DIR__ . '/utils/ResponseHelper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/utils/Database.php';
    $pdo = Database::getInstance();
    
    $result = [
        'success' => true,
        'data' => []
    ];
    
    // 1. Проверяем существование таблицы sessions
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'sessions'");
    $stmt->execute();
    $sessionsTableExists = $stmt->fetch() !== false;
    
    $result['data']['sessions_table_exists'] = $sessionsTableExists;
    
    if ($sessionsTableExists) {
        // 2. Получаем структуру таблицы sessions
        $stmt = $pdo->prepare("DESCRIBE sessions");
        $stmt->execute();
        $sessionsStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['data']['sessions_structure'] = $sessionsStructure;
        
        // 3. Получаем количество записей в sessions
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sessions");
        $stmt->execute();
        $sessionsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $result['data']['sessions_count'] = $sessionsCount;
        
        // 4. Получаем последние 5 сессий
        $stmt = $pdo->prepare("
            SELECT s.*, u.telegram_id, u.first_name_tg, u.last_name_tg 
            FROM sessions s 
            LEFT JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['data']['recent_sessions'] = $recentSessions;
    }
    
    // 5. Проверяем поля в таблице users для Telegram
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME IN ('telegram_id', 'telegram_photo_url', 'last_telegram_auth')
    ");
    $stmt->execute();
    $userTelegramFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['data']['user_telegram_fields'] = $userTelegramFields;
    
    // 6. Получаем количество пользователей с telegram_id
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE telegram_id IS NOT NULL");
    $stmt->execute();
    $usersWithTelegram = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $result['data']['users_with_telegram'] = $usersWithTelegram;
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'DB_ERROR',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 