<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Карта — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo getenv('map_ya_key') ?: ''; ?>&lang=ru_RU" type="text/javascript"></script>
    <style>
      /* Специальная разметка для страницы карты: без прокрутки, карта на всю высоту */
      .page{ padding:0; height:var(--app-height, 100vh); overflow:hidden }
      .map-container{ position:relative; width:100%; height:calc(var(--app-height, 100vh) - var(--nav-safe)); }
      .yandex-map{ width:100%; height:100%; }
    </style>
    <script>
      window.MAP_UPD_SEC = Number('<?php echo (int)(getenv('map_upd_sec') ?: 30); ?>') || 30;
      window.MAP_UPD_MOVING_SEC = Number('<?php echo (int)(getenv('map_upd_moving_sec') ?: 10); ?>') || 10; // интервал при движении
      window.MAP_MOVE_THRESHOLD_M = Number('<?php echo (int)(getenv('map_move_threshold_m') ?: 25); ?>') || 25; // движение если сдвиг > N м
      window.MAP_USERS_REFRESH_IDLE_SEC = Number('<?php echo (int)(getenv('map_users_refresh_idle_sec') ?: 60); ?>') || 60;
      window.MAP_USERS_REFRESH_MOVING_SEC = Number('<?php echo (int)(getenv('map_users_refresh_moving_sec') ?: 20); ?>') || 20;
    </script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <div class="map-container">
        <!-- Верхняя панель участников (Online - X) -->
        <div class="map-people-panel">
          <button id="peopleToggle" type="button" class="people-toggle" aria-expanded="false">Online - 0</button>
          <div id="peopleList" class="people-list" hidden></div>
        </div>

        <!-- Верхний блок ошибок -->
        <div id="mapError" class="map-error" hidden></div>

        <div id="map" class="yandex-map"></div>

        <!-- Круглые FAB-кнопки снизу -->
        <div class="map-fab-bar">
          <button id="sendLocationBtn" type="button" class="fab fab--power" aria-pressed="false" title="GPS">
           <!-- <span class="fab-progress" aria-hidden="true"></span> -->
            <span class="fab-icon" aria-hidden="true">⏻</span>
            <!-- <span class="blink-dot" aria-hidden="true"></span>-->
          </button>
          <!--
          <button id="audioRecBtn" type="button" class="fab fab--rec" aria-pressed="false" title="REC (скоро)">
            <span class="fab-icon" aria-hidden="true">●</span>
          </button>

          <button id="refreshUsersBtn" type="button" class="fab fab--refresh" title="Обновить">
            <span class="fab-progress" aria-hidden="true"></span>
            <span class="fab-icon" aria-hidden="true">↻</span>
          </button>
          -->
        </div>

        <!-- Тост над FAB -->
        <div id="mapToast" class="map-toast" hidden></div>
      </div>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script src="/app/frontend/assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>"></script>
    <script src="/app/frontend/assets/js/map.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/map.js'); ?>"></script>
    <script>
      console.log('🧪 Тест: JavaScript загружен!');
      console.log('🧪 Тест: app.js загружен?', !!window.CabrioAPI);
      console.log('🧪 Тест: map.js загружен?', !!window.MapFunctions);
      console.log('🧪 MAP_UPD_SEC =', window.MAP_UPD_SEC);
    </script>
  </body>
</html>


