<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Карта — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <div class="map-placeholder card">
        <img class="map-bg" src="/app/frontend/assets/img/map_placeholder.jpg" alt="map" onerror="this.style.display='none'"/>
        <div class="map-content">
          <h3 style="margin:0 0 6px 0">Карта — в разработке</h3>
          <div style="color:#ccc;line-height:1.5;max-width:560px">
            Здесь появится интерактивная карта с автомобилями участников, событиями и маршрутами. 
            Вы сможете фильтровать и искать по городу, дате и типу объекта.
          </div>
          <div class="map-badges" style="margin-top:8px">
            <span class="badge">Пины авто на карте</span>
            <span class="badge">Фильтры по статусу</span>
            <span class="badge">События на карте</span>
            <span class="badge">Маршруты поездок</span>
          </div>
        </div>
      </div>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
    </script>
  </body>
  </html>


