<?php
/**
 * UrlHelper — формирование абсолютных URL для файлов из папки uploads
 */

require_once __DIR__ . '/load_env.php';

class UrlHelper {
    public static function getUploadsBaseUrl(): string {
        $base = getenv('UPLOADS_BASE_URL');
        if (!$base || trim($base) === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $scheme . '://' . $host . '/app/uploads';
        }
        return rtrim($base, '/');
    }

    /**
     * Склеить базовый URL и значение из БД (photos.url).
     * Поддерживает варианты: "/uploads/...", "uploads/...", "user/...", "car/...".
     * Если уже абсолютный URL — возвращает как есть.
     */
    public static function buildUploadsUrl(?string $dbUrl): ?string {
        if ($dbUrl === null || $dbUrl === '') return null;
        $trimmed = trim($dbUrl);
        if (preg_match('/^https?:\/\//i', $trimmed)) return $trimmed;
        $path = ltrim($trimmed, '/');
        if (stripos($path, 'uploads/') === 0) {
            $path = substr($path, strlen('uploads/'));
        }
        return self::getUploadsBaseUrl() . '/' . $path;
    }
}


