<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Гид — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по названию, городу или услугам...',
      'filters' => [
        ['id' => 'categoryFilter', 'placeholder' => 'Все типы'],
        ['id' => 'typeFilter', 'placeholder' => 'Все виды']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
      <div class="card" style="margin-bottom:12px;">
        <h3 style="margin:0 0 6px 0;">Страница в разработке</h3>
        <p style="margin:0 0 6px 0; color:#aaa;">Скоро здесь появится полноценный раздел гида.</p>
        <ul style="margin:0 0 0 16px; padding:0; color:#ccc;">
          <li>Список мест и сервисов (мойки, СТО, детейлинг, кафе и т.д.)</li>
          <li>Фильтры по типам, видам и городу</li>
          <li>Детальная модалка объекта с описанием и контактами</li>
          <li>Оценки, отзывы и добавление новых мест</li>
        </ul>
      </div>
      <div id="services-list"><div class="card">Объектов пока нет</div></div>
    </main>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
    </script>
  </body>
  </html>


