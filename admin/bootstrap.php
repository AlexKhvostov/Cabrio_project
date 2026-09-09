<?php
/**
 * Общая загрузка админки рассылки.
 * Вход не через Telegram, а логин/пароль из .env (ADMIN_PANEL_USER / ADMIN_PANEL_PASSWORD).
 * Это не роли участников клуба.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/backend/utils/load_env.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php'));
    $cookiePath = rtrim(dirname($script), '/') ?: '/';
    session_name('cabrio_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function admin_env(string $key, string $default = ''): string
{
    return (string)($_ENV[$key] ?? $default);
}

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_ok']);
}

function admin_require_login(): void
{
    if (!admin_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function admin_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function admin_csrf_ok(): bool
{
    $got = (string)($_POST['csrf'] ?? '');
    return $got !== '' && hash_equals(admin_csrf(), $got);
}

function admin_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Показать кусок токена, не весь. Полный токен в форму не кладём. */
function admin_mask_token(string $token): string
{
    $token = trim($token);
    if ($token === '') {
        return 'не задан BOT_TOKEN';
    }
    $len = strlen($token);
    if ($len < 12) {
        return str_repeat('•', $len);
    }
    return substr($token, 0, 6) . '…' . substr($token, -4);
}

function admin_first_id(string $csv): string
{
    foreach (explode(',', $csv) as $part) {
        $id = trim($part);
        if ($id !== '' && preg_match('/^-?\d+$/', $id)) {
            return $id;
        }
    }
    return '';
}

/** Готовые тексты из папки admin/posts — html или txt. */
function admin_post_presets(): array
{
    $dir = __DIR__ . '/posts';
    if (!is_dir($dir)) {
        return [];
    }
    $out = [];
    foreach (array_merge(glob($dir . '/*.html') ?: [], glob($dir . '/*.txt') ?: []) as $path) {
        $name = basename($path);
        $raw = (string)file_get_contents($path);
        $first = trim((string)preg_split("/\r\n|\n|\r/", strip_tags($raw), 2)[0]);
        $title = $first !== '' ? mb_substr($first, 0, 60) : $name;
        $out[] = ['file' => $name, 'title' => $title, 'body' => $raw];
    }
    return $out;
}

/** Меню админки: рассылка и журнал. */
function admin_nav(): void
{
    if (!admin_logged_in()) {
        return;
    }
    $here = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $items = [
        'index.php' => 'Пост в группу',
        'logs.php' => 'Журнал',
    ];
    echo '<nav class="admin-nav">';
    foreach ($items as $href => $label) {
        $on = $here === $href ? ' is-on' : '';
        echo '<a class="admin-nav-a' . $on . '" href="' . admin_h($href) . '">' . admin_h($label) . '</a>';
    }
    echo '</nav>';
}

function admin_shared_css(): void
{
    echo <<<'CSS'
    body{margin:0;font:14px/1.45 system-ui,Segoe UI,Roboto,sans-serif;background:#070b12;color:#f5f7fa}
    .wrap{max-width:1100px;margin:0 auto;padding:24px 16px 48px}
    h1{font-size:20px;margin:0 0 8px}
    .muted{color:#95a5b8;font-size:13px}
    .card{background:rgba(145,168,196,.08);border:1px solid rgba(144,183,207,.17);border-radius:14px;padding:16px;margin:14px 0}
    label{display:block;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#46e0d6;margin:0 0 6px}
    input,textarea,select{width:100%;box-sizing:border-box;border-radius:10px;border:1px solid rgba(144,183,207,.25);background:rgba(145,168,196,.08);color:#fff;padding:10px 12px;font:inherit}
    textarea{min-height:280px;resize:vertical;font-family:ui-monospace,Consolas,monospace;font-size:13px}
    .row{margin:0 0 12px}
    .btn{border:0;border-radius:10px;padding:10px 16px;font-weight:700;cursor:pointer}
    .btn-go{background:#46e0d6;color:#031416}
    .btn-ghost{background:transparent;color:#d7e0ea;border:1px solid rgba(144,183,207,.3)}
    .ok{color:#3dcc7a}.bad{color:#ff6b3d}
    .limits{font-size:12px;color:#95a5b8;margin:8px 0 0;white-space:pre-wrap}
    .meta b{color:#fff}
    .copy{font-size:13px;word-break:break-all;background:rgba(0,0,0,.25);padding:8px 10px;border-radius:8px}
    .actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:12px}
    .count{font-size:12px;color:#95a5b8}
    .count.is-over{color:#ff6b3d}
    .admin-nav{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 4px}
    .admin-nav-a{color:#d7e0ea;text-decoration:none;border:1px solid rgba(144,183,207,.3);border-radius:999px;padding:6px 12px;font-size:13px}
    .admin-nav-a.is-on{background:#46e0d6;color:#031416;border-color:transparent;font-weight:700}
    .log-table{width:100%;border-collapse:collapse;font-size:13px}
    .log-table th,.log-table td{text-align:left;padding:8px 6px;border-bottom:1px solid rgba(144,183,207,.15);vertical-align:top}
    .log-table th{color:#95a5b8;font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600}
    .filters{display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end}
    @media (max-width:700px){.filters{grid-template-columns:1fr}}
    .pager{display:flex;gap:8px;margin-top:12px}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media (max-width:700px){.grid-2{grid-template-columns:1fr}}
    input[readonly]{opacity:.92;color:#d7e0ea}
    .warn{color:#ffe08a}
    .check-line{display:flex;align-items:flex-start;gap:8px;margin:10px 0;font-size:13px;color:#d7e0ea;text-transform:none;letter-spacing:0}
    .check-line input{width:auto;margin-top:3px;flex:0 0 auto}
    .preset-row{display:flex;gap:8px;flex-wrap:wrap;align-items:end}
    .preset-row select{flex:1;min-width:180px}
CSS;
}
