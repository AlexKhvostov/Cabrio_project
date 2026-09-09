<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('События — CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по названию, городу...',
      'filters' => [
        ['id' => 'typeFilter', 'placeholder' => 'Все типы']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
<div id="eventsAccessBanner" class="info-block" style="margin-bottom:12px;display:none">
  <h3>Доступ к событиям</h3>
  <p style="margin:0">Список встреч открыт участникам клуба (роль member и выше).</p>
</div>
      <div id="events" class="cars-grid"><div class="list-busy" style="grid-column:1/-1"><div class="spinner"></div>Загрузка…</div></div>
      <button type="button" id="addEventFab" class="fab fab-add" hidden title="Добавить событие" aria-label="Добавить событие"><span class="fab-icon">+</span></button>
    </main>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/modals/event_modal.js'); ?>"></script>
    <script src="<?php echo cabrio_asset_href('assets/js/list_debug.js'); ?>"></script>
    <script>
      if (window.CabrioListDebug) {
        CabrioListDebug.start({
          route: '/api/events',
          listId: 'events',
          bannerId: 'eventsAccessBanner',
          fabId: 'addEventFab',
          kind: 'event',
          modalUrl: <?php echo json_encode(html_entity_decode(cabrio_asset_href('assets/js/modals/event_modal.js'), ENT_QUOTES, 'UTF-8'), JSON_UNESCAPED_SLASHES); ?>,
          cardUrl: <?php echo json_encode(html_entity_decode(cabrio_asset_href('assets/js/components/cards/event_card.js'), ENT_QUOTES, 'UTF-8'), JSON_UNESCAPED_SLASHES); ?>
        })
      }
    </script>
  </body>
</html>
