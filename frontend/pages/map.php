<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Карта — CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo getenv('map_ya_key') ?: ''; ?>&lang=ru_RU" type="text/javascript"></script>
    <style>
      /* Страница карты: карта на весь экран, список людей ниже системной шапки */
      .page{ padding:0; height:var(--app-height, 100vh); overflow:hidden }
      .map-container{ position:relative; width:100%; height:calc(var(--app-height, 100vh) - var(--nav-safe)); }
      .yandex-map{ width:100%; height:100%; }
    </style>
    <script>
      // Интервалы с сервера: как часто слать свою точку и как часто подтягивать чужие
      window.MAP_UPD_SEC = Number('<?php echo (int)(getenv('map_upd_sec') ?: 30); ?>') || 30;
      window.MAP_UPD_MOVING_SEC = Number('<?php echo (int)(getenv('map_upd_moving_sec') ?: 10); ?>') || 10;
      window.MAP_MOVE_THRESHOLD_M = Number('<?php echo (int)(getenv('map_move_threshold_m') ?: 25); ?>') || 25;
      window.MAP_USERS_REFRESH_IDLE_SEC = Number('<?php echo (int)(getenv('map_users_refresh_idle_sec') ?: 60); ?>') || 60;
      window.MAP_USERS_REFRESH_MOVING_SEC = Number('<?php echo (int)(getenv('map_users_refresh_moving_sec') ?: 20); ?>') || 20;
    </script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <div class="map-container">
        <!-- Кто сейчас делится координатами -->
        <div class="map-people-panel">
          <button id="peopleToggle" type="button" class="people-toggle" aria-expanded="false">Сейчас на карте — 0</button>
          <div id="peopleList" class="people-list" hidden></div>
        </div>

        <div id="mapError" class="map-error" hidden></div>

        <div id="map" class="yandex-map"></div>

        <!-- Подсказка, пока ты не делишься геолокацией: карту всё равно можно смотреть -->
        <div id="mapShareHint" class="map-share-hint">
          Чтобы свои видели тебя — включи геолокацию кнопкой ⏻
        </div>

        <div class="map-fab-bar">
          <button id="sendLocationBtn" type="button" class="fab fab--power" aria-pressed="false" title="Показывать меня на карте" aria-label="Показывать меня на карте">
            <span class="fab-icon" aria-hidden="true">⏻</span>
            <span class="blink-dot" aria-hidden="true"></span>
          </button>
          <button id="followMeBtn" type="button" class="fab" hidden aria-pressed="false" title="Держать меня в центре карты" aria-label="Держать меня в центре карты">
            <span class="fab-icon" aria-hidden="true">➤</span>
          </button>
        </div>

        <div id="mapToast" class="map-toast" hidden></div>
      </div>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script src="<?php echo cabrio_asset_href('assets/js/map.js'); ?>"></script>
  </body>
</html>
