<?php
/**
 * База фронта: локально /Cabrio_app/frontend, на тесте и бое /app/frontend.
 * Все ссылки на css/js/страницы считаем отсюда, без жёсткого /app.
 */
function cabrio_frontend_base(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(.*?/frontend)(?:/|$)#', $script, $m)) {
        return $m[1];
    }
    return '/app/frontend';
}

function cabrio_frontend_url(string $fromFrontend): string
{
    return rtrim(cabrio_frontend_base(), '/') . '/' . ltrim($fromFrontend, '/');
}

function cabrio_asset(string $relFromFrontend, string $absoluteFile): string
{
    $v = is_file($absoluteFile) ? filemtime($absoluteFile) : time();
    return htmlspecialchars(cabrio_frontend_url($relFromFrontend) . '?v=' . $v, ENT_QUOTES);
}

/** Ссылка на файл внутри frontend/ с версией по дате изменения */
function cabrio_asset_href(string $relFromFrontend): string
{
    $abs = dirname(__DIR__) . '/' . ltrim(str_replace('\\', '/', $relFromFrontend), '/');
    return cabrio_asset($relFromFrontend, $abs);
}

/** Корень приложения: …/Cabrio_app или /app — родитель папки frontend */
function cabrio_app_base(): string
{
    return (string) preg_replace('#/frontend$#', '', rtrim(cabrio_frontend_base(), '/'));
}
