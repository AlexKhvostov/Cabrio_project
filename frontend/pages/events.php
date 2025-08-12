<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('События — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по названию или описанию...',
      'filters' => [
        ['id' => 'typeFilter', 'placeholder' => 'Все типы'],
        ['id' => 'statusFilter', 'placeholder' => 'Все статусы']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
      <div class="card" style="margin-bottom:12px;">
        <h3 style="margin:0 0 6px 0;">Страница в разработке</h3>
        <p style="margin:0 0 6px 0; color:#aaa;">Скоро здесь появится полноценный раздел событий клуба.</p>
        <ul style="margin:0 0 0 16px; padding:0; color:#ccc;">
          <li>Список событий с датой, городом, типом и статусом</li>
          <li>Фильтры по типу, статусу и поиску</li>
          <li>Детальная модалка события с описанием и участием</li>
          <li>Кнопки участия (Да / Нет / Возможно) и +1</li>
        </ul>
      </div>
      <div id="events"><div class="card">Событий пока нет</div></div>
    </main>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
      // Пока показываем заглушку и описание раздела.
    </script>
  </body>
  </html>


