<?php
require_once __DIR__ . '/../../backend/utils/load_env.php';

function render_meta(string $title = 'CabrioRide') {
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">';
  echo '<meta name="format-detection" content="telephone=no">';
  echo '<meta name="theme-color" content="#1a1a1a">';
  echo '<title>' . htmlspecialchars($title) . '</title>';
  // Прокидываем базовый URL фронта как window.__BASE_URL
  $defaultApp = ((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/app');
  $appBase = getenv('BASE_URL') ?: $defaultApp;
  $appBase = rtrim($appBase, '/');
  echo '<script>window.__BASE_URL = ' . json_encode($appBase, JSON_UNESCAPED_SLASHES) . ';</script>';
  // Прокидываем базовый URL API в JS как window.__API_URL
  // По умолчанию указываем корень backend
  $defaultBase = ((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/app/backend');
  $apiBase = getenv('BACKEND_API_URL') ?: $defaultBase;
  $apiBase = rtrim($apiBase, '/');
  echo '<script>window.__API_URL = ' . json_encode($apiBase, JSON_UNESCAPED_SLASHES) . ';</script>';
	// Интервалы обновления карты и отправки координат из .env
	$updSec = getenv('MAP_UPD_SEC') ?: '30';
	$usersRefreshSec = getenv('MAP_USERS_REFRESH_IDLE_SEC') ?: '60';
	echo '<script>window.MAP_UPD_SEC = ' . json_encode((int)$updSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_USERS_REFRESH_IDLE_SEC = ' . json_encode((int)$usersRefreshSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	// Порог движения (в метрах) и минимальные интервалы при движении
	$moveThresholdM = getenv('MAP_MOVE_THRESHOLD_M') ?: '25';
	$updMovingSec = getenv('MAP_UPD_MOVING_SEC') ?: '10';
	$usersRefreshMovingSec = getenv('MAP_USERS_REFRESH_MOVING_SEC') ?: '10';
	echo '<script>window.MAP_MOVE_THRESHOLD_M = ' . json_encode((int)$moveThresholdM, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_UPD_MOVING_SEC = ' . json_encode((int)$updMovingSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_USERS_REFRESH_MOVING_SEC = ' . json_encode((int)$usersRefreshMovingSec, JSON_UNESCAPED_SLASHES) . ';</script>';
}

