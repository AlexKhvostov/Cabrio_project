<?php
require_once __DIR__ . '/../../backend/utils/load_env.php';
require_once __DIR__ . '/urls.php';

function render_meta(string $title = 'CabrioRide') {
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover, interactive-widget=overlays-content">';
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
  cabrio_render_metrika();
}

/** Яндекс.Метрика Mini App. Счётчик 112389999 — только апка, не лендинг cabrioride.by (там 102530559). */
function cabrio_render_metrika(): void {
  $raw = (string) (getenv('YANDEX_METRIKA_ID') ?: getenv('yandex_metrika_id') ?: '');
  $id = preg_replace('/\D+/', '', $raw);
  if ($id === '') {
    $id = '112389999';
  }
  $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
  $isLocal = (strpos($host, 'localhost') === 0) || $host === '127.0.0.1';
  if ($isLocal && $raw === '') {
    return;
  }
  $safe = htmlspecialchars($id, ENT_QUOTES);
  echo '<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function(m,e,t,r,i,k,a){
    m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
})(window, document,\'script\',\'https://mc.yandex.ru/metrika/tag.js?id=' . $safe . '\', \'ym\');
/* Не шлём location.href: в Mini App там длинный хвост Telegram (hash/query). */
ym(' . $id . ', \'init\', {ssr:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true, defer:true});
(function(){
  var counterId = ' . $id . ';
  function shortPageUrl(){
    /* Адрес раздела без ? и # — короткая страница, без tgWebAppData */
    var page = location.origin + location.pathname;
    var login = "";
    try {
      var u = window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.initDataUnsafe && window.Telegram.WebApp.initDataUnsafe.user;
      if (u && u.username) login = String(u.username).replace(/^@+/, "").trim();
    } catch (e) {}
    if (!login) return page;
    return page + "?utm_source=telegram&utm_medium=miniapp&utm_campaign=cabrioapp&utm_content=" + encodeURIComponent(login);
  }
  function sendHit(){
    if (sendHit.done) return;
    sendHit.done = true;
    ym(counterId, "hit", shortPageUrl());
  }
  /* Скрипт Telegram в head ниже: к DOMContentLoaded username уже известен (или его нет). */
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", sendHit);
  else sendHit();
})();
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/' . $safe . '" style="position:absolute;left:-9999px" alt=""></div></noscript>
<!-- /Yandex.Metrika counter -->';
}

