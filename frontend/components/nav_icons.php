<?php
/**
 * Картинки нижнего меню. Путь считаем от адреса страницы,
 * чтобы работало и локально (/Cabrio_app/frontend/...), и на /app.
 */
require_once __DIR__ . '/../partials/urls.php';

function cabrio_nav_pic(string $name): string
{
    $allowed = ['people', 'car', 'map', 'events', 'guide', 'profile'];
    if (!in_array($name, $allowed, true)) {
        return '';
    }
    if ($name === 'profile') {
        $src = htmlspecialchars(cabrio_frontend_url('assets/img/ph-user.png') . '?v=4', ENT_QUOTES);
        return '<img class="nav-pic" src="' . $src . '" alt="" width="32" height="32" decoding="async">';
    }
    $src = htmlspecialchars(cabrio_frontend_url('assets/img/nav/' . $name . '.png') . '?v=5', ENT_QUOTES);
    return '<img class="nav-pic" src="' . $src . '" alt="" width="32" height="32" decoding="async">';
}
