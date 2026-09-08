<?php
require_once __DIR__ . '/../../backend/utils/load_env.php';
require_once __DIR__ . '/urls.php';

function render_meta(string $title = 'CabrioRide') {
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">';
  echo '<meta name="format-detection" content="telephone=no">';
  echo '<meta name="theme-color" content="#070b12">';
  echo '<title>' . htmlspecialchars($title) . '</title>';
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((string)($_SERVER['SERVER_PORT'] ?? '') === '443');
  $scheme = $https ? 'https' : (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http');
  if ($scheme !== 'http' && $scheme !== 'https') {
    $scheme = 'http';
  }
  $host = $_SERVER['HTTP_HOST'] ?? '';
  $origin = $scheme . '://' . $host;
  // Пути считаем от реальной папки frontend, чтобы локально не требовался /app
  $frontPath = cabrio_frontend_base();
  $appPath = cabrio_app_base();
  echo '<script>window.__FRONT_URL = ' . json_encode($origin . $frontPath, JSON_UNESCAPED_SLASHES) . ';</script>';
  $defaultApp = $origin . $appPath;
  $appBase = getenv('BASE_URL') ?: $defaultApp;
  $appBase = rtrim($appBase, '/');
  echo '<script>window.__BASE_URL = ' . json_encode($appBase, JSON_UNESCAPED_SLASHES) . ';</script>';
  $defaultBase = $origin . $appPath . '/backend';
  $apiBase = getenv('BACKEND_API_URL') ?: $defaultBase;
  $apiBase = rtrim($apiBase, '/');
  echo '<script>window.__API_URL = ' . json_encode($apiBase, JSON_UNESCAPED_SLASHES) . ';</script>';
	// Интервалы обновления карты и отправки координат из .env
	$updSec = getenv('MAP_UPD_SEC') ?: '30';
	$usersRefreshSec = getenv('MAP_USERS_REFRESH_IDLE_SEC') ?: '60';
	echo '<script>window.MAP_UPD_SEC = ' . json_encode((int)$updSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_USERS_REFRESH_IDLE_SEC = ' . json_encode((int)$usersRefreshSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	// Сколько минут чужая точка ещё показывается на карте
	$liveTimeMin = getenv('MAP_LIVE_TIME_MIN') ?: '60';
	echo '<script>window.MAP_LIVE_TIME_MIN = ' . json_encode((int)$liveTimeMin, JSON_UNESCAPED_SLASHES) . ';</script>';
	// Порог движения (в метрах) и минимальные интервалы при движении
	$moveThresholdM = getenv('MAP_MOVE_THRESHOLD_M') ?: '25';
	$updMovingSec = getenv('MAP_UPD_MOVING_SEC') ?: '10';
	$usersRefreshMovingSec = getenv('MAP_USERS_REFRESH_MOVING_SEC') ?: '10';
	echo '<script>window.MAP_MOVE_THRESHOLD_M = ' . json_encode((int)$moveThresholdM, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_UPD_MOVING_SEC = ' . json_encode((int)$updMovingSec, JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<script>window.MAP_USERS_REFRESH_MOVING_SEC = ' . json_encode((int)$usersRefreshMovingSec, JSON_UNESCAPED_SLASHES) . ';</script>';
}

